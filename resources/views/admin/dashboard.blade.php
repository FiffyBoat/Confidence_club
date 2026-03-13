@extends('admin.layout')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Admin</div>
        <h2 class="mb-1">Admin Overview</h2>
        <div class="text-muted">Summary for {{ now()->format('D, d M Y') }}</div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Total Users</div>
                    <div class="stat-value">{{ $totalUsers }}</div>
                </div>
                <span class="badge text-bg-secondary"><i class="bi bi-people"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Admins</div>
                    <div class="stat-value">{{ $adminUsers }}</div>
                </div>
                <span class="badge text-bg-success"><i class="bi bi-shield-lock"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Treasurers</div>
                    <div class="stat-value">{{ $treasurerUsers }}</div>
                </div>
                <span class="badge text-bg-warning"><i class="bi bi-cash-stack"></i></span>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Total Members</div>
            <div class="stat-value">{{ $totalMembers }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Total Contributions</div>
            <div class="stat-value">GHS {{ number_format($totalContributions, 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Total Expenses</div>
            <div class="stat-value">GHS {{ number_format($totalExpenses, 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Outstanding Loans</div>
            <div class="stat-value">GHS {{ number_format($outstandingLoans, 2) }}</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <strong>Recent Activity</strong>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentLogs as $log)
                <tr>
                    <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $log->user->name ?? 'System' }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->description }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">No recent activity logs.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
