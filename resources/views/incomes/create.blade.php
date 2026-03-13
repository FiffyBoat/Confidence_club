@extends('layouts.app')

@section('content')
<h2 class="mb-3">Record Income</h2>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('incomes.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Source</label>
                    <input type="text" name="source" class="form-control" value="{{ old('source') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Transaction Date</label>
                    <input type="date" name="transaction_date" class="form-control" value="{{ old('transaction_date', now()->toDateString()) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Description (optional)</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Save Income</button>
                <a href="{{ route('incomes.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
