@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Loan Details</h2>
    <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-muted small">Member</div>
                <div class="fw-semibold">{{ $loan->member->full_name ?? '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Principal</div>
                <div class="fw-semibold">GHS {{ number_format($loan->principal, 2) }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Interest Rate</div>
                <div class="fw-semibold">{{ number_format($loan->interest_rate, 2) }}%</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Total Payable</div>
                <div class="fw-semibold">GHS {{ number_format($loan->total_payable, 2) }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Balance</div>
                <div class="fw-semibold">GHS {{ number_format($loan->balance, 2) }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Status</div>
                @php
                    $statusClass = match ($loan->status) {
                        'overdue' => 'text-bg-danger',
                        'completed' => 'text-bg-success',
                        default => 'text-bg-secondary',
                    };
                @endphp
                <span class="badge {{ $statusClass }}">{{ ucfirst($loan->status) }}</span>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Issue Date</div>
                <div class="fw-semibold">{{ $loan->issue_date?->format('Y-m-d') }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Due Date</div>
                <div class="fw-semibold">{{ $loan->due_date?->format('Y-m-d') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Record Repayment</strong>
            </div>
            <div class="card-body">
                @if($loan->balance > 0)
                    <form action="{{ route('loan-repayments.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="loan_id" value="{{ $loan->id }}">
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" name="amount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <button class="btn btn-primary">Record Repayment</button>
                    </form>
                @else
                    <div class="alert alert-info mb-0">This loan has been fully repaid.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Repayment History</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Recorded By</th>
                            <th class="text-end">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loan->repayments as $repayment)
                        <tr>
                            <td>GHS {{ number_format($repayment->amount, 2) }}</td>
                            <td>{{ $repayment->payment_date?->format('Y-m-d') }}</td>
                            <td>{{ $repayment->recordedBy->name ?? '-' }}</td>
                            <td class="text-end">
                                @if($repayment->receipt)
                                    <a href="{{ route('receipts.view', $repayment->receipt) }}" class="btn btn-outline-primary btn-sm" target="_blank">Receipt</a>
                                @else
                                    <span class="text-muted small">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4">No repayments recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
