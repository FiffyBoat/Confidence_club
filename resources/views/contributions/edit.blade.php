@extends('layouts.app')

@section('content')
<h2 class="mb-3">Edit Contribution</h2>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('contributions.update', $contribution) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Member</label>
                    <select name="member_id" class="form-select" required>
                        <option value="">Select member</option>
                        @foreach($members as $member)
                        <option value="{{ $member->id }}" {{ (int) old('member_id', $contribution->member_id) === $member->id ? 'selected' : '' }}>
                            {{ $member->membership_id }} - {{ $member->full_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Type</label>
                    <input type="text" name="type" class="form-control" value="{{ old('type', $contribution->type) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Description (optional)</label>
                    <input type="text" name="description" class="form-control" value="{{ old('description', $contribution->description) }}" placeholder="e.g. Support for member medical bill">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $contribution->amount) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select" required>
                        @foreach(['cash' => 'Cash', 'momo' => 'Mobile Money', 'bank' => 'Bank', 'card' => 'Card'] as $value => $label)
                        <option value="{{ $value }}" {{ old('payment_method', $contribution->payment_method) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Transaction Date</label>
                    <input type="date" name="transaction_date" class="form-control" value="{{ old('transaction_date', $contribution->transaction_date?->format('Y-m-d')) }}" required>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Update Contribution</button>
                <a href="{{ route('contributions.show', $contribution) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
