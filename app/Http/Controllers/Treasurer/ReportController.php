<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Loan;
use App\Models\LoanRepayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $driver = DB::getDriverName();
        $yearExpr = $driver === 'sqlite' ? "strftime('%Y', transaction_date)" : 'YEAR(transaction_date)';
        $monthExpr = $driver === 'sqlite' ? "strftime('%m', transaction_date)" : 'MONTH(transaction_date)';

        $monthlyContributions = Contribution::selectRaw($yearExpr.' as year, '.$monthExpr.' as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(6)
            ->get()
            ->reverse();

        $monthlyExpenses = Expense::selectRaw($yearExpr.' as year, '.$monthExpr.' as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(6)
            ->get()
            ->reverse();

        $monthlyIncome = Income::selectRaw($yearExpr.' as year, '.$monthExpr.' as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(6)
            ->get()
            ->reverse();

        $monthlyChartMap = [];
        $addChartRows = function ($rows, string $field) use (&$monthlyChartMap) {
            foreach ($rows as $row) {
                $key = sprintf('%04d-%02d', $row->year, $row->month);
                if (! isset($monthlyChartMap[$key])) {
                    $monthlyChartMap[$key] = [
                        'label' => \Carbon\Carbon::create($row->year, $row->month, 1)->format('M Y'),
                        'contrib' => 0.0,
                        'income' => 0.0,
                        'expenses' => 0.0,
                    ];
                }
                $monthlyChartMap[$key][$field] = (float) $row->total;
            }
        };

        $addChartRows($monthlyContributions, 'contrib');
        $addChartRows($monthlyIncome, 'income');
        $addChartRows($monthlyExpenses, 'expenses');

        ksort($monthlyChartMap);
        $monthlyChart = array_values($monthlyChartMap);
        if (count($monthlyChart) > 6) {
            $monthlyChart = array_slice($monthlyChart, -6);
        }

        $chartMax = 0.0;
        foreach ($monthlyChart as $item) {
            $chartMax = max($chartMax, $item['contrib'], $item['income'], $item['expenses']);
        }

        $loanSummary = [
            'total_loans' => Loan::count(),
            'total_outstanding' => Loan::sum('balance'),
            'overdue' => Loan::where('balance', '>', 0)->whereDate('due_date', '<', now())->count(),
        ];

        return view('reports.index', compact(
            'monthlyContributions',
            'monthlyExpenses',
            'monthlyIncome',
            'monthlyChart',
            'chartMax',
            'loanSummary'
        ));
    }

    public function ccmSummaryCsv(): Response
    {
        $summary = $this->buildCcmSummary();

        $lines = [];
        $lines[] = ['section', 'label', 'value'];
        $lines[] = ['live', 'members', $summary['members']];
        $lines[] = ['live', 'contributions', $summary['contributions']];
        $lines[] = ['live', 'contributions_total', number_format($summary['contributions_total'], 2, '.', '')];

        foreach ($summary['by_type'] as $row) {
            $lines[] = ['by_type', $row->type, number_format((float) $row->total, 2, '.', '')];
        }

        foreach ($summary['expected'] as $label => $value) {
            $lines[] = ['expected', $label, is_numeric($value) ? number_format((float) $value, 2, '.', '') : $value];
        }

        $lines[] = ['expected', 'expected_total', number_format($summary['expected_total'], 2, '.', '')];
        $lines[] = ['expected', 'delta_live_minus_expected', number_format($summary['delta'], 2, '.', '')];

        $handle = fopen('php://temp', 'r+');
        foreach ($lines as $line) {
            fputcsv($handle, $line);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $filename = 'ccm_summary_'.now()->format('Ymd_His').'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function ccmSummaryPdf(): Response
    {
        $summary = $this->buildCcmSummary();

        $pdf = Pdf::loadView('reports.ccm_summary_pdf', [
            'summary' => $summary,
            'generatedAt' => now(),
        ]);

        $filename = 'ccm_summary_'.now()->format('Ymd_His').'.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function financial(Request $request): View
    {
        $report = $this->buildFinancialReport(
            $request->input('start'),
            $request->input('end')
        );

        return view('reports.financial', $report);
    }

    public function financialPrint(Request $request): Response
    {
        $report = $this->buildFinancialReport(
            $request->input('start'),
            $request->input('end')
        );

        $pdf = Pdf::loadView('reports.financial_pdf', array_merge($report, [
            'generatedAt' => now(),
        ]));

        $filename = 'financial_report_'.$report['start']->format('Ymd').'_to_'.$report['end']->format('Ymd').'.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    public function financialPdf(Request $request): Response
    {
        $report = $this->buildFinancialReport(
            $request->input('start'),
            $request->input('end')
        );

        $pdf = Pdf::loadView('reports.financial_pdf', array_merge($report, [
            'generatedAt' => now(),
        ]));

        $filename = 'financial_report_'.$report['start']->format('Ymd').'_to_'.$report['end']->format('Ymd').'.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function buildCcmSummary(): array
    {
        if (! Schema::hasTable('staging_ccm_imports')) {
            abort(500, 'Missing staging_ccm_imports table.');
        }

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

        return [
            'members' => $membersCount,
            'contributions' => $contribCount,
            'contributions_total' => $contribSum,
            'by_type' => $byType,
            'expected' => $expected,
            'expected_total' => $expectedTotal,
            'delta' => $contribSum - $expectedTotal,
        ];
    }

    private function buildFinancialReport(?string $startInput, ?string $endInput): array
    {
        $start = $startInput ? Carbon::parse($startInput) : now()->startOfMonth();
        $end = $endInput ? Carbon::parse($endInput) : now()->endOfMonth();

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        $startDate = $start->copy()->startOfDay();
        $endDate = $end->copy()->endOfDay();

        $contributionQuery = Contribution::with('member')
            ->whereBetween('transaction_date', [$startDate, $endDate]);
        $incomeQuery = Income::whereBetween('transaction_date', [$startDate, $endDate]);
        $expenseQuery = Expense::whereBetween('transaction_date', [$startDate, $endDate]);
        $repaymentQuery = LoanRepayment::with(['loan.member'])
            ->whereBetween('payment_date', [$startDate, $endDate]);
        $loanQuery = Loan::with('member')
            ->whereBetween('issue_date', [$startDate, $endDate]);

        $summary = [
            'contributions' => (float) $contributionQuery->sum('amount'),
            'income' => (float) $incomeQuery->sum('amount'),
            'repayments' => (float) $repaymentQuery->sum('amount'),
            'expenses' => (float) $expenseQuery->sum('amount'),
        ];
        $summary['net'] = $summary['contributions'] + $summary['income'] + $summary['repayments'] - $summary['expenses'];

        $byContributionType = Contribution::select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();

        $byIncomeSource = Income::select('source', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->groupBy('source')
            ->orderByDesc('total')
            ->get();

        $byExpenseCategory = Expense::select('category', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        return [
            'start' => $startDate,
            'end' => $endDate,
            'summary' => $summary,
            'byContributionType' => $byContributionType,
            'byIncomeSource' => $byIncomeSource,
            'byExpenseCategory' => $byExpenseCategory,
            'contributions' => $contributionQuery->orderBy('transaction_date', 'desc')->get(),
            'incomes' => $incomeQuery->orderBy('transaction_date', 'desc')->get(),
            'expenses' => $expenseQuery->orderBy('transaction_date', 'desc')->get(),
            'repayments' => $repaymentQuery->orderBy('payment_date', 'desc')->get(),
            'loans' => $loanQuery->orderBy('issue_date', 'desc')->get(),
            'loanOutstanding' => (float) Loan::sum('balance'),
            'loanIssuedTotal' => (float) Loan::whereBetween('issue_date', [$startDate, $endDate])->sum('principal'),
        ];
    }
}
