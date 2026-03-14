@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Edit Loan Repayment</h2>
    <a href="{{ route('loans.show', $loan) }}" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-muted small">Member</div>
                <div class="fw-semibold">{{ $loan->member->full_name ?? '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Loan Balance</div>
                <div class="fw-semibold">GHS {{ number_format($loan->balance, 2) }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Total Payable</div>
                <div class="fw-semibold">GHS {{ number_format($loan->total_payable, 2) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('loan-repayments.update', $repayment) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $repayment->amount) }}" required>
                    <div class="form-text">Max allowed includes the current balance plus this repayment.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Payment Date</label>
                    <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', $repayment->payment_date?->format('Y-m-d')) }}" required>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Update Repayment</button>
                <a href="{{ route('loans.show', $loan) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
