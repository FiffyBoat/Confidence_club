<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLoanRepaymentRequest;
use App\Models\ActivityLog;
use App\Models\Loan;
use App\Services\LoanService;
use App\Services\ReceiptService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

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
}
