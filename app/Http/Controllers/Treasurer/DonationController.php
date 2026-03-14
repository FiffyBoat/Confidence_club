<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDonationRequest;
use App\Http\Requests\UpdateDonationRequest;
use App\Models\ActivityLog;
use App\Models\Contribution;
use App\Models\Donation;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function index(): View
    {
        $specialContributionGroups = $this->specialContributionGroups();

        $donations = Donation::with(['specialContribution.member'])
            ->orderBy('donation_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('donations.index', compact('specialContributionGroups', 'donations'));
    }

    public function edit(Donation $donation): View
    {
        $specialContributionGroups = $this->specialContributionGroups();

        return view('donations.edit', compact('donation', 'specialContributionGroups'));
    }

    public function store(StoreDonationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $purpose = $this->normalizePurpose($data['special_contribution_purpose']);

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
            $donation = Donation::create([
                'special_contribution_id' => null,
                'expense_id' => null,
                'special_contribution_purpose' => $purpose,
                'donated_amount' => $donatedAmount,
                'remaining_amount' => $remaining,
                'donation_purpose' => $data['donation_purpose'] ?? null,
                'remaining_use' => $data['remaining_use'] ?? null,
                'donation_date' => $data['donation_date'],
                'recorded_by' => $request->user()->id,
            ]);

            if ($donatedAmount > 0) {
                $description = $this->buildExpenseDescription($data, $purpose);
                $expense = Expense::create([
                    'category' => 'Donation',
                    'amount' => $donatedAmount,
                    'description' => $description,
                    'transaction_date' => $data['donation_date'],
                    'recorded_by' => $request->user()->id,
                ]);

                $donation->update(['expense_id' => $expense->id]);
            }
        });

        return redirect()
            ->route('donations.index')
            ->with('success', 'Donation recorded.');
    }

    public function update(UpdateDonationRequest $request, Donation $donation): RedirectResponse
    {
        $data = $request->validated();
        $purpose = $this->normalizePurpose($data['special_contribution_purpose']);

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
                ->route('donations.edit', $donation)
                ->with('status', 'Selected purpose has no special contributions.');
        }

        $alreadyDonated = (float) Donation::where('special_contribution_purpose', $purpose)
            ->where('id', '!=', $donation->id)
            ->sum('donated_amount');
        $available = max(0, $totalCollected - $alreadyDonated);
        $donatedAmount = (float) $data['donated_amount'];

        if ($donatedAmount > $available) {
            return redirect()
                ->route('donations.edit', $donation)
                ->with('status', 'Donated amount cannot exceed the available special contribution balance (GHS '.number_format($available, 2).').');
        }

        $remaining = $available - $donatedAmount;

        DB::transaction(function () use ($data, $purpose, $donatedAmount, $remaining, $donation, $request) {
            $donation->update([
                'special_contribution_purpose' => $purpose,
                'donated_amount' => $donatedAmount,
                'remaining_amount' => $remaining,
                'donation_purpose' => $data['donation_purpose'] ?? null,
                'remaining_use' => $data['remaining_use'] ?? null,
                'donation_date' => $data['donation_date'],
                'recorded_by' => $request->user()->id,
            ]);

            $description = $this->buildExpenseDescription($data, $purpose);
            $expensePayload = [
                'category' => 'Donation',
                'amount' => $donatedAmount,
                'description' => $description,
                'transaction_date' => $data['donation_date'],
                'recorded_by' => $request->user()->id,
            ];

            if ($donation->expense_id) {
                Expense::whereKey($donation->expense_id)->update($expensePayload);
            } else {
                $expense = Expense::create($expensePayload);
                $donation->update(['expense_id' => $expense->id]);
            }

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'Updated Donation',
                'description' => 'Updated donation '.$donation->id,
            ]);
        });

        return redirect()
            ->route('donations.index')
            ->with('success', 'Donation updated.');
    }

    public function destroy(Request $request, Donation $donation): RedirectResponse
    {
        $expenseId = $donation->expense_id;

        DB::transaction(function () use ($donation, $expenseId, $request) {
            $donation->delete();

            if ($expenseId) {
                Expense::whereKey($expenseId)->delete();
            }

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'Deleted Donation',
                'description' => 'Deleted donation '.$donation->id,
            ]);
        });

        return redirect()
            ->route('donations.index')
            ->with('success', 'Donation deleted.');
    }

    private function specialContributionGroups()
    {
        $specialContributions = Contribution::with('member')
            ->where('type', 'Special Contribution')
            ->orderBy('transaction_date', 'desc')
            ->get();

        $donatedByPurpose = Donation::select('special_contribution_purpose', DB::raw('SUM(donated_amount) as total'))
            ->whereNotNull('special_contribution_purpose')
            ->groupBy('special_contribution_purpose')
            ->pluck('total', 'special_contribution_purpose');

        return $specialContributions
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
    }

    private function normalizePurpose(?string $purpose): string
    {
        $normalized = trim((string) $purpose);

        return $normalized !== '' ? $normalized : 'General';
    }

    private function buildExpenseDescription(array $data, string $poolPurpose): string
    {
        $purposeLabel = $data['donation_purpose'] ?? 'General donation';
        $description = 'Donation from Special Contribution Pool ('.$poolPurpose.') - '.$purposeLabel;

        if (! empty($data['remaining_use'])) {
            $description .= '. Remaining: '.$data['remaining_use'];
        }

        return Str::limit($description, 255);
    }
}
