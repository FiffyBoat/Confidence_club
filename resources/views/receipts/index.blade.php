@extends('layouts.app')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Records</div>
        <h2 class="mb-1">Receipts</h2>
        <div class="text-muted">View generated receipts and download PDF copies.</div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('receipts.index') }}" method="GET" class="row g-2 mb-3 align-items-end">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Search by receipt or member" value="{{ $search ?? '' }}">
                    @if(!empty($search))
                        <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Payment Type</label>
                <select name="payment_type" class="form-select">
                    <option value="">All</option>
                    <option value="admission_fee" {{ ($paymentType ?? '') === 'admission_fee' ? 'selected' : '' }}>Admission Fee</option>
                    <option value="monthly_dues" {{ ($paymentType ?? '') === 'monthly_dues' ? 'selected' : '' }}>Monthly Dues</option>
                    <option value="special_contribution" {{ ($paymentType ?? '') === 'special_contribution' ? 'selected' : '' }}>Special Contribution</option>
                    <option value="professor_donation" {{ ($paymentType ?? '') === 'professor_donation' ? 'selected' : '' }}>Professor Donation</option>
                    <option value="lawyer_donation" {{ ($paymentType ?? '') === 'lawyer_donation' ? 'selected' : '' }}>Lawyer Donation</option>
                    <option value="extra_levies" {{ ($paymentType ?? '') === 'extra_levies' ? 'selected' : '' }}>Extra Levies</option>
                    <option value="loan_repayment" {{ ($paymentType ?? '') === 'loan_repayment' ? 'selected' : '' }}>Loan Repayment</option>
                    <option value="income" {{ ($paymentType ?? '') === 'income' ? 'selected' : '' }}>Income</option>
                    <option value="contribution" {{ ($paymentType ?? '') === 'contribution' ? 'selected' : '' }}>Other Contributions</option>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>Member</th>
                        <th>Payment Type</th>
                        <th>Amount</th>
                        <th>Generated</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $receipt)
                    <tr>
                        <td>{{ $receipt->receipt_number }}</td>
                        <td>{{ $receipt->member->full_name ?? 'General Income' }}</td>
                        <td>{{ $paymentLabels[$receipt->id] ?? 'Receipt' }}</td>
                        <td>GHS {{ number_format($receipt->amount, 2) }}</td>
                        <td>{{ $receipt->created_at->format('Y-m-d') }}</td>
                        <td class="text-end">
                            <a href="{{ route('receipts.view', $receipt) }}" class="btn btn-primary btn-sm" target="_blank">View / Print</a>
                            <a href="{{ route('receipts.show', $receipt) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('receipts.download', $receipt) }}" class="btn btn-outline-primary btn-sm">Download</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">No receipts found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $receipts->links('pagination::bootstrap-5') }}
</div>
@endsection
