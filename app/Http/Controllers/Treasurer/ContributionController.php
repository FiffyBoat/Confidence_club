<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContributionRequest;
use App\Models\ActivityLog;
use App\Models\Contribution;
use App\Models\Member;
use App\Services\ReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ContributionController extends Controller
{
    public function __construct(private readonly ReceiptService $receiptService)
    {
    }

    public function index(Request $request): View
    {
        $search = $request->input('q');

        $query = Contribution::with(['member', 'receipt'])->orderBy('transaction_date', 'desc');

        if ($search) {
            $query->where(function ($builder) use ($search) {
                $builder->where('type', 'like', '%'.$search.'%')
                    ->orWhereHas('member', function ($memberBuilder) use ($search) {
                        $memberBuilder->where('full_name', 'like', '%'.$search.'%')
                            ->orWhere('membership_id', 'like', '%'.$search.'%');
                    });
            });
        }

        $contributions = $query->paginate(15)->withQueryString();

        return view('contributions.index', compact('contributions', 'search'));
    }

    public function create(): View
    {
        $members = Member::where('status', 'active')->orderBy('full_name')->get();

        return view('contributions.create', compact('members'));
    }

    public function store(StoreContributionRequest $request): RedirectResponse
    {
        $contribution = DB::transaction(function () use ($request) {
            $contribution = Contribution::create([
                'member_id' => $request->validated('member_id'),
                'type' => $request->validated('type'),
                'description' => $request->validated('description'),
                'amount' => $request->validated('amount'),
                'payment_method' => $request->validated('payment_method'),
                'transaction_date' => $request->validated('transaction_date'),
                'recorded_by' => $request->user()->id,
            ]);

            $contribution->load('member');

            $this->receiptService->createForContribution($contribution, $request->user());

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'Recorded Contribution',
                'description' => 'Contribution for member '.$contribution->member?->membership_id.' ('.$contribution->type.')',
            ]);

            return $contribution;
        });

        return redirect()->route('contributions.show', $contribution)->with('success', 'Contribution recorded.');
    }

    public function show(Contribution $contribution): View
    {
        $contribution->load(['member', 'receipt']);

        return view('contributions.show', compact('contribution'));
    }

    public function destroy(Request $request, Contribution $contribution): RedirectResponse
    {
        $contribution->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Deleted Contribution',
            'description' => 'Deleted contribution '.$contribution->id,
        ]);

        return redirect()->route('contributions.index')->with('success', 'Contribution deleted.');
    }
}
