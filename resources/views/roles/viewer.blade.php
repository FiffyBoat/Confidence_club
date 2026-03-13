@extends('layouts.app')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Role Guide</div>
        <h2 class="mb-1">Viewer Access</h2>
        <div class="text-muted">Read-only visibility across key summaries.</div>
    </div>
    <span class="text-muted">{{ now()->format('D, d M Y') }}</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="badge text-bg-secondary p-3"><i class="bi bi-eye"></i></div>
                    <div>
                        <h5 class="mb-1">Viewer Responsibilities</h5>
                        <p class="text-muted mb-3">You can review totals, reports, and member listings without changing data.</p>
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle text-success"></i>
                                    <span>Dashboard (read-only)</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle text-success"></i>
                                    <span>Members list (read-only)</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle text-success"></i>
                                    <span>Reports & exports</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle text-success"></i>
                                    <span>Transparency Portal</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-shield-lock text-danger"></i>
                                    <span class="text-muted">No edit or record permissions</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="text-muted mb-3">Quick Access</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('transparency') }}" class="btn btn-outline-primary"><i class="bi bi-eye me-1"></i>Transparency Portal</a>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-primary"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-primary"><i class="bi bi-bar-chart-line me-1"></i>Reports</a>
                    <a href="{{ route('members.index') }}" class="btn btn-outline-secondary"><i class="bi bi-people me-1"></i>Members</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="stat-card h-100">
            <div class="stat-label">Focus</div>
            <div class="stat-value">Transparency</div>
            <div class="text-muted small mt-2">Use the portal for public summaries.</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card h-100">
            <div class="stat-label">Exports</div>
            <div class="stat-value">Reports</div>
            <div class="text-muted small mt-2">Download PDF/CSV where available.</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card h-100">
            <div class="stat-label">Permissions</div>
            <div class="stat-value">Read-Only</div>
            <div class="text-muted small mt-2">No changes to records or payments.</div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mt-4">
    <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
        <div>
            <div class="text-muted small">Member Milestones</div>
            <h5 class="mb-1">Birthdays</h5>
            <div class="text-muted">Track upcoming birthdays and send wishes.</div>
        </div>
        <a href="{{ route('birthdays.index') }}" class="btn btn-outline-primary"><i class="bi bi-balloon me-1"></i>Open Birthdays</a>
    </div>
</div>
@endsection
