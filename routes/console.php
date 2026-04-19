<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Models\Contribution;
use App\Models\Income;
use App\Models\LoanRepayment;
use App\Models\Receipt;
use App\Models\Member;
use App\Models\User;
use App\Services\ReceiptService;
use Barryvdh\DomPDF\Facade\Pdf;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

function resolve_sqlite_path(?string $path = null): string
{
    $database = $path ?: env('DB_DATABASE', 'database.sqlite');

    if (
        str_starts_with($database, '/')
        || str_starts_with($database, '\\')
        || preg_match('/^[A-Za-z]:[\\\\\\/]/', $database) === 1
    ) {
        return $database;
    }

    return base_path($database);
}

Artisan::command('db:archive {--path=} {--name=}', function () {
    $source = $this->option('path') ?: resolve_sqlite_path(env('DB_DATABASE', 'ccm_db'));
    if (! File::exists($source)) {
        $this->error('Database file not found at: '.$source);
        return 1;
    }

    $backupDir = storage_path('app/backups');
    File::ensureDirectoryExists($backupDir);

    $stamp = now()->format('Ymd_His');
    $name = $this->option('name') ?: 'ccm_db_full_'.$stamp.'.sqlite';
    $destination = $backupDir.DIRECTORY_SEPARATOR.$name;

    File::copy($source, $destination);
    $this->info('Database archived: '.$destination);

    return 0;
})->purpose('Archive the SQLite database file to storage/app/backups');

Artisan::command('db:switch {target}', function () {
    $target = strtolower((string) $this->argument('target'));
    $map = [
        'full' => 'ccm_db',
        'empty' => 'ccm_db_empty',
    ];

    if (! isset($map[$target])) {
        $this->error('Invalid target. Use "full" or "empty".');
        return 1;
    }

    $dbFile = base_path($map[$target]);
    if (! File::exists($dbFile)) {
        $this->error('Database file not found: '.$dbFile);
        return 1;
    }

    $envPath = base_path('.env');
    if (! File::exists($envPath)) {
        $this->error('Missing .env file.');
        return 1;
    }

    $contents = File::get($envPath);
    if (preg_match('/^DB_DATABASE=.*$/m', $contents)) {
        $contents = preg_replace('/^DB_DATABASE=.*$/m', 'DB_DATABASE='.$map[$target], $contents);
    } else {
        $contents .= PHP_EOL.'DB_DATABASE='.$map[$target].PHP_EOL;
    }

    File::put($envPath, $contents);
    Artisan::call('optimize:clear');

    $this->info('Switched DB_DATABASE to '.$map[$target].' and cleared cache.');
    return 0;
})->purpose('Switch between the full and empty SQLite databases');

Artisan::command('activity-logs:clear', function () {
    if (! Schema::hasTable('activity_logs')) {
        $this->error('Missing table: activity_logs (run migrations)');
        return 1;
    }

    DB::table('activity_logs')->truncate();
    $this->info('Activity logs cleared.');

    return 0;
})->purpose('Clear all activity logs');

Artisan::command('backup:sqlite', function () {
    $databasePath = resolve_sqlite_path();

    if (! File::exists($databasePath)) {
        $this->error('SQLite database not found at: '.$databasePath);
        return;
    }

    $backupDir = storage_path('app/backups');
    File::ensureDirectoryExists($backupDir);

    $timestamp = now()->format('Ymd_His');
    $backupPath = $backupDir.'/ccm_'.$timestamp.'.sqlite';
    File::copy($databasePath, $backupPath);

    $this->info('Backup created: '.$backupPath);

    $files = collect(File::files($backupDir))
        ->sortByDesc(fn ($file) => $file->getMTime())
        ->values();

    foreach ($files->slice(14) as $oldFile) {
        File::delete($oldFile->getPathname());
    }

    $this->info('Old backups pruned (kept latest 14).');
})->purpose('Create a daily SQLite backup with retention');

