<?php

namespace App\Services;

use App\Models\Contribution;
use App\Models\Member;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MemberStatementService
{
    public function build(Member $member, ?Carbon $asOfDate = null): array
    {
        $asOfDate = ($asOfDate ?? now())->copy()->endOfMonth();
        $monthlyRate = (float) (Setting::getValue('monthly_dues_amount')
            ?? config('ccm.monthly_dues_amount', 50));
        $clubStartDateValue = Setting::getValue('club_start_date')
            ?? config('ccm.club_start_date', '2025-10-01');
        $clubStartDate = Carbon::parse($clubStartDateValue)->startOfMonth();

        $member->loadMissing([
            'contributions' => fn ($query) => $query->with('receipt')->orderByDesc('transaction_date')->orderByDesc('created_at'),
            'loans' => fn ($query) => $query->orderByDesc('issue_date'),
            'loans.repayments' => fn ($query) => $query->with('receipt')->orderByDesc('payment_date')->orderByDesc('created_at'),
        ]);

        $payments = $this->buildPayments($member, $asOfDate);
        $dues = $this->buildDuesSchedule($member, $clubStartDate, $monthlyRate, $asOfDate);

        return [
            'member' => $member,
            'asOfDate' => $asOfDate,
            'monthlyRate' => $monthlyRate,
            'payments' => $payments,
            'unpaidDues' => $dues['unpaid_dues'],
            'duesSchedule' => $dues['schedule'],
            'duesSummary' => $dues['summary'],
            'paymentSummary' => [
                'payment_count' => $payments->count(),
                'total_paid' => (float) $payments->sum('amount'),
                'contributions_paid' => (float) $payments->where('category', 'contribution')->sum('amount'),
                'loan_repayments_paid' => (float) $payments->where('category', 'loan_repayment')->sum('amount'),
            ],
        ];
    }

    private function buildPayments(Member $member, Carbon $asOfDate): Collection
    {
        $contributions = $member->contributions
            ->filter(fn (Contribution $contribution) => $contribution->transaction_date && $contribution->transaction_date->lte($asOfDate))
            ->map(function (Contribution $contribution) {
                return [
                    'category' => 'contribution',
                    'type' => $contribution->type,
                    'description' => $contribution->description ?: $contribution->type,
                    'amount' => (float) $contribution->amount,
                    'date' => $contribution->transaction_date,
                    'receipt' => $contribution->receipt,
                    'record' => $contribution,
                ];
            });

        $repayments = $member->loans
            ->flatMap(function ($loan) use ($asOfDate) {
                return $loan->repayments
                    ->filter(fn ($repayment) => $repayment->payment_date && $repayment->payment_date->lte($asOfDate))
                    ->map(function ($repayment) use ($loan) {
                        return [
                            'category' => 'loan_repayment',
                            'type' => 'Loan Repayment',
                            'description' => 'Repayment for Loan #'.$loan->id,
                            'amount' => (float) $repayment->amount,
                            'date' => $repayment->payment_date,
                            'receipt' => $repayment->receipt,
                            'record' => $repayment,
                        ];
                    });
            });

        return $contributions
            ->concat($repayments)
            ->sort(function (array $left, array $right) {
                $leftDate = $left['date']?->timestamp ?? 0;
                $rightDate = $right['date']?->timestamp ?? 0;

                return [$rightDate, $right['type'], $right['amount']] <=> [$leftDate, $left['type'], $left['amount']];
            })
            ->values();
    }

    private function buildDuesSchedule(Member $member, Carbon $clubStartDate, float $monthlyRate, Carbon $asOfDate): array
    {
        $memberStart = $member->join_date
            ? ($member->join_date->greaterThan($clubStartDate) ? $member->join_date : $clubStartDate)
            : $clubStartDate;
        $memberStart = $memberStart->copy()->startOfMonth();
        $asOfMonth = $asOfDate->copy()->startOfMonth();

        $schedule = collect();

        if ($memberStart->gt($asOfMonth) || $monthlyRate <= 0) {
            return [
                'schedule' => $schedule,
                'unpaid_dues' => $schedule,
                'summary' => [
                    'months_due' => 0,
                    'total_due' => 0.0,
                    'paid_to_date' => 0.0,
                    'outstanding' => 0.0,
                ],
            ];
        }

        $duesPaymentsByMonth = $member->contributions
            ->filter(function (Contribution $contribution) use ($asOfDate) {
                return $contribution->type === 'Monthly Dues'
                    && $contribution->transaction_date
                    && $contribution->transaction_date->lte($asOfDate);
            })
            ->groupBy(fn (Contribution $contribution) => $contribution->transaction_date->format('Y-m'))
            ->map(fn (Collection $items) => (float) $items->sum('amount'));

        $cursor = $memberStart->copy();
        $availableCredit = 0.0;

        while ($cursor->lte($asOfMonth)) {
            $monthKey = $cursor->format('Y-m');
            $availableCredit += (float) ($duesPaymentsByMonth[$monthKey] ?? 0.0);

            $paidAmount = round(min($monthlyRate, $availableCredit), 2);
            $outstandingAmount = round(max(0, $monthlyRate - $paidAmount), 2);

            $schedule->push([
                'month_key' => $monthKey,
                'month_label' => $cursor->format('M Y'),
                'expected' => $monthlyRate,
                'paid' => $paidAmount,
                'outstanding' => $outstandingAmount,
                'status' => $outstandingAmount <= 0
                    ? 'paid'
                    : ($paidAmount > 0 ? 'partial' : 'unpaid'),
            ]);

            $availableCredit = round(max(0, $availableCredit - $monthlyRate), 2);
            $cursor->addMonth();
        }

        $unpaidDues = $schedule->filter(fn (array $month) => $month['outstanding'] > 0)->values();

        return [
            'schedule' => $schedule,
            'unpaid_dues' => $unpaidDues,
            'summary' => [
                'months_due' => $schedule->count(),
                'total_due' => round($schedule->sum('expected'), 2),
                'paid_to_date' => round($schedule->sum('paid'), 2),
                'outstanding' => round($schedule->sum('outstanding'), 2),
            ],
        ];
    }
}
