@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Member Profile</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('members.statement.print', $member) }}" class="btn btn-primary" target="_blank">View / Print Statement</a>
        <a href="{{ route('members.statement.pdf', $member) }}" class="btn btn-outline-primary">Download Statement PDF</a>
        <form action="{{ route('members.force-destroy', $member) }}" method="POST" onsubmit="return confirm('This will permanently delete the member and all related data. Continue?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger">Delete Permanently</button>
        </form>
        <a href="{{ route('members.edit', $member) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('members.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Payments Recorded</div>
            <div class="stat-value">{{ $statement['paymentSummary']['payment_count'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Total Paid</div>
            <div class="stat-value">GHS {{ number_format($statement['paymentSummary']['total_paid'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Dues Outstanding</div>
            <div class="stat-value">GHS {{ number_format($statement['duesSummary']['outstanding'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Unpaid Dues Months</div>
            <div class="stat-value">{{ $statement['unpaidDues']->count() }}</div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-muted small">Membership ID</div>
                <div class="fw-semibold">{{ $member->membership_id }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Full Name</div>
                <div class="fw-semibold">{{ $member->full_name }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Status</div>
                <span class="badge {{ $member->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                    {{ ucfirst($member->status) }}
                </span>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Phone</div>
                <div class="fw-semibold">{{ $member->phone }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Email</div>
                <div class="fw-semibold">{{ $member->email ?? '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Join Date</div>
                <div class="fw-semibold">{{ $member->join_date?->format('Y-m-d') }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Admission Fee</div>
                @if($hasAdmissionFee)
                    <span class="badge text-bg-success">Paid</span>
                @else
                    <span class="badge text-bg-warning">Pending</span>
                @endif
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Birthday</div>
                <div class="fw-semibold">
                    @if($member->birth_month && $member->birth_day)
                        {{ \Carbon\Carbon::create(2024, $member->birth_month, $member->birth_day)->format('F j') }}
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Payments Done</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statement['payments'] as $payment)
                        <tr>
                            <td>{{ $payment['type'] }}</td>
                            <td>{{ $payment['description'] }}</td>
                            <td>GHS {{ number_format($payment['amount'], 2) }}</td>
                            <td>{{ $payment['date']?->format('Y-m-d') }}</td>
                            <td>
                                <div class="dropdown text-end">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            @if($payment['category'] === 'contribution')
                                                <a class="dropdown-item" href="{{ route('contributions.show', $payment['record']) }}">View Contribution</a>
                                            @else
                                                <a class="dropdown-item" href="{{ route('loans.show', $payment['record']->loan) }}">Open Loan</a>
                                            @endif
                                        </li>
                                        @if($payment['receipt'])
                                        <li>
                                            <a class="dropdown-item" href="{{ route('receipts.view', $payment['receipt']) }}" target="_blank">View / Print Receipt</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('receipts.download', $payment['receipt']) }}">Download Receipt</a>
                                        </li>
                                        @else
                                        <li>
                                            <span class="dropdown-item text-muted">Receipt Pending</span>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">No payments recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Unpaid Monthly Dues</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Expected</th>
                            <th>Paid</th>
                            <th>Outstanding</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statement['unpaidDues'] as $dueMonth)
                        <tr>
                            <td>{{ $dueMonth['month_label'] }}</td>
                            <td>GHS {{ number_format($dueMonth['expected'], 2) }}</td>
                            <td>GHS {{ number_format($dueMonth['paid'], 2) }}</td>
                            <td>GHS {{ number_format($dueMonth['outstanding'], 2) }}</td>
                            @php
                                $statusClass = match ($dueMonth['status']) {
                                    'partial' => 'text-bg-warning',
                                    'paid' => 'text-bg-success',
                                    default => 'text-bg-danger',
                                };
                            @endphp
                            <td><span class="badge {{ $statusClass }}">{{ ucfirst($dueMonth['status']) }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">No unpaid dues as of {{ $statement['asOfDate']->format('Y-m') }}.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <strong>Loans</strong>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Principal</th>
                    <th>Balance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($member->loans as $loan)
                <tr>
                    <td>GHS {{ number_format($loan->principal, 2) }}</td>
                    <td>GHS {{ number_format($loan->balance, 2) }}</td>
                    @php
                        $statusClass = match ($loan->status) {
                            'overdue' => 'text-bg-danger',
                            'completed' => 'text-bg-success',
                            default => 'text-bg-secondary',
                        };
                    @endphp
                    <td><span class="badge {{ $statusClass }}">{{ ucfirst($loan->status) }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="3">No loans recorded.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