Artisan::command('import:ccm-staging {--path=} {--truncate}', function () {
    $path = $this->option('path') ?: storage_path('imports/ccm_clean.csv');

    if (! File::exists($path)) {
        $this->error('CSV not found at: '.$path);
        return 1;
    }

    if (! Schema::hasTable('staging_ccm_imports')) {
        $this->error('Missing table: staging_ccm_imports (run migrations)');
        return 1;
    }

    if ($this->option('truncate')) {
        DB::table('staging_ccm_imports')->truncate();
    }

    $handle = fopen($path, 'r');
    if ($handle === false) {
        $this->error('Unable to open CSV.');
        return 1;
    }

    $headers = fgetcsv($handle);
    if (! $headers) {
        fclose($handle);
        $this->error('CSV appears to be empty.');
        return 1;
    }
    $headers = array_map(function ($header) {
        $header = trim((string) $header);
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
        $header = preg_replace('/^\x{FEFF}/u', '', $header);
        $header = trim($header, " \t\n\r\0\x0B\"");
        return strtolower($header);
    }, $headers);

    $required = [
        'member_no',
        'membership_id',
        'full_name',
        'phone',
        'admission_fee',
        'professor_donation',
        'lawyer_donation',
        'extra_levies',
        'extra_notes',
        'payment_received_raw',
        'notes',
    ];

    $required = array_map('strtolower', $required);
    $missing = array_values(array_diff($required, $headers));
    if (count($missing) > 0) {
        fclose($handle);
        $this->error('Missing required columns: '.implode(', ', $missing));
        return 1;
    }

    $rows = [];
    $count = 0;

    $headerCount = count($headers);
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) === 1 && trim((string) $row[0]) === '') {
            continue;
        }

        if (count($row) < $headerCount) {
            $row = array_pad($row, $headerCount, null);
        } elseif (count($row) > $headerCount) {
            $fixed = array_slice($row, 0, $headerCount - 1);
            $fixed[] = implode(',', array_slice($row, $headerCount - 1));
            $row = $fixed;
        }

        $data = array_combine($headers, $row);
        if (! $data) {
            continue;
        }

        foreach ($data as $key => $value) {
            $value = trim((string) $value);
            $data[$key] = $value === '' ? null : $value;
        }

        $dues = [];
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'dues_')) {
                $period = str_replace('_', '-', substr($key, 5));
                $dues[$period] = $value === null ? null : (float) $value;
            }
        }

        $rows[] = [
            'member_no' => $data['member_no'],
            'membership_id' => $data['membership_id'],
            'full_name' => $data['full_name'],
            'phone' => $data['phone'],
            'admission_fee' => $data['admission_fee'] === null ? null : (float) $data['admission_fee'],
            'professor_donation' => $data['professor_donation'] === null ? null : (float) $data['professor_donation'],
            'lawyer_donation' => $data['lawyer_donation'] === null ? null : (float) $data['lawyer_donation'],
            'extra_levies' => $data['extra_levies'] === null ? null : (float) $data['extra_levies'],
            'extra_notes' => $data['extra_notes'],
            'payment_received_raw' => $data['payment_received_raw'],
            'notes' => $data['notes'],
            'dues' => json_encode($dues),
            'raw_row' => json_encode($data),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (count($rows) >= 200) {
            DB::table('staging_ccm_imports')->insert($rows);
            $count += count($rows);
            $rows = [];
        }
    }

    if (count($rows) > 0) {
        DB::table('staging_ccm_imports')->insert($rows);
        $count += count($rows);
    }

    fclose($handle);

    $this->info('Imported '.$count.' rows into staging_ccm_imports.');
    return 0;
})->purpose('Import CCM CSV into staging_ccm_imports');

