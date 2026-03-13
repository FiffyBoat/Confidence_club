@extends('layouts.app')

@section('content')
@php
    $arrearsCount = $rows->filter(fn ($row) => $row['balance_end'] > 0)->count();
    $totalOutstanding = $rows->sum('balance_end');
    $expectedToAsOf = $rows->count() * $asOfMonth * 50;
@endphp

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Member Dues</div>
        <h2 class="mb-1">Monthly Dues</h2>
        <div class="text-muted">Tracking payments up to {{ $monthsList[$asOfMonth] }} {{ $year }}</div>
    </div>
    <span class="text-muted">{{ now()->format('D, d M Y') }}</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Members in Arrears</div>
            <div class="stat-value">{{ $arrearsCount }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Outstanding Balance</div>
            <div class="stat-value">GHS {{ number_format($totalOutstanding, 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Expected Dues (to date)</div>
            <div class="stat-value">GHS {{ number_format($expectedToAsOf, 2) }}</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Record Dues Payment</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('dues.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Member</label>
                            <select name="member_id" class="form-select" required>
                                <option value="">Select member</option>
                                @foreach($members as $member)
                                <option value="{{ $member->id }}">{{ $member->membership_id }} - {{ $member->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Month</label>
                            <select name="month" class="form-select" required>
                                @foreach($monthsList as $num => $label)
                                <option value="{{ $num }}" {{ (int) old('month', $asOfMonth) === $num ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" class="form-control" value="{{ old('year', $year) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', 50) }}" required>
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
                            <label class="form-label">Transaction Date (optional)</label>
                            <input type="date" name="transaction_date" class="form-control" value="{{ old('transaction_date') }}">
                        </div>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-primary">Save Dues</button>
                        <a href="{{ route('dues.index', ['year' => $year, 'as_of' => $asOfMonth]) }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Filter</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('dues.index') }}" method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Year</label>
                        <input type="number" name="year" class="form-control" value="{{ $year }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">As Of Month</label>
                        <select name="as_of" class="form-select">
                            @foreach($monthsList as $num => $label)
                            <option value="{{ $num }}" {{ (int) $asOfMonth === $num ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-outline-primary">Apply</button>
                        <a href="{{ route('dues.index') }}" class="btn btn-outline-secondary">Current Year</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Dues Register - {{ $year }}</strong>
        <span class="text-muted small">As of {{ $monthsList[$asOfMonth] }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Member</th>
                    @foreach($monthsList as $label)
                    <th class="text-end">{{ $label }}</th>
                    @endforeach
                    <th class="text-end">Year Paid</th>
                    <th class="text-end">Balance End Month</th>
                    <th class="text-end">Balance + Next Month</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $row['member']->full_name }}</div>
                        <div class="text-muted small">{{ $row['member']->membership_id }}</div>
                    </td>
                    @foreach($monthsList as $num => $label)
                    <td class="text-end">
                        @if($row['months'][$num] > 0)
                            GHS {{ number_format($row['months'][$num], 2) }}
                        @elseif($num <= $asOfMonth)
                            <span class="text-danger">GHS 0.00</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    @endforeach
                    <td class="text-end fw-semibold">GHS {{ number_format($row['year_total'], 2) }}</td>
                    <td class="text-end">
                        <span class="{{ $row['balance_end'] > 0 ? 'text-danger' : 'text-success' }}">
                            GHS {{ number_format($row['balance_end'], 2) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <span class="{{ $row['balance_next'] > 0 ? 'text-danger' : 'text-success' }}">
                            GHS {{ number_format($row['balance_next'], 2) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="16">No members found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
