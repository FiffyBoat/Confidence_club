@extends('layouts.app')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Member Support</div>
        <h2 class="mb-1">Special Contributions</h2>
        <div class="text-muted">Record purpose-driven contributions (min GHS 100).</div>
    </div>
    <span class="text-muted">{{ now()->format('D, d M Y') }}</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Record Special Contribution</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('special-contributions.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
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
                        <div class="col-12">
                            <label class="form-label">Description (Purpose)</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount (min GHS 100)</label>
                            <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', 100) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                @foreach(['cash' => 'Cash', 'momo' => 'Mobile Money', 'bank' => 'Bank', 'card' => 'Card'] as $value => $label)
                                <option value="{{ $value }}" {{ old('payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Transaction Date</label>
                            <input type="date" name="transaction_date" class="form-control" value="{{ old('transaction_date', now()->toDateString()) }}" required>
                        </div>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-primary">Save</button>
                        <a href="{{ route('special-contributions.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Recent Special Contributions</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th class="text-end">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($specialContributions as $contribution)
                        <tr>
                            <td>{{ $contribution->member->full_name ?? '-' }}</td>
                            <td>{{ $contribution->description }}</td>
                            <td>GHS {{ number_format($contribution->amount, 2) }}</td>
                            <td>{{ $contribution->transaction_date?->format('Y-m-d') }}</td>
                            <td class="text-end">
                                @if($contribution->receipt)
                                    <a href="{{ route('receipts.view', $contribution->receipt) }}" class="btn btn-outline-primary btn-sm" target="_blank">Receipt</a>
                                @else
                                    <span class="text-muted small">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">No special contributions recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $specialContributions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
