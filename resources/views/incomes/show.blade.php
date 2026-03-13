@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Income Details</h2>
    <div class="d-flex gap-2">
        @if($income->receipt)
        <a href="{{ route('receipts.download', $income->receipt) }}" class="btn btn-outline-primary">Download Receipt</a>
        @endif
        <a href="{{ route('incomes.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-muted small">Source</div>
                <div class="fw-semibold">{{ $income->source }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Amount</div>
                <div class="fw-semibold">GHS {{ number_format($income->amount, 2) }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Transaction Date</div>
                <div class="fw-semibold">{{ $income->transaction_date?->format('Y-m-d') }}</div>
            </div>
            <div class="col-md-12">
                <div class="text-muted small">Description</div>
                <div class="fw-semibold">{{ $income->description ?? '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Receipt</div>
                <div class="fw-semibold">{{ $income->receipt->receipt_number ?? 'Pending' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
