@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Expense Details</h2>
    <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-muted small">Category</div>
                <div class="fw-semibold">{{ $expense->category }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Amount</div>
                <div class="fw-semibold">GHS {{ number_format($expense->amount, 2) }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Transaction Date</div>
                <div class="fw-semibold">{{ $expense->transaction_date?->format('Y-m-d') }}</div>
            </div>
            <div class="col-md-12">
                <div class="text-muted small">Description</div>
                <div class="fw-semibold">{{ $expense->description ?? '-' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
