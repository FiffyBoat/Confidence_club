@extends('layouts.app')

@section('content')
@php
    $tableColumns = $canViewPayments ? 8 : 7;
@endphp
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Directory</div>
        <h2 class="mb-1">Members</h2>
        <div class="text-muted">Search profiles and review payment history.</div>
    </div>
    @if(in_array(auth()->user()->role ?? 'viewer', ['admin', 'treasurer']))
        <a href="{{ route('members.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Add Member</a>
    @endif
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('members.index') }}" method="GET" class="row g-2 mb-3 align-items-end">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Search by name, membership ID, phone" value="{{ $search ?? '' }}">
                    @if(!empty($search))
                        <a href="{{ route('members.index') }}" class="btn btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </div>
            @if($canViewPayments)
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Admission Fee</label>
                    <select name="admission_fee" class="form-select">
                        <option value="">All</option>
                        <option value="paid" {{ ($admissionFilter ?? '') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="pending" {{ ($admissionFilter ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
            @endif
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary">Search</button>
            </div>
        </form>

        @if(!empty($search))
        <div class="small text-muted mb-3">
            Showing {{ $members->count() }} of {{ $members->total() }} result(s) for <strong>{{ $search }}</strong>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Membership ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Join Date</th>
                        @if($canViewPayments)
                            <th>Admission Fee</th>
                        @endif
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                    <tr>
                        <td>{{ $member->id }}</td>
                        <td>{{ $member->membership_id }}</td>
                        <td>{{ $member->full_name }}</td>
                        <td>{{ $member->phone }}</td>
                        <td>
                            <span class="badge {{ $member->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ ucfirst($member->status) }}
                            </span>
                        </td>
                        <td>{{ $member->join_date?->format('Y-m-d') }}</td>
                        @if($canViewPayments)
                            <td>
                                @if($member->has_admission_fee)
                                    <span class="badge text-bg-success">Paid</span>
                                @else
                                    <span class="badge text-bg-warning">Pending</span>
                                @endif
                            </td>
                        @endif
                        <td class="text-end">
                            @if(in_array(auth()->user()->role ?? 'viewer', ['admin', 'treasurer']))
                                <a href="{{ route('members.show', $member) }}" class="btn btn-info btn-sm">View</a>
                                <a href="{{ route('members.edit', $member) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('members.destroy', $member) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Delete member?')">Delete</button>
                                </form>
                            @else
                                <span class="text-muted">Read-only</span>
                            @endif
                        </td>
                    </tr>
                    @if(!empty($search) && $canViewPayments)
                    <tr class="bg-light">
                        <td colspan="{{ $tableColumns }}">
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge text-bg-secondary">Contributions: GHS {{ number_format($member->contributions->sum('amount'), 2) }}</span>
                                <span class="badge text-bg-success">Repayments: GHS {{ number_format($member->loans->flatMap(fn ($loan) => $loan->repayments)->sum('amount'), 2) }}</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <div class="fw-semibold mb-2">Contributions</div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Description</th>
                                                    <th>Amount</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($member->contributions as $contribution)
                                                <tr>
                                                    <td>{{ $contribution->type }}</td>
                                                    <td>{{ $contribution->description ?? '-' }}</td>
                                                    <td>GHS {{ number_format($contribution->amount, 2) }}</td>
                                                    <td>{{ $contribution->transaction_date?->format('Y-m-d') }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-muted">No contributions recorded.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="fw-semibold mb-2">Loan Repayments</div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Loan</th>
                                                    <th>Amount</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $repayments = $member->loans->flatMap(function ($loan) {
                                                        return $loan->repayments->map(function ($repayment) use ($loan) {
                                                            return [
                                                                'loan_id' => $loan->id,
                                                                'amount' => $repayment->amount,
                                                                'date' => $repayment->payment_date,
                                                            ];
                                                        });
                                                    })->sortByDesc('date');
                                                @endphp
                                                @forelse($repayments as $repayment)
                                                <tr>
                                                    <td>Loan #{{ $repayment['loan_id'] }}</td>
                                                    <td>GHS {{ number_format($repayment['amount'], 2) }}</td>
                                                    <td>{{ optional($repayment['date'])->format('Y-m-d') }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="3" class="text-muted">No repayments recorded.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="{{ $tableColumns }}">No members found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $members->links('pagination::bootstrap-5') }}
</div>
@endsection
