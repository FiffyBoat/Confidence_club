@extends('layouts.app')

@section('content')
<div class="page-hero mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
        <div>
            <div class="hero-badge">Overview</div>
            <h2 class="page-title mb-1">Welcome back, {{ auth()->user()->name ?? 'Member' }}</h2>
            <div class="page-subtitle">Snapshot for {{ now()->format('l, d M Y') }}</div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <span class="pill"><i class="bi bi-shield-check"></i>{{ ucfirst(auth()->user()->role ?? 'viewer') }}</span>
                <span class="pill"><i class="bi bi-calendar-event"></i>{{ now()->format('M Y') }}</span>
            </div>
        </div>
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3">
            @if(in_array(auth()->user()->role ?? 'viewer', ['admin', 'treasurer']))
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('members.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-person-plus me-1"></i>Add Member</a>
                <a href="{{ route('dues.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-calendar-check me-1"></i>Record Dues</a>
                <a href="{{ route('contributions.create') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-cash-stack me-1"></i>Contribution</a>
            </div>
            @endif
            <div class="hero-icon d-none d-lg-flex"><i class="bi bi-speedometer2"></i></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Total Members</div>
                    <div class="stat-value">{{ $totalMembers }}</div>
                </div>
                <span class="badge text-bg-secondary"><i class="bi bi-people"></i></span>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Total Balance</div>
                    <div class="stat-value">GHS {{ number_format($totalBalance, 2) }}</div>
                </div>
                <span class="badge text-bg-success"><i class="bi bi-wallet2"></i></span>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Monthly Contributions</div>
                    <div class="stat-value">GHS {{ number_format($monthlyContributions, 2) }}</div>
                </div>
                <span class="badge text-bg-primary"><i class="bi bi-coin"></i></span>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Monthly Expenses</div>
                    <div class="stat-value">GHS {{ number_format($monthlyExpenses, 2) }}</div>
                </div>
                <span class="badge text-bg-danger"><i class="bi bi-credit-card"></i></span>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Active Loans</div>
                    <div class="stat-value">{{ $activeLoans }}</div>
                </div>
                <span class="badge text-bg-warning"><i class="bi bi-bank"></i></span>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Overdue Loans</div>
                    <div class="stat-value">{{ $overdueLoans }}</div>
                </div>
                <span class="badge text-bg-danger"><i class="bi bi-exclamation-triangle"></i></span>
            </div>
        </div>
    </div>
</div>

@if(in_array(auth()->user()->role ?? 'viewer', ['admin', 'treasurer']))
<div class="card shadow-sm border-0 mt-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Recent Receipts</strong>
        <a href="{{ route('receipts.index') }}" class="btn btn-outline-primary btn-sm">View all</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Receipt #</th>
                    <th>Member</th>
                    <th class="text-end">Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentReceipts as $receipt)
                <tr>
                    <td>{{ $receipt->receipt_number }}</td>
                    <td>{{ $receipt->member->full_name ?? 'General Income' }}</td>
                    <td class="text-end">GHS {{ number_format($receipt->amount, 2) }}</td>
                    <td>{{ $receipt->created_at->format('Y-m-d') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">No recent receipts.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