Artisan::command('validate:ccm-staging', function () {
    if (! Schema::hasTable('staging_ccm_imports')) {
        $this->error('Missing table: staging_ccm_imports (run migrations)');
        return 1;
    }

    $total = DB::table('staging_ccm_imports')->count();
    $this->info('Total rows: '.$total);

    $missingMembership = DB::table('staging_ccm_imports')
        ->whereNull('membership_id')
        ->orWhere('membership_id', '')
        ->get(['member_no', 'full_name', 'phone']);

    if ($missingMembership->count() > 0) {
        $this->warn('Missing membership_id: '.$missingMembership->count());
        foreach ($missingMembership as $row) {
            $this->line('- member_no '.$row->member_no.' | '.$row->full_name.' | '.$row->phone);
        }
    } else {
        $this->info('No missing membership_id.');
    }

    $duplicateMembership = DB::table('staging_ccm_imports')
        ->select('membership_id', DB::raw('count(*) as cnt'))
        ->whereNotNull('membership_id')
        ->where('membership_id', '!=', '')
        ->groupBy('membership_id')
        ->having('cnt', '>', 1)
        ->get();

    if ($duplicateMembership->count() > 0) {
        $this->warn('Duplicate membership_id: '.$duplicateMembership->count());
        foreach ($duplicateMembership as $row) {
            $this->line('- '.$row->membership_id.' ('.$row->cnt.')');
        }
    } else {
        $this->info('No duplicate membership_id.');
    }

    $missingNames = DB::table('staging_ccm_imports')
        ->whereNull('full_name')
        ->orWhere('full_name', '')
        ->get(['member_no', 'membership_id']);

    if ($missingNames->count() > 0) {
        $this->warn('Missing full_name: '.$missingNames->count());
        foreach ($missingNames as $row) {
            $this->line('- member_no '.$row->member_no.' | '.$row->membership_id);
        }
    }

    $missingPhones = DB::table('staging_ccm_imports')
        ->whereNull('phone')
        ->orWhere('phone', '')
        ->get(['member_no', 'membership_id', 'full_name']);

    if ($missingPhones->count() > 0) {
        $this->warn('Missing phone: '.$missingPhones->count());
        foreach ($missingPhones as $row) {
            $this->line('- member_no '.$row->member_no.' | '.$row->membership_id.' | '.$row->full_name);
        }
    }

    $duesAnomalies = [];
    $rows = DB::table('staging_ccm_imports')->select('member_no', 'membership_id', 'dues')->get();
    foreach ($rows as $row) {
        $dues = json_decode($row->dues ?? '[]', true);
        if (! is_array($dues)) {
            continue;
        }
        foreach ($dues as $period => $amount) {
            if ($amount === null || $amount === '') {
                continue;
            }
            if ((float) $amount !== 50.0) {
                $duesAnomalies[] = $row->member_no.' | '.$row->membership_id.' | '.$period.' = '.$amount;
            }
        }
    }

    if (count($duesAnomalies) > 0) {
        $this->warn('Dues not equal to 50 detected: '.count($duesAnomalies));
        foreach ($duesAnomalies as $line) {
            $this->line('- '.$line);
        }
    } else {
        $this->info('All dues amounts are 50 where present.');
    }

    return 0;
})->purpose('Validate staging CCM imports (missing IDs, duplicates, dues anomalies)');

