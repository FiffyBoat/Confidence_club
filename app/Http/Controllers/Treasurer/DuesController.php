<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDuesRequest;
use App\Models\Contribution;
use App\Models\Member;
use App\Models\Setting;
use App\Services\ReceiptService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DuesController extends Controller
{
    public function __construct(private readonly ReceiptService $receiptService)
    {
    }

    public function index(Request $request): View
    {
        return view('dues.index', $this->buildDuesData(
            yearInput: $request->input('year'),
            asOfInput: $request->input('as_of'),
            includePayments: true,
        ));
    }

    public function arrearsCsv(Request $request): Response
    {
        $data = $this->buildDuesData(
            yearInput: $request->input('year'),
            asOfInput: $request->input('as_of'),
            includePayments: false,
        );

        $lines = [];
        $lines[] = ['member_id', 'member_name', 'status', 'months_due', 'paid_to_date', 'amount_owed', 'unpaid_months'];

        foreach ($data['outstandingRows'] as $row) {
            $statusLabel = match ($row['payment_status']) {
                'none' => 'No dues paid',
                'partial' => 'Partly paid',
                default => 'Outstanding',
            };

            $lines[] = [
                $row['member']->membership_id,
                $row['member']->full_name,
                $statusLabel,
                $row['months_due'],
                number_format($row['paid_to_as_of'], 2, '.', ''),
                number_format($row['balance_end'], 2, '.', ''),
                collect($row['arrears_months'])->pluck('label')->implode(', '),
            ];
        }

        $handle = fopen('php://temp', 'r+');
        foreach ($lines as $line) {
            fputcsv($handle, $line);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $filename = sprintf(
            'dues_arrears_%d_%02d_%s.csv',
            $data['year'],
            $data['asOfMonth'],
            now()->format('Ymd_His')
        );

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function arrearsPrint(Request $request): Response
    {
        $data = $this->buildDuesData(
            yearInput: $request->input('year'),
            asOfInput: $request->input('as_of'),
            includePayments: false,
        );

        $pdf = Pdf::loadView('dues.arrears_pdf', array_merge($data, [
            'generatedAt' => now(),
        ]));

        $filename = sprintf(
            'dues_arrears_%d_%02d_%s.pdf',
            $data['year'],
            $data['asOfMonth'],
            now()->format('Ymd_His')
        );

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    public function store(StoreDuesRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $month = (int) $data['month'];
        $year = (int) $data['year'];
        $transactionDate = $data['transaction_date'] ?? Carbon::create($year, $month, 1)->toDateString();

        $contribution = Contribution::create([
            'member_id' => $data['member_id'],
            'type' => 'Monthly Dues',
            'description' => 'Monthly dues for '.Carbon::create($year, $month, 1)->format('F Y'),
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'transaction_date' => $transactionDate,
            'recorded_by' => $request->user()->id,
        ]);

        $contribution->load('member');
        $this->receiptService->createForContribution($contribution, $request->user());

        return redirect()
            ->route('dues.index', ['year' => $year, 'as_of' => $month])
            ->with('success', 'Monthly dues recorded.');
    }

    private function buildDuesData(mixed $yearInput, mixed $asOfInput, bool $includePayments): array
    {
        $year = (int) ($yearInput ?: now()->year);
        $asOfMonth = (int) ($asOfInput ?: now()->month);
        $asOfMonth = max(1, min(12, $asOfMonth));

        $monthlyRate = (float) (Setting::getValue('monthly_dues_amount')
            ?? config('ccm.monthly_dues_amount', 50));
        $clubStartDateValue = Setting::getValue('club_start_date')
            ?? config('ccm.club_start_date', '2025-10-01');
        $clubStartDate = Carbon::parse($clubStartDateValue)->startOfMonth();

        $members = Member::orderBy('full_name')->get();

        $duesContributions = Contribution::where('type', 'Monthly Dues')
            ->whereYear('transaction_date', $year)
            ->get()
            ->groupBy('member_id');

        $rows = $members->map(function (Member $member) use ($duesContributions, $year, $asOfMonth, $clubStartDate, $monthlyRate) {
            $months = [];
            for ($m = 1; $m <= 12; $m++) {
                $months[$m] = 0.0;
            }

            foreach ($duesContributions->get($member->id, collect()) as $contribution) {
                $monthIndex = (int) $contribution->transaction_date?->format('n');
                if ($monthIndex >= 1 && $monthIndex <= 12) {
                    $months[$monthIndex] += (float) $contribution->amount;
                }
            }

            $paidToAsOf = 0.0;
            for ($m = 1; $m <= $asOfMonth; $m++) {
                $paidToAsOf += $months[$m];
            }

            $memberStart = $clubStartDate;
            if ($member->join_date) {
                $memberStart = $member->join_date->greaterThan($clubStartDate)
                    ? $member->join_date
                    : $clubStartDate;
            }

            $memberStart = $memberStart->copy()->startOfMonth();
            $monthsDue = 0;
            if ($year > $memberStart->year || ($year === $memberStart->year && $asOfMonth >= $memberStart->month)) {
                $startMonth = $year === $memberStart->year ? $memberStart->month : 1;
                $monthsDue = max(0, $asOfMonth - $startMonth + 1);
            }

            $dueToAsOf = $monthsDue * $monthlyRate;
            $balanceEnd = max(0, $dueToAsOf - $paidToAsOf);
            $nextMonthActive = $year > $memberStart->year
                || ($year === $memberStart->year && $asOfMonth >= max(1, $memberStart->month - 1));
            $balanceNext = $balanceEnd + ($nextMonthActive ? $monthlyRate : 0);
            $startMonthForYear = $year < $memberStart->year
                ? 13
                : ($year === $memberStart->year ? $memberStart->month : 1);
            $dueMonths = [];
            if ($monthsDue > 0) {
                for ($m = $startMonthForYear; $m <= $asOfMonth; $m++) {
                    $dueMonths[] = $m;
                }
            }

            $arrearsMonths = $this->buildArrearsMonths(
                dueMonths: $dueMonths,
                months: $months,
                monthlyRate: $monthlyRate,
                year: $year,
            );

            $paymentStatus = 'cleared';
            if ($dueToAsOf <= 0) {
                $paymentStatus = 'not_due';
            } elseif ($paidToAsOf <= 0) {
                $paymentStatus = 'none';
            } elseif ($balanceEnd > 0) {
                $paymentStatus = 'partial';
            }

            return [
                'member' => $member,
                'months' => $months,
                'paid_to_as_of' => $paidToAsOf,
                'due_to_as_of' => $dueToAsOf,
                'months_due' => $monthsDue,
                'start_month' => $startMonthForYear,
                'balance_end' => $balanceEnd,
                'balance_next' => $balanceNext,
                'year_total' => array_sum($months),
                'arrears_months' => $arrearsMonths,
                'arrears_months_count' => count($arrearsMonths),
                'payment_status' => $paymentStatus,
            ];
        });

        $outstandingRows = $rows
            ->filter(fn (array $row) => $row['balance_end'] > 0)
            ->sort(function (array $left, array $right) {
                $statusOrder = [
                    'none' => 0,
                    'partial' => 1,
                    'cleared' => 2,
                    'not_due' => 3,
                ];

                return [
                    $statusOrder[$left['payment_status']] ?? 99,
                    -$left['balance_end'],
                    $left['member']->full_name,
                ] <=> [
                    $statusOrder[$right['payment_status']] ?? 99,
                    -$right['balance_end'],
                    $right['member']->full_name,
                ];
            })
            ->values();

        $monthsList = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec',
        ];

        $data = [
            'rows' => $rows,
            'outstandingRows' => $outstandingRows,
            'monthsList' => $monthsList,
            'year' => $year,
            'asOfMonth' => $asOfMonth,
            'members' => $members,
            'monthlyRate' => $monthlyRate,
        ];

        if ($includePayments) {
            $data['duesPayments'] = Contribution::with(['member', 'receipt'])
                ->where('type', 'Monthly Dues')
                ->whereYear('transaction_date', $year)
                ->orderBy('transaction_date', 'desc')
                ->paginate(15)
                ->withQueryString();
        }

        return $data;
    }

    private function buildArrearsMonths(array $dueMonths, array $months, float $monthlyRate, int $year): array
    {
        if ($monthlyRate <= 0) {
            return [];
        }

        $availableCredit = 0.0;
        $arrears = [];

        foreach ($dueMonths as $monthNumber) {
            $availableCredit += (float) ($months[$monthNumber] ?? 0.0);

            $coveredAmount = min($monthlyRate, $availableCredit);
            $outstandingAmount = round(max(0, $monthlyRate - $coveredAmount), 2);

            if ($outstandingAmount > 0) {
                $arrears[] = [
                    'month' => $monthNumber,
                    'label' => Carbon::create($year, $monthNumber, 1)->format('M Y'),
                    'outstanding' => $outstandingAmount,
                    'is_partial' => $outstandingAmount < $monthlyRate,
                ];
            }

            $availableCredit = round(max(0, $availableCredit - $monthlyRate), 2);
        }

        return $arrears;
    }
}
