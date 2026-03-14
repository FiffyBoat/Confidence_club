@extends('layouts.app')

@section('content')
<div class="page-hero mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
        <div>
            <div class="hero-badge">Treasurer Guide</div>
            <h2 class="page-title mb-1">Treasurer workspace</h2>
            <div class="page-subtitle">Daily finance tasks, receipts, and reports — all in one place.</div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <span class="pill"><i class="bi bi-cash-coin"></i>Admission fee: GHS 200</span>
                <span class="pill"><i class="bi bi-calendar-check"></i>Dues: GHS 50/month</span>
                <span class="pill"><i class="bi bi-stars"></i>Special min: GHS 100</span>
            </div>
        </div>
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2">
            <a href="{{ route('members.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-person-plus me-1"></i>Add Member</a>
            <a href="{{ route('dues.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-calendar-check me-1"></i>Record Dues</a>
            <a href="{{ route('contributions.create') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-cash-stack me-1"></i>New Contribution</a>
            <div class="hero-icon d-none d-lg-flex"><i class="bi bi-wallet2"></i></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <strong>Daily Checklist</strong>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Register members; leave admission fee unchecked if unpaid.</li>
                    <li>Record dues, contributions, and special contributions.</li>
                    <li>Log income, expenses, loans, and repayments.</li>
                    <li>Print receipts for every payment.</li>
                    <li>Run reports weekly for summaries.</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <strong>Quick Actions</strong>
            </div>
            <div class="card-body d-flex flex-wrap gap-2">
                <a href="{{ route('members.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-people me-1"></i>Members</a>
                <a href="{{ route('dues.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-calendar-check me-1"></i>Dues</a>
                <a href="{{ route('contributions.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-cash-stack me-1"></i>Contributions</a>
                <a href="{{ route('special-contributions.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-stars me-1"></i>Special Contributions</a>
                <a href="{{ route('donations.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-heart me-1"></i>Donations</a>
                <a href="{{ route('incomes.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-graph-up-arrow me-1"></i>Income</a>
                <a href="{{ route('expenses.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-graph-down-arrow me-1"></i>Expenses</a>
                <a href="{{ route('loans.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-bank me-1"></i>Loans</a>
                <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-receipt me-1"></i>Receipts</a>
                <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-bar-chart-line me-1"></i>Reports</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-4">
        <div class="stat-card h-100">
            <div class="stat-label">Receipts</div>
            <div class="stat-value">Auto generated</div>
            <div class="text-muted small mt-2">Every payment creates a receipt with payment type.</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card h-100">
            <div class="stat-label">Special Contributions</div>
            <div class="stat-value">Public badges</div>
            <div class="text-muted small mt-2">Shown as “Paid” on viewer dashboard when enabled.</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card h-100">
            <div class="stat-label">Viewer Visibility</div>
            <div class="stat-value">Admin controlled</div>
            <div class="text-muted small mt-2">Only sections enabled in settings appear to the public.</div>
        </div>
    </div>
</div>
@endsection
