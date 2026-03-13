@extends('layouts.app')

@section('content')
<h2 class="mb-3">Record Contribution</h2>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('contributions.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Member</label>
                    <select name="member_id" class="form-select" required>
                        <option value="">Select member</option>
                        @foreach($members as $member)
                        <option value="{{ $member->id }}" {{ (int) old('member_id') === $member->id ? 'selected' : '' }}>
                            {{ $member->membership_id }} - {{ $member->full_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Type</label>
                    <input type="text" name="type" class="form-control" value="{{ old('type') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Description (optional)</label>
                    <input type="text" name="description" class="form-control" value="{{ old('description') }}" placeholder="e.g. Support for member medical bill">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select" required>
                        @foreach(['cash' => 'Cash', 'momo' => 'Mobile Money', 'bank' => 'Bank', 'card' => 'Card'] as $value => $label)
                        <option value="{{ $value }}" {{ old('payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Transaction Date</label>
                    <input type="date" name="transaction_date" class="form-control" value="{{ old('transaction_date', now()->toDateString()) }}" required>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Save Contribution</button>
                <a href="{{ route('contributions.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
