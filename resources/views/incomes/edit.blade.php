@extends('layouts.app')

@section('content')
<h2 class="mb-3">Edit Income</h2>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('incomes.update', $income) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Source</label>
                    <input type="text" name="source" class="form-control" value="{{ old('source', $income->source) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $income->amount) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Transaction Date</label>
                    <input type="date" name="transaction_date" class="form-control" value="{{ old('transaction_date', $income->transaction_date?->format('Y-m-d')) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Description (optional)</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $income->description) }}</textarea>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Update Income</button>
                <a href="{{ route('incomes.show', $income) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