Artisan::command('promote:ccm-staging {--user-email=admin@example.com} {--dry-run}', function () {
    if (! Schema::hasTable('staging_ccm_imports')) {
        $this->error('Missing table: staging_ccm_imports (run migrations)');
        return 1;
    }

    if (! Schema::hasTable('members') || ! Schema::hasTable('contributions')) {
        $this->error('Missing members or contributions table (run migrations)');
        return 1;
    }

    $userEmail = $this->option('user-email') ?: 'admin@example.com';
    $user = User::where('email', $userEmail)->first() ?: User::query()->orderBy('id')->first();

    if (! $user) {
        $this->error('No users found. Run the DatabaseSeeder first.');
        return 1;
    }

    $dryRun = (bool) $this->option('dry-run');
    $stats = [
        'members_created' => 0,
        'members_updated' => 0,
        'contributions_created' => 0,
        'dues_created' => 0,
        'skipped_rows' => 0,
    ];

    $rows = DB::table('staging_ccm_imports')->get();

    $resolveJoinDate = function (array $dues): string {
        $periods = array_keys($dues);
        sort($periods);
        foreach ($periods as $period) {
            if (preg_match('/^\\d{4}-\\d{2}$/', $period)) {
                return $period.'-01';
            }
        }
        return now()->toDateString();
    };

    DB::transaction(function () use ($rows, $user, $dryRun, &$stats, $resolveJoinDate) {
        foreach ($rows as $row) {
            $membershipId = trim((string) $row->membership_id);
            if ($membershipId === '') {
                $stats['skipped_rows']++;
                continue;
            }

            $dues = json_decode($row->dues ?? '[]', true);
            if (! is_array($dues)) {
                $dues = [];
            }

            $joinDate = $resolveJoinDate($dues);

            $member = Member::where('membership_id', $membershipId)->first();
            if (! $member) {
                if (! $dryRun) {
                    $member = Member::create([
                        'membership_id' => $membershipId,
                        'full_name' => $row->full_name ?: 'Unknown Member',
                        'phone' => $row->phone ?: 'N/A',
                        'email' => null,
                        'status' => 'active',
                        'join_date' => $joinDate,
                    ]);
                }
                $stats['members_created']++;
            } else {
                $updates = [];
                if (! $member->full_name && $row->full_name) {
                    $updates['full_name'] = $row->full_name;
                }
                if (! $member->phone && $row->phone) {
                    $updates['phone'] = $row->phone;
                }
                if (! empty($updates)) {
                    if (! $dryRun) {
                        $member->update($updates);
                    }
                    $stats['members_updated']++;
                }
            }

            if (! $member) {
                continue;
            }

            $createContribution = function (string $type, $amount, string $date) use ($member, $user, $dryRun, &$stats) {
                if ($amount === null || $amount === '') {
                    return;
                }
                $amount = (float) $amount;
                if ($amount <= 0) {
                    return;
                }

                if ($dryRun) {
                    $stats['contributions_created']++;
                    return;
                }

                $contribution = Contribution::firstOrCreate(
                    [
                        'member_id' => $member->id,
                        'type' => $type,
                        'amount' => $amount,
                        'payment_method' => 'cash',
                        'transaction_date' => $date,
                    ],
                    [
                        'recorded_by' => $user->id,
                    ]
                );

                if ($contribution->wasRecentlyCreated) {
                    $stats['contributions_created']++;
                }
            };

            $baseDate = $joinDate;
            $createContribution('Admission Fee', $row->admission_fee, $baseDate);
            $createContribution('Professor Donation', $row->professor_donation, $baseDate);
            $createContribution('Lawyer Donation', $row->lawyer_donation, $baseDate);

            $extraType = 'Extra Levies';
            if ($row->extra_notes) {
                $note = trim((string) $row->extra_notes);
                if ($note !== '') {
                    $extraType = substr('Extra Levies - '.$note, 0, 120);
                }
            }
            $createContribution($extraType, $row->extra_levies, $baseDate);

            foreach ($dues as $period => $amount) {
                if ($amount === null || $amount === '') {
                    continue;
                }
                if (! preg_match('/^\\d{4}-\\d{2}$/', (string) $period)) {
                    continue;
                }
                $date = $period.'-01';
                $createContribution('Monthly Dues', $amount, $date);
                $stats['dues_created']++;
            }
        }
    });

    $this->info('Promotion complete'.($dryRun ? ' (dry-run)' : '').'.');
    foreach ($stats as $key => $value) {
        $this->line($key.': '.$value);
    }

    return 0;
})->purpose('Promote staging CCM imports into members and contributions');

Artisan::command('report:ccm-summary', function () {
    if (! Schema::hasTable('staging_ccm_imports')) {
        $this->error('Missing table: staging_ccm_imports (run migrations)');
        return 1;
    }

    $membersCount = DB::table('members')->count();
    $contribCount = DB::table('contributions')->count();
    $contribSum = (float) DB::table('contributions')->sum('amount');

    $this->info('=== Live Tables Summary ===');
    $this->line('Members: '.$membersCount);
    $this->line('Contributions: '.$contribCount);
    $this->line('Contributions Total (GHS): '.number_format($contribSum, 2));

    $this->line('');
    $this->info('Contributions by Type:');
    $byType = DB::table('contributions')
        ->select('type', DB::raw('count(*) as cnt'), DB::raw('sum(amount) as total'))
        ->groupBy('type')
        ->orderBy('type')
        ->get();

    foreach ($byType as $row) {
        $this->line('- '.$row->type.': '.$row->cnt.' | '.number_format((float) $row->total, 2));
    }

    $this->line('');
    $this->info('=== Staging Totals (Expected) ===');
    $staging = DB::table('staging_ccm_imports')->get();

    $expected = [
        'Admission Fee' => 0.0,
        'Professor Donation' => 0.0,
        'Lawyer Donation' => 0.0,
        'Extra Levies' => 0.0,
        'Monthly Dues' => 0.0,
        'Monthly Dues Count' => 0,
    ];

    foreach ($staging as $row) {
        $expected['Admission Fee'] += (float) ($row->admission_fee ?? 0);
        $expected['Professor Donation'] += (float) ($row->professor_donation ?? 0);
        $expected['Lawyer Donation'] += (float) ($row->lawyer_donation ?? 0);
        $expected['Extra Levies'] += (float) ($row->extra_levies ?? 0);

        $dues = json_decode($row->dues ?? '[]', true);
        if (is_array($dues)) {
            foreach ($dues as $amount) {
                if ($amount === null || $amount === '') {
                    continue;
                }
                $expected['Monthly Dues'] += (float) $amount;
                $expected['Monthly Dues Count']++;
            }
        }
    }

    foreach ($expected as $label => $value) {
        if ($label === 'Monthly Dues Count') {
            $this->line($label.': '.$value);
            continue;
        }
        $this->line($label.' (GHS): '.number_format($value, 2));
    }

    $expectedTotal = $expected['Admission Fee']
        + $expected['Professor Donation']
        + $expected['Lawyer Donation']
        + $expected['Extra Levies']
        + $expected['Monthly Dues'];

    $this->line('Expected Total (GHS): '.number_format($expectedTotal, 2));

    $this->line('');
    $this->info('=== Live vs Expected ===');
    $delta = $contribSum - $expectedTotal;
    $this->line('Delta (Live - Expected): '.number_format($delta, 2));

    return 0;
})->purpose('Summarize CCM import totals from staging vs live tables');

