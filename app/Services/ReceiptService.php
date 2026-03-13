<?php

namespace App\Services;

use App\Models\Contribution;
use App\Models\Income;
use App\Models\LoanRepayment;
use App\Models\Receipt;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReceiptService
{
    public function createForContribution(Contribution $contribution, User $user): Receipt
    {
        return $this->createReceipt(
            memberId: $contribution->member_id,
            amount: $contribution->amount,
            referenceType: Receipt::TYPE_CONTRIBUTION,
            referenceId: $contribution->id,
            generatedBy: $user->id,
            payload: [
                'title' => 'Contribution Receipt',
                'member' => $contribution->member,
                'record' => $contribution,
                'reference' => $contribution->type,
                'paymentType' => $contribution->type,
                'paymentMethod' => $contribution->payment_method,
                'description' => $contribution->description,
                'transactionDate' => $contribution->transaction_date,
                'recordedBy' => $user->name,
            ]
        );
    }

    public function createForIncome(Income $income, User $user): Receipt
    {
        return $this->createReceipt(
            memberId: null,
            amount: $income->amount,
            referenceType: Receipt::TYPE_INCOME,
            referenceId: $income->id,
            generatedBy: $user->id,
            payload: [
                'title' => 'Income Receipt',
                'member' => null,
                'record' => $income,
                'reference' => $income->source,
                'paymentType' => 'Income',
                'paymentMethod' => null,
                'description' => $income->description,
                'transactionDate' => $income->transaction_date,
                'recordedBy' => $user->name,
            ]
        );
    }

    public function createForLoanRepayment(LoanRepayment $repayment, User $user): Receipt
    {
        return $this->createReceipt(
            memberId: $repayment->loan->member_id,
            amount: $repayment->amount,
            referenceType: Receipt::TYPE_LOAN_REPAYMENT,
            referenceId: $repayment->id,
            generatedBy: $user->id,
            payload: [
                'title' => 'Loan Repayment Receipt',
                'member' => $repayment->loan->member,
                'record' => $repayment,
                'reference' => 'Loan #'.$repayment->loan_id,
                'paymentType' => 'Loan Repayment',
                'paymentMethod' => null,
                'description' => 'Repayment for Loan #'.$repayment->loan_id,
                'transactionDate' => $repayment->payment_date,
                'recordedBy' => $user->name,
            ]
        );
    }

    private function createReceipt(
        ?int $memberId,
        float $amount,
        string $referenceType,
        int $referenceId,
        int $generatedBy,
        array $payload
    ): Receipt {
        return DB::transaction(function () use ($memberId, $amount, $referenceType, $referenceId, $generatedBy, $payload) {
            $receiptNumber = $this->nextReceiptNumber();

            $pdf = Pdf::loadView('receipts.pdf', array_merge($payload, [
                'receiptNumber' => $receiptNumber,
                'amount' => $amount,
            ]));

            $path = 'receipts/'.$receiptNumber.'.pdf';
            Storage::disk('public')->put($path, $pdf->output());

            return Receipt::create([
                'receipt_number' => $receiptNumber,
                'member_id' => $memberId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'amount' => $amount,
                'generated_by' => $generatedBy,
                'pdf_path' => $path,
            ]);
        });
    }

    private function nextReceiptNumber(): string
    {
        $sequence = (int) Receipt::max('id') + 1;
        $year = now()->format('Y');

        return 'REC-'.$year.'-'.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }
}
