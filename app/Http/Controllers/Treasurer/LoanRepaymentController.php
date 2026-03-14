<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLoanRepaymentRequest;
use App\Http\Requests\UpdateLoanRepaymentRequest;
use App\Models\ActivityLog;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Services\LoanService;
use App\Services\ReceiptService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LoanRepaymentController extends Controller
{
    public function __construct(
        private readonly LoanService $loanService,
        private readonly ReceiptService $receiptService
    ) {
    }

    public function store(StoreLoanRepaymentRequest $request): RedirectResponse
    {
        $loan = Loan::with('member')->findOrFail($request->validated('loan_id'));

        if ((float) $request->validated('amount') > (float) $loan->balance) {
            return back()->withErrors([
                'amount' => 'Repayment exceeds the remaining balance.',
            ])->withInput();
        }

        DB::transaction(function () use ($loan, $request) {
            $repayment = $this->loanService->recordRepayment(
                $loan,
                (float) $request->validated('amount'),
                Carbon::parse($request->validated('payment_date')),
                $request->user()
            );

            $this->receiptService->createForLoanRepayment($repayment, $request->user());

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'Recorded Loan Repayment',
                'description' => 'Repayment for loan '.$loan->id.' (member '.$loan->member->membership_id.')',
            ]);
        });

        return redirect()->route('loans.show', $loan)->with('success', 'Repayment recorded.');
    }

    public function edit(LoanRepayment $loanRepayment): View
    {
        $loanRepayment->load(['loan.member']);

        return view('loan_repayments.edit', [
            'repayment' => $loanRepayment,
            'loan' => $loanRepayment->loan,
        ]);
    }

    public function update(UpdateLoanRepaymentRequest $request, LoanRepayment $loanRepayment): RedirectResponse
    {
        $loanRepayment->load(['loan.member']);
        $loan = $loanRepayment->loan;

        $amount = (float) $request->validated('amount');
        $maxAllowed = (float) $loan->balance + (float) $loanRepayment->amount;

        if ($amount > $maxAllowed) {
            return back()->withErrors([
                'amount' => 'Repayment exceeds the remaining balance.',
            ])->withInput();
        }

        DB::transaction(function () use ($request, $loanRepayment, $loan, $amount) {
            $loanRepayment->update([
                'amount' => $amount,
                'payment_date' => Carbon::parse($request->validated('payment_date')),
            ]);

            $this->loanService->recalculateBalance($loan);

            if ($loanRepayment->receipt) {
                $loanRepayment->receipt->update(['amount' => $loanRepayment->amount]);
                $this->receiptService->regeneratePdf($loanRepayment->receipt);
            }

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'Updated Loan Repayment',
                'description' => 'Updated repayment '.$loanRepayment->id.' for loan '.$loan->id,
            ]);
        });

        return redirect()->route('loans.show', $loan)->with('success', 'Repayment updated.');
    }

    public function destroy(Request $request, LoanRepayment $loanRepayment): RedirectResponse
    {
        $loanRepayment->load('loan');
        $loan = $loanRepayment->loan;
        $receipt = $loanRepayment->receipt;

        DB::transaction(function () use ($loanRepayment, $loan, $receipt, $request) {
            $loanRepayment->delete();

            $this->loanService->recalculateBalance($loan);

            if ($receipt) {
                $path = $receipt->pdf_path;
                $receipt->delete();

                if ($path) {
                    Storage::disk('public')->delete($path);
                }
            }

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'Deleted Loan Repayment',
                'description' => 'Deleted repayment '.$loanRepayment->id.' for loan '.$loan->id,
            ]);
        });

        return redirect()->route('loans.show', $loan)->with('success', 'Repayment deleted.');
    }
}