Artisan::command('report:ccm-export {--format=both}', function () {
    $format = strtolower((string) $this->option('format'));
    if (! in_array($format, ['csv', 'pdf', 'both'], true)) {
        $this->error('Invalid format. Use csv, pdf, or both.');
        return 1;
    }

    if (! Schema::hasTable('staging_ccm_imports')) {
        $this->error('Missing table: staging_ccm_imports (run migrations)');
        return 1;
    }

    $timestamp = now()->format('Ymd_His');
    $reportDir = storage_path('app/reports');
    File::ensureDirectoryExists($reportDir);

    $membersCount = DB::table('members')->count();
    $contribCount = DB::table('contributions')->count();
    $contribSum = (float) DB::table('contributions')->sum('amount');

    $byType = DB::table('contributions')
        ->select('type', DB::raw('count(*) as cnt'), DB::raw('sum(amount) as total'))
        ->groupBy('type')
        ->orderBy('type')
        ->get();

    $staging = DB::table('staging_ccm_imports')->get();
    $expected = [
        'Admission Fee' => 0.0,
        'Professor Donation' => 0.0,
        'Lawyer Donation' => 0.0,
        'Extra Levies' => 0.0,
        'Monthly Dues' => 0.0,
        'Monthly Dues Count' => 0,
    ];

    foreach ($staging as $row) {
        $expected['Admission Fee'] += (float) ($row->admission_fee ?? 0);
        $expected['Professor Donation'] += (float) ($row->professor_donation ?? 0);
        $expected['Lawyer Donation'] += (float) ($row->lawyer_donation ?? 0);
        $expected['Extra Levies'] += (float) ($row->extra_levies ?? 0);

        $dues = json_decode($row->dues ?? '[]', true);
        if (is_array($dues)) {
            foreach ($dues as $amount) {
                if ($amount === null || $amount === '') {
                    continue;
                }
                $expected['Monthly Dues'] += (float) $amount;
                $expected['Monthly Dues Count']++;
            }
        }
    }

    $expectedTotal = $expected['Admission Fee']
        + $expected['Professor Donation']
        + $expected['Lawyer Donation']
        + $expected['Extra Levies']
        + $expected['Monthly Dues'];

    $summary = [
        'members' => $membersCount,
        'contributions' => $contribCount,
        'contributions_total' => $contribSum,
        'by_type' => $byType,
        'expected' => $expected,
        'expected_total' => $expectedTotal,
        'delta' => $contribSum - $expectedTotal,
    ];

    if ($format === 'csv' || $format === 'both') {
        $csvPath = $reportDir.'/ccm_summary_'.$timestamp.'.csv';
        $lines = [];
        $lines[] = ['section', 'label', 'value'];
        $lines[] = ['live', 'members', $membersCount];
        $lines[] = ['live', 'contributions', $contribCount];
        $lines[] = ['live', 'contributions_total', number_format($contribSum, 2, '.', '')];

        foreach ($byType as $row) {
            $lines[] = ['by_type', $row->type, number_format((float) $row->total, 2, '.', '')];
        }

        foreach ($expected as $label => $value) {
            $lines[] = ['expected', $label, is_numeric($value) ? number_format((float) $value, 2, '.', '') : $value];
        }

        $lines[] = ['expected', 'expected_total', number_format($expectedTotal, 2, '.', '')];
        $lines[] = ['expected', 'delta_live_minus_expected', number_format($summary['delta'], 2, '.', '')];

        $handle = fopen($csvPath, 'w');
        foreach ($lines as $line) {
            fputcsv($handle, $line);
        }
        fclose($handle);

        $this->info('CSV report saved: '.$csvPath);
    }

    if ($format === 'pdf' || $format === 'both') {
        $pdfPath = $reportDir.'/ccm_summary_'.$timestamp.'.pdf';
        $pdf = Pdf::loadView('reports.ccm_summary_pdf', [
            'summary' => $summary,
            'generatedAt' => now(),
        ]);
        File::put($pdfPath, $pdf->output());
        $this->info('PDF report saved: '.$pdfPath);
    }

    return 0;
})->purpose('Export CCM summary report to CSV/PDF');

