<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncomeRequest;
use App\Models\ActivityLog;
use App\Models\Income;
use App\Services\ReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IncomeController extends Controller
{
    public function __construct(private readonly ReceiptService $receiptService)
    {
    }

    public function index(Request $request): View
    {
        $search = $request->input('q');

        $query = Income::orderBy('transaction_date', 'desc');
        if ($search) {
            $query->where('source', 'like', '%'.$search.'%');
        }

        $incomes = $query->paginate(15)->withQueryString();

        return view('incomes.index', compact('incomes', 'search'));
    }

    public function create(): View
    {
        return view('incomes.create');
    }

    public function store(StoreIncomeRequest $request): RedirectResponse
    {
        $income = DB::transaction(function () use ($request) {
            $income = Income::create([
                'source' => $request->validated('source'),
                'amount' => $request->validated('amount'),
                'description' => $request->validated('description'),
                'transaction_date' => $request->validated('transaction_date'),
                'recorded_by' => $request->user()->id,
            ]);

            $this->receiptService->createForIncome($income, $request->user());

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'Recorded Income',
                'description' => 'Income from '.$income->source,
            ]);

            return $income;
        });

        return redirect()->route('incomes.show', $income)->with('success', 'Income recorded.');
    }

    public function show(Income $income): View
    {
        $income->load('receipt');

        return view('incomes.show', compact('income'));
    }

    public function destroy(Request $request, Income $income): RedirectResponse
    {
        $income->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Deleted Income',
            'description' => 'Deleted income '.$income->id,
        ]);

        return redirect()->route('incomes.index')->with('success', 'Income deleted.');
    }
}
