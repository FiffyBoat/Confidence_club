<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLoanRequest;
use App\Models\ActivityLog;
use App\Models\Loan;
use App\Models\Member;
use App\Services\LoanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function __construct(private readonly LoanService $loanService)
    {
    }

    public function index(Request $request): View
    {
        $status = $request->input('status');

        $this->loanService->refreshStatuses();

        $query = Loan::with('member')->latest();
        if ($status) {
            $query->where('status', $status);
        }

        $loans = $query->paginate(15)->withQueryString();

        return view('loans.index', compact('loans', 'status'));
    }

    public function create(): View
    {
        $members = Member::where('status', 'active')->orderBy('full_name')->get();

        return view('loans.create', compact('members'));
    }

    public function store(StoreLoanRequest $request): RedirectResponse
    {
        $member = Member::findOrFail($request->validated('member_id'));
        $loan = $this->loanService->createLoan($member, $request->validated(), $request->user());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Issued Loan',
            'description' => 'Loan issued to member '.$member->membership_id.' ('.$loan->id.')',
        ]);

        return redirect()->route('loans.show', $loan)->with('success', 'Loan issued successfully.');
    }

    public function show(Loan $loan): View
    {
        $this->loanService->refreshStatuses();

        $loan->load([
            'member',
            'repayments' => fn ($query) => $query->latest('payment_date'),
            'repayments.recordedBy',
            'repayments.receipt',
        ]);

        return view('loans.show', compact('loan'));
    }
}
