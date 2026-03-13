@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Admin Role Guide</h2>
    <span class="text-muted">{{ now()->format('D, d M Y') }}</span>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <p class="mb-2">As an <strong>Admin</strong>, you have full access to the system.</p>
        <ul class="mb-0">
            <li>Manage users (create, edit, activate/deactivate roles).</li>
            <li>View system activity logs and audit trails.</li>
            <li>Access all Treasurer functions (members, dues, contributions, income, expenses, loans, receipts, reports).</li>
            <li>Configure settings and governance.</li>
        </ul>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3">
            <div class="fw-semibold mb-2">User Management</div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary btn-sm">Manage Users</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3">
            <div class="fw-semibold mb-2">Activity Logs</div>
            <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-primary btn-sm">View Logs</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3">
            <div class="fw-semibold mb-2">Treasurer Dashboard</div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-sm">Open Dashboard</a>
        </div>
    </div>
</div>
@endsection
