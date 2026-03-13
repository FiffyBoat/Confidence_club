<?php

namespace App\Http\Controllers\Transparency;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransparencyController extends Controller
{
    public function index(): View
    {
        $visibility = [
            'total_members' => Setting::getBool('transparency_show_total_members', false),
            'total_contributions' => Setting::getBool('transparency_show_total_contributions', false),
            'total_income' => Setting::getBool('transparency_show_total_income', false),
            'total_repayments' => Setting::getBool('transparency_show_total_repayments', false),
            'total_expenses' => Setting::getBool('transparency_show_total_expenses', false),
            'net_balance' => Setting::getBool('transparency_show_net_balance', false),
            'outstanding_loans' => Setting::getBool('transparency_show_outstanding_loans', false),
            'monthly_contributions' => Setting::getBool('transparency_show_monthly_contributions', false),
            'monthly_expenses' => Setting::getBool('transparency_show_monthly_expenses', false),
            'expense_breakdown' => Setting::getBool('transparency_show_expense_breakdown', false),
            'loan_summary' => Setting::getBool('transparency_show_loan_summary', false),
        ];

        $showAny = in_array(true, $visibility, true);

        $totalMembers = $visibility['total_members'] ? Member::count() : 0;
        $totalContributions = $visibility['total_contributions'] ? Contribution::sum('amount') : 0;
        $totalIncome = $visibility['total_income'] ? Income::sum('amount') : 0;
        $totalRepayments = $visibility['total_repayments'] ? LoanRepayment::sum('amount') : 0;
        $totalExpenses = $visibility['total_expenses'] ? Expense::sum('amount') : 0;

        $netBalance = 0;
        if ($visibility['net_balance']) {
            $netBalance = ($totalContributions + $totalIncome + $totalRepayments) - $totalExpenses;
        }

        $driver = DB::getDriverName();
        $yearExpr = $driver === 'sqlite' ? "strftime('%Y', transaction_date)" : 'YEAR(transaction_date)';
        $monthExpr = $driver === 'sqlite' ? "strftime('%m', transaction_date)" : 'MONTH(transaction_date)';

        $monthlyContributions = collect();
        if ($visibility['monthly_contributions']) {
            $monthlyContributions = Contribution::selectRaw($yearExpr.' as year, '.$monthExpr.' as month, SUM(amount) as total')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->take(6)
                ->get()
                ->reverse();
        }

        $monthlyExpenses = collect();
        if ($visibility['monthly_expenses']) {
            $monthlyExpenses = Expense::selectRaw($yearExpr.' as year, '.$monthExpr.' as month, SUM(amount) as total')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->take(6)
                ->get()
                ->reverse();
        }

        $expenseBreakdown = collect();
        if ($visibility['expense_breakdown']) {
            $expenseBreakdown = Expense::selectRaw('category, SUM(amount) as total')
                ->groupBy('category')
                ->orderByDesc('total')
                ->get();
        }

        $loanSummary = [
            'total_loans' => 0,
            'total_outstanding' => 0,
            'overdue' => 0,
        ];
        if ($visibility['loan_summary'] || $visibility['outstanding_loans']) {
            $loanSummary = [
                'total_loans' => Loan::count(),
                'total_outstanding' => Loan::sum('balance'),
                'overdue' => Loan::where('balance', '>', 0)->whereDate('due_date', '<', now())->count(),
            ];
        }

        return view('transparency.index', compact(
            'totalMembers',
            'totalContributions',
            'totalIncome',
            'totalRepayments',
            'totalExpenses',
            'netBalance',
            'monthlyContributions',
            'monthlyExpenses',
            'expenseBreakdown',
            'loanSummary',
            'visibility',
            'showAny'
        ));
    }
}
