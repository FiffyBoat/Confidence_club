<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use App\Models\Income;
use App\Models\LoanRepayment;
use App\Models\Receipt;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('q');
        $paymentType = $request->input('payment_type');

        $query = Receipt::with('member')->latest();
        if ($search) {
            $query->where(function ($builder) use ($search) {
                $builder->where('receipt_number', 'like', '%'.$search.'%')
                    ->orWhereHas('member', function ($memberBuilder) use ($search) {
                        $memberBuilder->where('full_name', 'like', '%'.$search.'%')
                            ->orWhere('membership_id', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($paymentType) {
            $query->where(function ($builder) use ($paymentType) {
                switch ($paymentType) {
                    case 'income':
                        $builder->where('reference_type', Receipt::TYPE_INCOME);
                        break;
                    case 'loan_repayment':
                        $builder->where('reference_type', Receipt::TYPE_LOAN_REPAYMENT);
                        break;
                    case 'admission_fee':
                        $builder->where('reference_type', Receipt::TYPE_CONTRIBUTION)
                            ->whereIn('reference_id', Contribution::select('id')->where('type', 'Admission Fee'));
                        break;
                    case 'monthly_dues':
                        $builder->where('reference_type', Receipt::TYPE_CONTRIBUTION)
                            ->whereIn('reference_id', Contribution::select('id')->where('type', 'Monthly Dues'));
                        break;
                    case 'special_contribution':
                        $builder->where('reference_type', Receipt::TYPE_CONTRIBUTION)
                            ->whereIn('reference_id', Contribution::select('id')->where('type', 'Special Contribution'));
                        break;
                    case 'professor_donation':
                        $builder->where('reference_type', Receipt::TYPE_CONTRIBUTION)
                            ->whereIn('reference_id', Contribution::select('id')->where('type', 'Professor Donation'));
                        break;
                    case 'lawyer_donation':
                        $builder->where('reference_type', Receipt::TYPE_CONTRIBUTION)
                            ->whereIn('reference_id', Contribution::select('id')->where('type', 'Lawyer Donation'));
                        break;
                    case 'extra_levies':
                        $builder->where('reference_type', Receipt::TYPE_CONTRIBUTION)
                            ->whereIn('reference_id', Contribution::select('id')->where('type', 'Extra Levies'));
                        break;
                    case 'contribution':
                        $builder->where('reference_type', Receipt::TYPE_CONTRIBUTION);
                        break;
                }
            });
        }

        $receipts = $query->paginate(15)->withQueryString();
        $paymentLabels = $this->buildPaymentLabels($receipts->items());
        $missingReceiptCount = $this->missingReceiptCount();

        return view('receipts.index', compact('receipts', 'search', 'paymentType', 'paymentLabels', 'missingReceiptCount'));
    }

    public function show(Receipt $receipt): View
    {
        $receipt->load(['member', 'generatedBy']);
        $paymentLabel = $this->resolvePaymentLabel($receipt);

        return view('receipts.show', compact('receipt', 'paymentLabel'));
    }

    public function download(Receipt $receipt, ReceiptService $receiptService)
    {
        $path = $this->resolveReceiptPath($receipt, $receiptService);

        if (! $path) {
            abort(404, 'Receipt file is not available.');
        }

        return Storage::disk('public')->download($path, $receipt->receipt_number.'.pdf');
    }

    public function view(Receipt $receipt, ReceiptService $receiptService)
    {
        $path = $this->resolveReceiptPath($receipt, $receiptService);

        if (! $path) {
            abort(404, 'Receipt file is not available.');
        }

        return Storage::disk('public')->response(
            $path,
            $receipt->receipt_number.'.pdf',
            ['Content-Disposition' => 'inline; filename="'.$receipt->receipt_number.'.pdf"']
        );
    }

    public function regenerateAll(ReceiptService $receiptService)
    {
        set_time_limit(0);
        $regenerated = 0;

        Receipt::orderBy('id')->chunk(100, function ($receipts) use ($receiptService, &$regenerated) {
            foreach ($receipts as $receipt) {
                $receiptService->regeneratePdf($receipt);
                $regenerated++;
            }
        });

        return redirect()
            ->route('receipts.index')
            ->with('status', "Regenerated {$regenerated} receipts.");
    }

    public function generateMissing(Request $request, ReceiptService $receiptService): RedirectResponse
    {
        set_time_limit(0);

        $created = [
            'contributions' => 0,
            'incomes' => 0,
            'repayments' => 0,
        ];

        Contribution::with('member')->whereDoesntHave('receipt')->orderBy('id')->chunkById(100, function ($contributions) use ($receiptService, $request, &$created) {
            foreach ($contributions as $contribution) {
                $receiptService->createForContribution($contribution, $request->user());
                $created['contributions']++;
            }
        });

        Income::whereDoesntHave('receipt')->orderBy('id')->chunkById(100, function ($incomes) use ($receiptService, $request, &$created) {
            foreach ($incomes as $income) {
                $receiptService->createForIncome($income, $request->user());
                $created['incomes']++;
            }
        });

        LoanRepayment::with('loan.member')->whereDoesntHave('receipt')->orderBy('id')->chunkById(100, function ($repayments) use ($receiptService, $request, &$created) {
            foreach ($repayments as $repayment) {
                $receiptService->createForLoanRepayment($repayment, $request->user());
                $created['repayments']++;
            }
        });

        $total = array_sum($created);

        return redirect()
            ->route('receipts.index')
            ->with('status', "Generated {$total} missing receipts.");
    }

    private function resolveReceiptPath(Receipt $receipt, ReceiptService $receiptService): ?string
    {
        $path = $receipt->pdf_path ?: 'receipts/'.$receipt->receipt_number.'.pdf';

        if (Storage::disk('public')->exists($path)) {
            return $path;
        }

        return $receiptService->regeneratePdf($receipt);
    }

    private function buildPaymentLabels(array $receipts): array
    {
        $labels = [];
        $contributionIds = [];

        foreach ($receipts as $receipt) {
            if ($receipt->reference_type === Receipt::TYPE_CONTRIBUTION) {
                $contributionIds[] = $receipt->reference_id;
            }
        }

        $contributionTypes = [];
        if ($contributionIds) {
            $contributionTypes = Contribution::withTrashed()
                ->whereIn('id', $contributionIds)
                ->pluck('type', 'id')
                ->all();
        }

        foreach ($receipts as $receipt) {
            $labels[$receipt->id] = $this->resolvePaymentLabel($receipt, $contributionTypes);
        }

        return $labels;
    }

    private function missingReceiptCount(): int
    {
        return Contribution::whereDoesntHave('receipt')->count()
            + Income::whereDoesntHave('receipt')->count()
            + LoanRepayment::whereDoesntHave('receipt')->count();
    }

    private function resolvePaymentLabel(Receipt $receipt, array $contributionTypes = []): string
    {
        if ($receipt->reference_type === Receipt::TYPE_CONTRIBUTION) {
            return $contributionTypes[$receipt->reference_id] ?? 'Contribution';
        }

        if ($receipt->reference_type === Receipt::TYPE_LOAN_REPAYMENT) {
            return 'Loan Repayment';
        }

        if ($receipt->reference_type === Receipt::TYPE_INCOME) {
            return 'Income';
        }

        return 'Receipt';
    }
}
