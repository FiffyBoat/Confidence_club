<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Carbon\Carbon;
use Illuminate\View\View;

class BirthdayController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $windowDays = 7;

        $members = Member::query()
            ->whereNotNull('birth_month')
            ->whereNotNull('birth_day')
            ->orderBy('full_name')
            ->get();

        $resolveBirthdayDate = function (int $month, int $day, int $year) {
            if ($month === 2 && $day === 29 && ! Carbon::create($year, 1, 1)->isLeapYear()) {
                $day = 28;
            }
            return Carbon::create($year, $month, $day);
        };

        $birthdaysToday = [];
        $birthdaysUpcoming = [];
        $allBirthdays = [];

        foreach ($members as $member) {
            $month = (int) $member->birth_month;
            $day = (int) $member->birth_day;

            $next = $resolveBirthdayDate($month, $day, $today->year);
            if ($next->lt($today)) {
                $next = $resolveBirthdayDate($month, $day, $today->year + 1);
            }

            $daysAway = (int) $today->diffInDays($next, false);

            $entry = [
                'member' => $member,
                'date' => $next,
                'days' => $daysAway,
            ];

            if ($daysAway === 0) {
                $birthdaysToday[] = $entry;
            } elseif ($daysAway > 0 && $daysAway <= $windowDays) {
                $birthdaysUpcoming[] = $entry;
            }

            $allBirthdays[] = $entry;
        }

        usort($birthdaysUpcoming, fn ($a, $b) => $a['days'] <=> $b['days']);
        usort($allBirthdays, fn ($a, $b) => $a['days'] <=> $b['days']);

        return view('birthdays.index', [
            'birthdaysToday' => $birthdaysToday,
            'birthdaysUpcoming' => $birthdaysUpcoming,
            'allBirthdays' => $allBirthdays,
            'windowDays' => $windowDays,
            'today' => $today,
        ]);
    }
}
