<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDuesRequest;
use App\Models\Contribution;
use App\Models\Member;
use App\Services\ReceiptService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DuesController extends Controller
{
    public function __construct(private readonly ReceiptService $receiptService)
    {
    }

    public function index(Request $request): View
    {
        $year = (int) $request->input('year', now()->year);
        $asOfMonth = (int) $request->input('as_of', now()->month);
        $asOfMonth = max(1, min(12, $asOfMonth));

        $members = Member::orderBy('full_name')->get();

        $duesContributions = Contribution::where('type', 'Monthly Dues')
            ->whereYear('transaction_date', $year)
            ->get()
            ->groupBy('member_id');

        $rows = $members->map(function (Member $member) use ($duesContributions, $year, $asOfMonth) {
            $months = [];
            for ($m = 1; $m <= 12; $m++) {
                $months[$m] = 0.0;
            }

            foreach ($duesContributions->get($member->id, collect()) as $contribution) {
                $monthIndex = (int) $contribution->transaction_date?->format('n');
                if ($monthIndex >= 1 && $monthIndex <= 12) {
                    $months[$monthIndex] += (float) $contribution->amount;
                }
            }

            $paidToAsOf = 0.0;
            for ($m = 1; $m <= $asOfMonth; $m++) {
                $paidToAsOf += $months[$m];
            }

            $dueToAsOf = $asOfMonth * 50;
            $balanceEnd = max(0, $dueToAsOf - $paidToAsOf);
            $balanceNext = $balanceEnd + 50;

            return [
                'member' => $member,
                'months' => $months,
                'paid_to_as_of' => $paidToAsOf,
                'balance_end' => $balanceEnd,
                'balance_next' => $balanceNext,
                'year_total' => array_sum($months),
            ];
        });

        $monthsList = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec',
        ];

        return view('dues.index', compact('rows', 'monthsList', 'year', 'asOfMonth', 'members'));
    }

    public function store(StoreDuesRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $month = (int) $data['month'];
        $year = (int) $data['year'];
        $transactionDate = $data['transaction_date'] ?? Carbon::create($year, $month, 1)->toDateString();

        $contribution = Contribution::create([
            'member_id' => $data['member_id'],
            'type' => 'Monthly Dues',
            'description' => 'Monthly dues for '.Carbon::create($year, $month, 1)->format('F Y'),
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'transaction_date' => $transactionDate,
            'recorded_by' => $request->user()->id,
        ]);

        $contribution->load('member');
        $this->receiptService->createForContribution($contribution, $request->user());

        return redirect()
            ->route('dues.index', ['year' => $year, 'as_of' => $month])
            ->with('success', 'Monthly dues recorded.');
    }
}
