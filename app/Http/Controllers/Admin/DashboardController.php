<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Contribution;
use App\Models\Expense;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::count();
        $adminUsers = User::where('role', 'admin')->where('is_active', true)->count();
        $treasurerUsers = User::where('role', 'treasurer')->where('is_active', true)->count();

        $totalMembers = Member::count();
        $totalContributions = Contribution::sum('amount');
        $totalExpenses = Expense::sum('amount');
        $outstandingLoans = Loan::sum('balance');

        $recentLogs = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'adminUsers',
            'treasurerUsers',
            'totalMembers',
            'totalContributions',
            'totalExpenses',
            'outstandingLoans',
            'recentLogs'
        ));
    }
}
