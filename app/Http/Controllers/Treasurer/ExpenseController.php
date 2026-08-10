<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\ActivityLog;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('q');
        $year = (int) $request->input('year', now()->year);

        $query = Expense::query()
            ->whereYear('transaction_date', $year);

        if ($search) {
            $query->where('category', 'like', '%'.$search.'%');
        }

        $totalExpenses = (clone $query)->sum('amount');
        $availableYears = Expense::query()
            ->selectRaw($this->yearExpression().' as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($value) => (int) $value)
            ->filter()
            ->values();

        if (! $availableYears->contains($year)) {
            $availableYears->prepend($year);
            $availableYears = $availableYears->unique()->sortDesc()->values();
        }

        $query->orderBy('transaction_date', 'desc');
        $expenses = $query->paginate(15)->withQueryString();

        return view('expenses.index', compact('expenses', 'search', 'year', 'availableYears', 'totalExpenses'));
    }

    public function create(): View
    {
        return view('expenses.create');
    }

    public function edit(Expense $expense): View
    {
        return view('expenses.edit', compact('expense'));
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $expense = DB::transaction(function () use ($request) {
            $expense = Expense::create([
                'category' => $request->validated('category'),
                'amount' => $request->validated('amount'),
                'description' => $request->validated('description'),
                'transaction_date' => $request->validated('transaction_date'),
                'recorded_by' => $request->user()->id,
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'Recorded Expense',
                'description' => 'Expense for '.$expense->category,
            ]);

            return $expense;
        });

        return redirect()->route('expenses.show', $expense)->with('success', 'Expense recorded.');
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        DB::transaction(function () use ($request, $expense) {
            $expense->update([
                'category' => $request->validated('category'),
                'amount' => $request->validated('amount'),
                'description' => $request->validated('description'),
                'transaction_date' => $request->validated('transaction_date'),
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'Updated Expense',
                'description' => 'Updated expense '.$expense->id,
            ]);
        });

        return redirect()->route('expenses.show', $expense)->with('success', 'Expense updated.');
    }

    public function show(Expense $expense): View
    {
        return view('expenses.show', compact('expense'));
    }

    public function destroy(Request $request, Expense $expense): RedirectResponse
    {
        $expense->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Deleted Expense',
            'description' => 'Deleted expense '.$expense->id,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }

    private function yearExpression(): string
    {
        return match (DB::getDriverName()) {
            'sqlite' => "strftime('%Y', transaction_date)",
            'pgsql' => 'EXTRACT(YEAR FROM transaction_date)',
            default => 'YEAR(transaction_date)',
        };
    }
}
