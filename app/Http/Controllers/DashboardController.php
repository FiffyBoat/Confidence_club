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
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    private const BIRTHDAY_QUOTES = [
        'Today is a beautiful reminder of how much light one life can bring to a whole community.',
        'A birthday is the perfect time to celebrate grace, growth, and the gift of togetherness.',
        'May today be filled with laughter, kindness, and the warmth of people who value you deeply.',
        'Every birthday is a fresh chapter, and we are grateful to witness your journey with you.',
        'The club celebrates not just a birthday today, but the joy and strength you bring to us all.',
    ];

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
        $birthdaysToday = collect();
        $birthdaysUpcoming = collect();
        $birthdaySpotlight = null;
        if ($visibility['birthdays']) {
            $messageTemplate = Setting::getValue(
                'birthday_message_template',
                "On behalf of the Confidence Club Members, we celebrate :name today. May this new year of life bring joy, good health, strength, and abundant blessings."
            );

            $birthdaysThisMonth = Member::query()
                ->whereNotNull('birth_month')
                ->whereNotNull('birth_day')
                ->where('birth_month', $today->month)
                ->orderBy('birth_day')
                ->get();

            $birthdaysToday = $this->buildTodayBirthdays($today, $messageTemplate);
            $birthdaysUpcoming = $this->buildUpcomingBirthdays($today, 7, $messageTemplate);
            $birthdaysThisMonth = $this->buildMonthlyBirthdays($birthdaysThisMonth, $today, $messageTemplate);
            $birthdaySpotlight = $birthdaysToday->first() ?? $birthdaysUpcoming->first();
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
            'birthdaysToday' => $birthdaysToday,
            'birthdaysUpcoming' => $birthdaysUpcoming,
            'birthdaySpotlight' => $birthdaySpotlight,
            'specialContributions' => $specialContributions,
            'transparency' => $transparency,
            'transparencyVisibility' => $transparencyVisibility,
        ]);
    }

    private function buildTodayBirthdays(Carbon $today, string $messageTemplate)
    {
        $members = Member::query()
            ->whereNotNull('birth_month')
            ->whereNotNull('birth_day')
            ->orderBy('full_name')
            ->get();

        $todayBirthdays = [];

        foreach ($members as $member) {
            $birthday = $this->resolveBirthdayDate($today->year, (int) $member->birth_month, (int) $member->birth_day);

            if ($birthday->isSameDay($today)) {
                $todayBirthdays[] = $this->buildBirthdayEntry($member, $birthday, 0, $messageTemplate, $today);
            }
        }

        return collect($todayBirthdays);
    }

    private function buildUpcomingBirthdays(Carbon $today, int $daysAhead, string $messageTemplate)
    {
        $end = $today->copy()->addDays($daysAhead);
        $members = Member::query()
            ->whereNotNull('birth_month')
            ->whereNotNull('birth_day')
            ->get();

        $upcoming = [];

        foreach ($members as $member) {
            $birthday = $this->resolveBirthdayDate($today->year, (int) $member->birth_month, (int) $member->birth_day);
            if ($birthday->lessThan($today)) {
                $birthday = $this->resolveBirthdayDate($today->year + 1, (int) $member->birth_month, (int) $member->birth_day);
            }

            if ($birthday->greaterThan($today) && $birthday->lessThanOrEqualTo($end)) {
                $upcoming[] = $this->buildBirthdayEntry(
                    $member,
                    $birthday,
                    (int) $today->diffInDays($birthday),
                    $messageTemplate,
                    $today
                );
            }
        }

        return collect($upcoming)->sortBy('date');
    }

    private function buildMonthlyBirthdays($members, Carbon $today, string $messageTemplate)
    {
        return $members
            ->map(function ($member) use ($today, $messageTemplate) {
                $birthday = $this->resolveBirthdayDate($today->year, (int) $member->birth_month, (int) $member->birth_day);
                $days = $birthday->lessThan($today) ? null : (int) $today->diffInDays($birthday);

                return $this->buildBirthdayEntry($member, $birthday, $days, $messageTemplate, $today);
            })
            ->values();
    }

    private function resolveBirthdayDate(int $year, int $month, int $day): Carbon
    {
        if ($month === 2 && $day === 29 && ! Carbon::create($year, 1, 1)->isLeapYear()) {
            $day = 28;
        }

        return Carbon::create($year, $month, $day);
    }

    private function buildBirthdayEntry(Member $member, Carbon $date, ?int $days, string $messageTemplate, Carbon $today): array
    {
        $quote = $this->resolveBirthdayQuote($member, $today);
        $message = $this->buildBirthdayMessage($member, $messageTemplate);
        $shareText = trim($message."\n\n".$quote."\n\nWith love from Confidence Club Members.");

        return [
            'member' => $member,
            'date' => $date,
            'days' => $days,
            'initials' => $this->extractInitials($member->full_name),
            'message' => $message,
            'quote' => $quote,
            'share_text' => $shareText,
            'whatsapp_url' => $this->buildWhatsAppUrl($member->phone, $shareText),
        ];
    }

    private function buildBirthdayMessage(Member $member, string $messageTemplate): string
    {
        return str_replace(':name', $member->full_name, $messageTemplate);
    }

    private function resolveBirthdayQuote(Member $member, Carbon $today): string
    {
        $index = abs(crc32($member->full_name.$today->toDateString())) % count(self::BIRTHDAY_QUOTES);

        return self::BIRTHDAY_QUOTES[$index];
    }

    private function extractInitials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $initials = '';

        foreach (array_slice($parts, 0, 2) as $part) {
            if ($part !== '') {
                $initials .= strtoupper(substr($part, 0, 1));
            }
        }

        return $initials !== '' ? $initials : 'CC';
    }

    private function buildWhatsAppUrl(?string $phone, string $message): string
    {
        $encodedMessage = rawurlencode($message);
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits !== '') {
            if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
                $digits = '233'.substr($digits, 1);
            }

            if (strlen($digits) >= 10) {
                return 'https://wa.me/'.$digits.'?text='.$encodedMessage;
            }
        }

        return 'https://wa.me/?text='.$encodedMessage;
    }
}
