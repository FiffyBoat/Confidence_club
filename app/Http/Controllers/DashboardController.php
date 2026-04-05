<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Contribution;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Meeting;
use App\Models\Member;
use App\Models\Receipt;
use App\Models\Setting;
use App\Repositories\MemberRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $role = auth()->user()->role ?? 'viewer';

        if ($role === 'viewer') {
            return $this->viewerDashboard($today);
        }

        $totalMembers = Member::count();
        $totalContributions = Contribution::sum('amount');
        $totalIncome = Income::sum('amount');
        $totalRepayments = LoanRepayment::sum('amount');
        $totalExpenses = Expense::sum('amount');

        $totalBalance = ($totalContributions + $totalIncome + $totalRepayments) - $totalExpenses;

        $monthlyContributions = Contribution::whereMonth('transaction_date', $today->month)
            ->whereYear('transaction_date', $today->year)
            ->sum('amount');

        $monthlyExpenses = Expense::whereMonth('transaction_date', $today->month)
            ->whereYear('transaction_date', $today->year)
            ->sum('amount');

        $activeLoans = Loan::where('balance', '>', 0)
            ->whereDate('due_date', '>=', $today)
            ->count();
        $overdueLoans = Loan::where('balance', '>', 0)
            ->whereDate('due_date', '<', $today)
            ->count();

        $recentReceipts = Receipt::with('member')
            ->latest()
            ->take(5)
            ->get();

        $ccmSummary = [
            'admission' => Contribution::where('type', 'Admission Fee')->sum('amount'),
            'professor' => Contribution::where('type', 'Professor Donation')->sum('amount'),
            'lawyer' => Contribution::where('type', 'Lawyer Donation')->sum('amount'),
            'extra' => Contribution::where('type', 'like', 'Extra Levies%')->sum('amount'),
            'dues' => Contribution::where('type', 'Monthly Dues')->sum('amount'),
        ];
        $ccmSummary['total'] = $ccmSummary['admission']
            + $ccmSummary['professor']
            + $ccmSummary['lawyer']
            + $ccmSummary['extra']
            + $ccmSummary['dues'];

        return view('dashboard.index', compact(
            'totalMembers',
            'totalBalance',
            'monthlyContributions',
            'monthlyExpenses',
            'activeLoans',
            'overdueLoans',
            'recentReceipts',
            'ccmSummary'
        ));
    }

    public function viewer(): \Illuminate\View\View
    {
        return $this->viewerDashboard(Carbon::today());
    }

    public function viewerMembers(Request $request): \Illuminate\View\View
    {
        $membersQuery = Member::query()
            ->select('membership_id', 'full_name', 'phone', 'email', 'status')
            ->orderBy('full_name');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $membersQuery->where(function ($builder) use ($search) {
                $builder->where('membership_id', 'like', '%'.$search.'%')
                    ->orWhere('full_name', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        $members = $membersQuery
            ->orderBy('full_name')
            ->paginate(30)
            ->withQueryString();

        return view('members.public', compact('members'));
    }

    public function viewerMemberSuggestions(Request $request, MemberRepository $members): JsonResponse
    {
        return response()->json([
            'suggestions' => $members->searchSuggestions($request->input('q')),
        ]);
    }

    private function viewerDashboard(Carbon $today)
    {
        $visibility = [
            'constitution' => Setting::getBool('viewer_show_constitution', true),
            'announcements' => Setting::getBool('viewer_show_announcements', true),
            'meetings' => Setting::getBool('viewer_show_meetings', true),
            'directory' => Setting::getBool('viewer_show_directory', true),
            'birthdays' => Setting::getBool('viewer_show_birthdays', true),
            'special_contributions' => Setting::getBool('viewer_show_special_contributions', true),
            'transparency_snapshot' => Setting::getBool('viewer_show_transparency_snapshot', false),
        ];

        $constitutionPath = Setting::getValue('constitution_path');
        $constitutionName = Setting::getValue('constitution_name') ?? 'constitution';
        $constitutionExists = $constitutionPath
            ? Storage::disk('public')->exists($constitutionPath)
            : false;

        $announcements = collect();
        if ($visibility['announcements']) {
            $announcements = Announcement::query()
                ->where('is_active', true)
                ->where(function ($builder) use ($today) {
                    $builder->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', $today->endOfDay());
                })
                ->where(function ($builder) use ($today) {
                    $builder->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', $today->startOfDay());
                })
                ->orderByDesc('starts_at')
                ->orderByDesc('created_at')
                ->take(5)
                ->get();
        }

        $meetings = collect();
        if ($visibility['meetings']) {
            $meetings = Meeting::query()
                ->where('is_active', true)
                ->where('meeting_at', '>=', $today->copy()->startOfDay())
                ->orderBy('meeting_at')
                ->take(5)
                ->get();
        }

        $directory = collect();
        if ($visibility['directory']) {
            $directory = Member::query()
                ->select('id', 'membership_id', 'full_name', 'status')
                ->orderBy('full_name')
                ->take(12)
                ->get();
        }

        $birthdaysThisMonth = collect();
        $birthdaysUpcoming = collect();
        if ($visibility['birthdays']) {
            $birthdaysThisMonth = Member::query()
                ->whereNotNull('birth_month')
                ->whereNotNull('birth_day')
                ->where('birth_month', $today->month)
                ->orderBy('birth_day')
                ->get();

            $birthdaysUpcoming = $this->buildUpcomingBirthdays($today, 7);
        }

        $specialContributions = collect();
        if ($visibility['special_contributions']) {
            $specialContributions = Contribution::query()
                ->with('member')
                ->where('type', 'Special Contribution')
                ->orderByDesc('transaction_date')
                ->orderByDesc('created_at')
                ->take(8)
                ->get();
        }

        $transparency = [
            'total_members' => 0,
            'total_contributions' => 0,
            'total_income' => 0,
            'total_repayments' => 0,
            'total_expenses' => 0,
            'net_balance' => 0,
            'outstanding_loans' => 0,
        ];
        $transparencyVisibility = [
            'total_members' => Setting::getBool('transparency_show_total_members', false),
            'total_contributions' => Setting::getBool('transparency_show_total_contributions', false),
            'total_income' => Setting::getBool('transparency_show_total_income', false),
            'total_repayments' => Setting::getBool('transparency_show_total_repayments', false),
            'total_expenses' => Setting::getBool('transparency_show_total_expenses', false),
            'net_balance' => Setting::getBool('transparency_show_net_balance', false),
            'outstanding_loans' => Setting::getBool('transparency_show_outstanding_loans', false),
        ];

        if ($visibility['transparency_snapshot']) {
            if ($transparencyVisibility['total_members']) {
                $transparency['total_members'] = Member::count();
            }
            if ($transparencyVisibility['total_contributions']) {
                $transparency['total_contributions'] = Contribution::sum('amount');
            }
            if ($transparencyVisibility['total_income']) {
                $transparency['total_income'] = Income::sum('amount');
            }
            if ($transparencyVisibility['total_repayments']) {
                $transparency['total_repayments'] = LoanRepayment::sum('amount');
            }
            if ($transparencyVisibility['total_expenses']) {
                $transparency['total_expenses'] = Expense::sum('amount');
            }
            if ($transparencyVisibility['outstanding_loans']) {
                $transparency['outstanding_loans'] = Loan::sum('balance');
            }
            if ($transparencyVisibility['net_balance']) {
                $transparency['net_balance'] =
                    $transparency['total_contributions']
                    + $transparency['total_income']
                    + $transparency['total_repayments']
                    - $transparency['total_expenses'];
            }
        }

        return view('dashboard.viewer', [
            'today' => $today,
            'visibility' => $visibility,
            'constitutionExists' => $constitutionExists,
            'constitutionName' => $constitutionName,
            'announcements' => $announcements,
            'meetings' => $meetings,
            'directory' => $directory,
            'birthdaysThisMonth' => $birthdaysThisMonth,
            'birthdaysUpcoming' => $birthdaysUpcoming,
            'specialContributions' => $specialContributions,
            'transparency' => $transparency,
            'transparencyVisibility' => $transparencyVisibility,
        ]);
    }

    private function buildUpcomingBirthdays(Carbon $today, int $daysAhead)
    {
        $end = $today->copy()->addDays($daysAhead);
        $members = Member::query()
            ->whereNotNull('birth_month')
            ->whereNotNull('birth_day')
            ->get();

        $upcoming = [];

        foreach ($members as $member) {
            $base = Carbon::create($today->year, $member->birth_month, 1);
            $day = min($member->birth_day, $base->daysInMonth);
            $birthday = $base->copy()->day($day);
            if ($birthday->lessThan($today)) {
                $birthday->addYear();
            }

            if ($birthday->between($today, $end)) {
                $upcoming[] = [
                    'member' => $member,
                    'date' => $birthday,
                ];
            }
        }

        return collect($upcoming)->sortBy('date');
    }
}
