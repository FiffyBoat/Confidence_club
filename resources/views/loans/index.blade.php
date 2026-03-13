@extends('layouts.app')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Credit</div>
        <h2 class="mb-1">Loans</h2>
        <div class="text-muted">Track balances, due dates, and repayment status.</div>
    </div>
    <a href="{{ route('loans.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Issue Loan</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('loans.index') }}" method="GET" class="row g-2 mb-3">
            <div class="col-md-10">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach(['active' => 'Active', 'overdue' => 'Overdue', 'completed' => 'Completed'] as $value => $label)
                    <option value="{{ $value }}" {{ ($status ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary">Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Member</th>
                        <th>Principal</th>
                        <th>Balance</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $loan)
                    <tr>
                        <td>{{ $loan->id }}</td>
                        <td>{{ $loan->member->full_name ?? 'Unknown' }}</td>
                        <td>GHS {{ number_format($loan->principal, 2) }}</td>
                        <td>GHS {{ number_format($loan->balance, 2) }}</td>
                        <td>{{ $loan->due_date?->format('Y-m-d') }}</td>
                        @php
                            $statusClass = match ($loan->status) {
                                'overdue' => 'text-bg-danger',
                                'completed' => 'text-bg-success',
                                default => 'text-bg-secondary',
                            };
                        @endphp
                        <td><span class="badge {{ $statusClass }}">{{ ucfirst($loan->status) }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('loans.show', $loan) }}" class="btn btn-info btn-sm">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">No loans found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $loans->links('pagination::bootstrap-5') }}
</div>
@endsection
