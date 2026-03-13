<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        return view('receipts.index', compact('receipts', 'search', 'paymentType', 'paymentLabels'));
    }

    public function show(Receipt $receipt): View
    {
        $receipt->load(['member', 'generatedBy']);
        $paymentLabel = $this->resolvePaymentLabel($receipt);

        return view('receipts.show', compact('receipt', 'paymentLabel'));
    }

    public function download(Receipt $receipt)
    {
        return Storage::disk('public')->download($receipt->pdf_path, $receipt->receipt_number.'.pdf');
    }

    public function view(Receipt $receipt)
    {
        return Storage::disk('public')->response(
            $receipt->pdf_path,
            $receipt->receipt_number.'.pdf',
            ['Content-Disposition' => 'inline; filename="'.$receipt->receipt_number.'.pdf"']
        );
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