Artisan::command('receipts:generate-contributions {--user-email=admin@example.com} {--limit=0} {--dry-run}', function () {
    if (! Schema::hasTable('receipts') || ! Schema::hasTable('contributions')) {
        $this->error('Missing receipts or contributions table (run migrations)');
        return 1;
    }

    $userEmail = $this->option('user-email') ?: 'admin@example.com';
    $user = User::where('email', $userEmail)->first() ?: User::query()->orderBy('id')->first();
    if (! $user) {
        $this->error('No users found. Run the DatabaseSeeder first.');
        return 1;
    }

    $limit = (int) $this->option('limit');
    $dryRun = (bool) $this->option('dry-run');

    $query = Contribution::with('member')->whereDoesntHave('receipt')->orderBy('id');
    if ($limit > 0) {
        $query->limit($limit);
    }

    $service = app(ReceiptService::class);
    $count = 0;

    foreach ($query->cursor() as $contribution) {
        if ($dryRun) {
            $count++;
            continue;
        }

        $service->createForContribution($contribution, $user);
        $count++;
    }

    $this->info('Receipts generated: '.$count.($dryRun ? ' (dry-run)' : ''));
    return 0;
})->purpose('Generate receipts for contributions missing receipts');

Artisan::command('receipts:regenerate-all {--user-email=admin@example.com} {--dry-run}', function () {
    if (
        ! Schema::hasTable('receipts')
        || ! Schema::hasTable('contributions')
        || ! Schema::hasTable('incomes')
        || ! Schema::hasTable('loan_repayments')
    ) {
        $this->error('Missing receipts, contributions, incomes, or loan_repayments table (run migrations)');
        return 1;
    }

    $userEmail = $this->option('user-email') ?: 'admin@example.com';
    $user = User::where('email', $userEmail)->first() ?: User::query()->orderBy('id')->first();
    if (! $user) {
        $this->error('No users found. Run the DatabaseSeeder first.');
        return 1;
    }

    $dryRun = (bool) $this->option('dry-run');
    $service = app(ReceiptService::class);

    $existingReceipts = Receipt::withTrashed()->get();
    $this->info('Existing receipts: '.$existingReceipts->count());

    if (! $dryRun) {
        foreach ($existingReceipts as $receipt) {
            if ($receipt->pdf_path) {
                Storage::disk('public')->delete($receipt->pdf_path);
            }
        }
        Receipt::withTrashed()->forceDelete();
    }

    $created = 0;
    foreach (Contribution::with('member')->orderBy('id')->cursor() as $contribution) {
        if ($dryRun) {
            $created++;
            continue;
        }
        $service->createForContribution($contribution, $user);
        $created++;
    }

    $incomeCount = 0;
    foreach (Income::orderBy('id')->cursor() as $income) {
        if ($dryRun) {
            $incomeCount++;
            continue;
        }
        $service->createForIncome($income, $user);
        $incomeCount++;
    }

    $repaymentCount = 0;
    foreach (LoanRepayment::with('loan.member')->orderBy('id')->cursor() as $repayment) {
        if ($dryRun) {
            $repaymentCount++;
            continue;
        }
        $service->createForLoanRepayment($repayment, $user);
        $repaymentCount++;
    }

    if ($dryRun) {
        $this->info('Dry-run complete. Would create receipts: '.$created.' contributions, '.$incomeCount.' incomes, '.$repaymentCount.' repayments.');
        return 0;
    }

    $this->info('Receipts regenerated: '.$created.' contributions, '.$incomeCount.' incomes, '.$repaymentCount.' repayments.');
    return 0;
})->purpose('Regenerate all receipts and PDFs with the latest receipt template');

