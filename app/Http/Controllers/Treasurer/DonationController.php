<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDonationRequest;
use App\Models\Contribution;
use App\Models\Donation;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function index(): View
    {
        $specialContributions = Contribution::with('member')
            ->where('type', 'Special Contribution')
            ->orderBy('transaction_date', 'desc')
            ->get();

        $donatedByPurpose = Donation::select('special_contribution_purpose', DB::raw('SUM(donated_amount) as total'))
            ->whereNotNull('special_contribution_purpose')
            ->groupBy('special_contribution_purpose')
            ->pluck('total', 'special_contribution_purpose');

        $specialContributionGroups = $specialContributions
            ->groupBy(function (Contribution $contribution) {
                $label = trim((string) ($contribution->description ?? ''));
                return $label !== '' ? $label : 'General';
            })
            ->map(function ($group, string $label) use ($donatedByPurpose) {
                $total = (float) $group->sum('amount');
                $donated = (float) ($donatedByPurpose[$label] ?? 0);
                $remaining = max(0, $total - $donated);

                return [
                    'label' => $label,
                    'total' => $total,
                    'donated' => $donated,
                    'remaining' => $remaining,
                    'count' => $group->count(),
                ];
            })
            ->values();

        $donations = Donation::with(['specialContribution.member'])
            ->orderBy('donation_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('donations.index', compact('specialContributionGroups', 'donations'));
    }

    public function store(StoreDonationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $purpose = trim((string) $data['special_contribution_purpose']);

        $totalQuery = Contribution::where('type', 'Special Contribution');
        if ($purpose === 'General') {
            $totalQuery->where(function ($query) {
                $query->whereNull('description')->orWhere('description', '');
            });
        } else {
            $totalQuery->where('description', $purpose);
        }
        $totalCollected = (float) $totalQuery->sum('amount');

        if ($totalCollected <= 0) {
            return redirect()
                ->route('donations.index')
                ->with('status', 'Selected purpose has no special contributions.');
        }

        $alreadyDonated = (float) Donation::where('special_contribution_purpose', $purpose)->sum('donated_amount');
        $available = max(0, $totalCollected - $alreadyDonated);
        $donatedAmount = (float) $data['donated_amount'];

        if ($donatedAmount > $available) {
            return redirect()
                ->route('donations.index')
                ->with('status', 'Donated amount cannot exceed the available special contribution balance (GHS '.number_format($available, 2).').');
        }

        $remaining = $available - $donatedAmount;

        DB::transaction(function () use ($data, $purpose, $donatedAmount, $remaining, $request) {
            Donation::create([
                'special_contribution_id' => null,
                'special_contribution_purpose' => $purpose,
                'donated_amount' => $donatedAmount,
                'remaining_amount' => $remaining,
                'donation_purpose' => $data['donation_purpose'] ?? null,
                'remaining_use' => $data['remaining_use'] ?? null,
                'donation_date' => $data['donation_date'],
                'recorded_by' => $request->user()->id,
            ]);

            if ($donatedAmount > 0) {
                $purpose = $data['donation_purpose'] ?? 'General donation';
                $description = 'Donation from Special Contribution Pool ('.$data['special_contribution_purpose'].') - '.$purpose;
                if (! empty($data['remaining_use'])) {
                    $description .= '. Remaining: '.$data['remaining_use'];
                }

                Expense::create([
                    'category' => 'Donation',
                    'amount' => $donatedAmount,
                    'description' => $description,
                    'transaction_date' => $data['donation_date'],
                    'recorded_by' => $request->user()->id,
                ]);
            }
        });

        return redirect()
            ->route('donations.index')
            ->with('success', 'Donation recorded.');
    }
}
