<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
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

        $query = Expense::orderBy('transaction_date', 'desc');
        if ($search) {
            $query->where('category', 'like', '%'.$search.'%');
        }

        $expenses = $query->paginate(15)->withQueryString();

        return view('expenses.index', compact('expenses', 'search'));
    }

    public function create(): View
    {
        return view('expenses.create');
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
}