Artisan::command('normalize:phones', function () {
    $normalize = function (?string $phone): ?string {
        if (! $phone) {
            return null;
        }

        $phone = trim($phone);
        if ($phone === '') {
            return null;
        }

        $hasPlus = str_starts_with($phone, '+');
        $digits = preg_replace('/\\D+/', '', $phone);

        if ($digits === '') {
            return null;
        }

        return $hasPlus ? ('+'.$digits) : $digits;
    };

    $members = DB::table('members')->select('id', 'phone')->get();
    $memberUpdated = 0;
    foreach ($members as $member) {
        $clean = $normalize($member->phone);
        if ($clean !== $member->phone) {
            DB::table('members')->where('id', $member->id)->update(['phone' => $clean]);
            $memberUpdated++;
        }
    }

    $stagingUpdated = 0;
    if (Schema::hasTable('staging_ccm_imports')) {
        $rows = DB::table('staging_ccm_imports')->select('id', 'phone')->get();
        foreach ($rows as $row) {
            $clean = $normalize($row->phone);
            if ($clean !== $row->phone) {
                DB::table('staging_ccm_imports')->where('id', $row->id)->update(['phone' => $clean]);
                $stagingUpdated++;
            }
        }
    }

    $this->info('Phones normalized.');
    $this->line('members updated: '.$memberUpdated);
    $this->line('staging updated: '.$stagingUpdated);

    return 0;
})->purpose('Normalize phone numbers in members and staging_ccm_imports');

Artisan::command('users:prune-non-admin {--keep-email=admin@example.com}', function () {
    if (! Schema::hasTable('users')) {
        $this->error('Missing users table.');
        return 1;
    }

    $keepEmail = $this->option('keep-email') ?: 'admin@example.com';
    $admin = User::where('email', $keepEmail)->first();
    if (! $admin) {
        $admin = User::where('role', 'admin')->orderBy('id')->first();
    }
    if (! $admin) {
        $this->error('No admin user found to keep.');
        return 1;
    }

    $adminId = $admin->id;
    $this->info('Keeping admin user: '.$admin->email.' (ID '.$adminId.')');

    $tablesToUpdate = [
        ['table' => 'activity_logs', 'column' => 'user_id'],
        ['table' => 'contributions', 'column' => 'recorded_by'],
        ['table' => 'incomes', 'column' => 'recorded_by'],
        ['table' => 'expenses', 'column' => 'recorded_by'],
        ['table' => 'loans', 'column' => 'recorded_by'],
        ['table' => 'loan_repayments', 'column' => 'recorded_by'],
        ['table' => 'receipts', 'column' => 'generated_by'],
    ];

    $updatedCounts = [];
    DB::transaction(function () use ($tablesToUpdate, $adminId, &$updatedCounts) {
        foreach ($tablesToUpdate as $info) {
            if (! Schema::hasTable($info['table'])) {
                continue;
            }
            $count = DB::table($info['table'])
                ->where($info['column'], '!=', $adminId)
                ->update([$info['column'] => $adminId]);
            $updatedCounts[$info['table'].'.'.$info['column']] = $count;
        }
    });

    $deleted = User::where('id', '!=', $adminId)->delete();

    $this->info('Reassigned references to admin.');
    foreach ($updatedCounts as $key => $count) {
        $this->line($key.': '.$count);
    }
    $this->info('Users deleted: '.$deleted);

    return 0;
})->purpose('Delete all non-admin users and reassign references to admin');

Schedule::command('backup:sqlite')->dailyAt('23:55');
