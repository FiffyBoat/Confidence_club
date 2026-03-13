<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public function createLoan(Member $member, array $data, User $user): Loan
    {
        return DB::transaction(function () use ($member, $data, $user) {
            $principal = (float) $data['principal'];
            $interestRate = (float) $data['interest_rate'];
            $totalPayable = $this->calculateTotalPayable($principal, $interestRate);

            return Loan::create([
                'member_id' => $member->id,
                'principal' => $principal,
                'interest_rate' => $interestRate,
                'total_payable' => $totalPayable,
                'balance' => $totalPayable,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'status' => 'active',
                'recorded_by' => $user->id,
            ]);
        });
    }

    public function recordRepayment(Loan $loan, float $amount, Carbon $paymentDate, User $user): LoanRepayment
    {
        return DB::transaction(function () use ($loan, $amount, $paymentDate, $user) {
            $repayment = LoanRepayment::create([
                'loan_id' => $loan->id,
                'amount' => $amount,
                'payment_date' => $paymentDate,
                'recorded_by' => $user->id,
            ]);

            $newBalance = max(0, (float) $loan->balance - $amount);
            $status = $newBalance <= 0 ? 'completed' : 'active';

            if ($status === 'active' && $loan->due_date && now()->greaterThan($loan->due_date)) {
                $status = 'overdue';
            }

            $loan->update([
                'balance' => $newBalance,
                'status' => $status,
            ]);

            return $repayment;
        });
    }

    public function calculateTotalPayable(float $principal, float $interestRate): float
    {
        return round($principal + ($principal * ($interestRate / 100)), 2);
    }

    public function refreshStatuses(): void
    {
        Loan::where('balance', '<=', 0)->where('status', '!=', 'completed')->update(['status' => 'completed']);

        Loan::where('balance', '>', 0)
            ->whereDate('due_date', '<', now())
            ->update(['status' => 'overdue']);

        Loan::where('balance', '>', 0)
            ->whereDate('due_date', '>=', now())
            ->where('status', 'overdue')
            ->update(['status' => 'active']);
    }
}
