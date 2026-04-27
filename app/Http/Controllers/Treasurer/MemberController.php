<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\ActivityLog;
use App\Models\Contribution;
use App\Models\Member;
use App\Repositories\MemberRepository;
use App\Services\MemberStatementService;
use App\Services\ReceiptService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function __construct(
        private readonly MemberRepository $members,
        private readonly ReceiptService $receiptService,
        private readonly MemberStatementService $memberStatementService
    )
    {
    }

    public function index(Request $request): View
    {
        $search = $request->input('q');
        $admissionFilter = $request->input('admission_fee');
        $role = $request->user()->role ?? 'viewer';
        $canViewPayments = in_array($role, ['admin', 'treasurer'], true);
        $members = $this->members->paginateWithSearch($search, 15, $canViewPayments, $admissionFilter);

        return view('members.index', compact('members', 'search', 'canViewPayments', 'admissionFilter'));
    }

    public function suggestions(Request $request): JsonResponse
    {
        $suggestions = $this->members->searchSuggestions($request->input('q'));

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }

    public function create(): View
    {
        return view('members.create');
    }

    public function store(StoreMemberRequest $request): RedirectResponse
    {
        $member = Member::create($request->safe()->only([
            'membership_id',
            'full_name',
            'phone',
            'email',
            'status',
            'join_date',
            'birth_month',
            'birth_day',
        ]));

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Created Member',
            'description' => 'Created member '.$member->full_name.' ('.$member->membership_id.')',
        ]);

        if ($request->boolean('record_admission_fee')) {
            DB::transaction(function () use ($member, $request) {
                $contribution = Contribution::create([
                    'member_id' => $member->id,
                    'type' => 'Admission Fee',
                    'description' => 'Membership admission fee',
                    'amount' => 200,
                    'payment_method' => $request->validated('admission_payment_method'),
                    'transaction_date' => $request->validated('admission_transaction_date'),
                    'recorded_by' => $request->user()->id,
                ]);

                $contribution->load('member');
                $this->receiptService->createForContribution($contribution, $request->user());
            });
        }

        return redirect()->route('members.index')->with('success', 'Member created successfully.');
    }

    public function show(Member $member): View
    {
        $statement = $this->memberStatementService->build($member);
        $hasAdmissionFee = $member->contributions->contains('type', 'Admission Fee');

        return view('members.show', compact('member', 'hasAdmissionFee', 'statement'));
    }

    public function statementPrint(Member $member): Response
    {
        $statement = $this->memberStatementService->build($member);
        $pdf = Pdf::loadView('members.statement_pdf', array_merge($statement, [
            'generatedAt' => now(),
            'downloadMode' => false,
        ]));

        $filename = 'member_statement_'.$member->membership_id.'_'.$statement['asOfDate']->format('Ymd').'.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    public function statementPdf(Member $member): Response
    {
        $statement = $this->memberStatementService->build($member);
        $pdf = Pdf::loadView('members.statement_pdf', array_merge($statement, [
            'generatedAt' => now(),
            'downloadMode' => true,
        ]));

        $filename = 'member_statement_'.$member->membership_id.'_'.$statement['asOfDate']->format('Ymd').'.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function edit(Member $member): View
    {
        $hasAdmissionFee = $member->contributions()
            ->where('type', 'Admission Fee')
            ->exists();

        return view('members.edit', compact('member', 'hasAdmissionFee'));
    }

    public function update(UpdateMemberRequest $request, Member $member): RedirectResponse
    {
        $member->update($request->validated());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Updated Member',
            'description' => 'Updated member '.$member->full_name.' ('.$member->membership_id.')',
        ]);

        $hasAdmissionFee = $member->contributions()
            ->where('type', 'Admission Fee')
            ->exists();

        if (! $hasAdmissionFee && $request->boolean('record_admission_fee')) {
            DB::transaction(function () use ($member, $request) {
                $contribution = Contribution::create([
                    'member_id' => $member->id,
                    'type' => 'Admission Fee',
                    'description' => 'Membership admission fee',
                    'amount' => 200,
                    'payment_method' => $request->validated('admission_payment_method'),
                    'transaction_date' => $request->validated('admission_transaction_date'),
                    'recorded_by' => $request->user()->id,
                ]);

                $contribution->load('member');
                $this->receiptService->createForContribution($contribution, $request->user());

                ActivityLog::create([
                    'user_id' => $request->user()->id,
                    'action' => 'Recorded Admission Fee',
                    'description' => 'Recorded admission fee for '.$member->full_name.' ('.$member->membership_id.')',
                ]);
            });
        }

        return redirect()->route('members.show', $member)->with('success', 'Member updated successfully.');
    }

    public function destroy(Request $request, Member $member): RedirectResponse
    {
        $member->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Deleted Member',
            'description' => 'Deleted member '.$member->full_name.' ('.$member->membership_id.')',
        ]);

        return redirect()->route('members.index')->with('success', 'Member deleted.');
    }

    public function forceDestroy(Request $request, Member $member): RedirectResponse
    {
        $member->forceDelete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Permanently Deleted Member',
            'description' => 'Permanently deleted member '.$member->full_name.' ('.$member->membership_id.')',
        ]);

        return redirect()->route('members.index')->with('success', 'Member permanently deleted.');
    }
}
