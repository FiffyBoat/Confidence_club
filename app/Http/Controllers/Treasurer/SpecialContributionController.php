<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSpecialContributionRequest;
use App\Models\Contribution;
use App\Models\Member;
use App\Services\ReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SpecialContributionController extends Controller
{
    public function __construct(private readonly ReceiptService $receiptService)
    {
    }

    public function index(): View
    {
        $members = Member::orderBy('full_name')->get();
        $specialContributions = Contribution::with(['member', 'receipt'])
            ->where('type', 'Special Contribution')
            ->orderBy('transaction_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('special_contributions.index', compact('members', 'specialContributions'));
    }

    public function store(StoreSpecialContributionRequest $request): RedirectResponse
    {
        $contribution = Contribution::create([
            'member_id' => $request->validated('member_id'),
            'type' => 'Special Contribution',
            'description' => $request->validated('description'),
            'amount' => $request->validated('amount'),
            'payment_method' => $request->validated('payment_method'),
            'transaction_date' => $request->validated('transaction_date'),
            'recorded_by' => $request->user()->id,
        ]);

        $contribution->load('member');
        $this->receiptService->createForContribution($contribution, $request->user());

        return redirect()
            ->route('special-contributions.index')
            ->with('success', 'Special contribution recorded.');
    }
}
