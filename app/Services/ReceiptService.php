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
use Illuminate\Support\Str;

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
            $receipt = Receipt::create([
                'receipt_number' => 'TMP-'.Str::uuid(),
                'member_id' => $memberId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'amount' => $amount,
                'generated_by' => $generatedBy,
                'pdf_path' => '',
            ]);

            $receiptNumber = $this->receiptNumberForId($receipt->id);

            $pdf = Pdf::loadView('receipts.pdf', array_merge($payload, [
                'receiptNumber' => $receiptNumber,
                'amount' => $amount,
            ]));

            $path = 'receipts/'.$receiptNumber.'.pdf';
            Storage::disk('public')->put($path, $pdf->output());

            $receipt->update([
                'receipt_number' => $receiptNumber,
                'pdf_path' => $path,
            ]);

            return $receipt->fresh();
        });
    }

    private function receiptNumberForId(int $receiptId): string
    {
        $year = now()->format('Y');

        return 'REC-'.$year.'-'.str_pad((string) $receiptId, 5, '0', STR_PAD_LEFT);
    }

    public function regeneratePdf(Receipt $receipt): ?string
    {
        $payload = $this->buildPayloadFromReceipt($receipt);

        if (! $payload) {
            return null;
        }

        $path = $receipt->pdf_path ?: 'receipts/'.$receipt->receipt_number.'.pdf';
        $pdf = Pdf::loadView('receipts.pdf', array_merge($payload, [
            'receiptNumber' => $receipt->receipt_number,
            'amount' => $receipt->amount,
        ]));

        Storage::disk('public')->put($path, $pdf->output());

        if ($receipt->pdf_path !== $path) {
            $receipt->update(['pdf_path' => $path]);
        }

        return $path;
    }

    private function buildPayloadFromReceipt(Receipt $receipt): ?array
    {
        if ($receipt->reference_type === Receipt::TYPE_CONTRIBUTION) {
            $contribution = Contribution::withTrashed()
                ->with(['member', 'recordedBy'])
                ->find($receipt->reference_id);

            if (! $contribution) {
                return null;
            }

            return [
                'title' => 'Contribution Receipt',
                'member' => $contribution->member,
                'record' => $contribution,
                'reference' => $contribution->type,
                'paymentType' => $contribution->type,
                'paymentMethod' => $contribution->payment_method,
                'description' => $contribution->description,
                'transactionDate' => $contribution->transaction_date,
                'recordedBy' => $contribution->recordedBy?->name,
            ];
        }

        if ($receipt->reference_type === Receipt::TYPE_INCOME) {
            $income = Income::withTrashed()
                ->with('recordedBy')
                ->find($receipt->reference_id);

            if (! $income) {
                return null;
            }

            return [
                'title' => 'Income Receipt',
                'member' => null,
                'record' => $income,
                'reference' => $income->source,
                'paymentType' => 'Income',
                'paymentMethod' => null,
                'description' => $income->description,
                'transactionDate' => $income->transaction_date,
                'recordedBy' => $income->recordedBy?->name,
            ];
        }

        if ($receipt->reference_type === Receipt::TYPE_LOAN_REPAYMENT) {
            $repayment = LoanRepayment::withTrashed()
                ->with(['loan.member', 'recordedBy'])
                ->find($receipt->reference_id);

            if (! $repayment) {
                return null;
            }

            return [
                'title' => 'Loan Repayment Receipt',
                'member' => $repayment->loan?->member,
                'record' => $repayment,
                'reference' => 'Loan #'.$repayment->loan_id,
                'paymentType' => 'Loan Repayment',
                'paymentMethod' => null,
                'description' => 'Repayment for Loan #'.$repayment->loan_id,
                'transactionDate' => $repayment->payment_date,
                'recordedBy' => $repayment->recordedBy?->name,
            ];
        }

        return null;
    }
}
