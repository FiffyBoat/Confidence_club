@extends('layouts.app')

@section('content')
@php
    $role = auth()->user()->role ?? 'treasurer';
    $isTreasurer = $role === 'treasurer';
@endphp

<div class="page-hero mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
        <div>
            <div class="hero-badge">User Manual</div>
            <h2 class="page-title mb-1">{{ config('app.name') }} Guide</h2>
            <div class="page-subtitle">
                @if($isTreasurer)
                    Treasurer-only guidance for recording payments, receipts, and reports.
                @else
                    Everything you need to manage members, payments, receipts, and reports.
                @endif
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <span class="pill"><i class="bi bi-shield-check"></i>Role-based access</span>
                <span class="pill"><i class="bi bi-receipt"></i>Receipts for all payments</span>
                <span class="pill"><i class="bi bi-bar-chart-line"></i>Printable reports</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('help.pdf') }}" class="btn btn-outline-primary"><i class="bi bi-file-earmark-pdf me-1"></i>Open PDF</a>
            <a href="{{ route('help.manual') }}" class="btn btn-outline-secondary"><i class="bi bi-journal-text me-1"></i>Public Manual</a>
            <div class="hero-icon d-none d-lg-flex"><i class="bi bi-book"></i></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <strong>Roles & Access</strong>
            </div>
            <div class="card-body">
                @if($isTreasurer)
                    <div class="d-flex gap-3">
                        <span class="badge text-bg-primary p-3"><i class="bi bi-wallet2"></i></span>
                        <div>
                            <div class="fw-semibold">Treasurer</div>
                            <div class="text-muted small">Records financial activity, generates receipts, and runs reports.</div>
                        </div>
                    </div>
                @else
                    <div class="d-flex gap-3 mb-3">
                        <span class="badge text-bg-secondary p-3"><i class="bi bi-shield-lock"></i></span>
                        <div>
                            <div class="fw-semibold">Admin</div>
                            <div class="text-muted small">Full access to all modules, users, and activity logs.</div>
                        </div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <span class="badge text-bg-primary p-3"><i class="bi bi-wallet2"></i></span>
                        <div>
                            <div class="fw-semibold">Treasurer</div>
                            <div class="text-muted small">Records financial activity, generates receipts, and runs reports.</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <strong>{{ $isTreasurer ? 'Treasurer Modules' : 'Core Modules' }}</strong>
            </div>
            <div class="card-body">
                @if($isTreasurer)
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="stat-card h-100">
                                <div class="stat-label">Members</div>
                                <div class="stat-value">Register & update</div>
                                <div class="text-muted small mt-2">Create members and record admission fees now or later.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card h-100">
                                <div class="stat-label">Monthly Dues</div>
                                <div class="stat-value">GHS 50 / month</div>
                                <div class="text-muted small mt-2">Record dues and track arrears.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card h-100">
                                <div class="stat-label">Contributions</div>
                                <div class="stat-value">All member payments</div>
                                <div class="text-muted small mt-2">Record admissions, dues, and special contributions.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card h-100">
                                <div class="stat-label">Special Contributions</div>
                                <div class="stat-value">Purpose-driven</div>
                                <div class="text-muted small mt-2">Record special contributions (min GHS 100).</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card h-100">
                                <div class="stat-label">Income & Expenses</div>
                                <div class="stat-value">Group finance</div>
                                <div class="text-muted small mt-2">Record operational inflows and outflows.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card h-100">
                                <div class="stat-label">Loans</div>
                                <div class="stat-value">Issue & repay</div>
                                <div class="text-muted small mt-2">Track balances and generate receipts.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card h-100">
                                <div class="stat-label">Receipts</div>
                                <div class="stat-value">View / Print</div>
                                <div class="text-muted small mt-2">Access PDF receipts for every payment.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card h-100">
                                <div class="stat-label">Reports</div>
                                <div class="stat-value">PDF + CSV</div>
                                <div class="text-muted small mt-2">Run summary and detailed financial reports.</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="stat-card h-100">
                                <div class="stat-label">Members</div>
                                <div class="stat-value">Profiles & admission fee</div>
                                <div class="text-muted small mt-2">Register members and optionally record GHS 200 admission fee now or later.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card h-100">
                                <div class="stat-label">Monthly Dues</div>
                                <div class="stat-value">GHS 50 / month</div>
                                <div class="text-muted small mt-2">Track paid months, arrears, and expected balances.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card h-100">
                                <div class="stat-label">Contributions</div>
                                <div class="stat-value">All member payments</div>
                                <div class="text-muted small mt-2">Record admission, dues, and special contributions.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card h-100">
                                <div class="stat-label">Loans</div>
                                <div class="stat-value">Issue & repayment</div>
                                <div class="text-muted small mt-2">Track balances and generate receipts for repayments.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card h-100">
                                <div class="stat-label">Income & Expenses</div>
                                <div class="stat-value">Group finance</div>
                                <div class="text-muted small mt-2">Record operational inflows and outflows.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card h-100">
                                <div class="stat-label">Receipts & Reports</div>
                                <div class="stat-value">PDF + CSV</div>
                                <div class="text-muted small mt-2">View, print, and download receipts and reports.</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <strong>Business Rules</strong>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Monthly dues are GHS 50 per member.</li>
                    <li>Admission fee is GHS 200 and can be recorded later if unpaid at registration.</li>
                    <li>Special contributions must be at least GHS 100 and require a purpose.</li>
                    <li>Every payment automatically generates a receipt.</li>
                    <li>Viewer dashboard is public and only shows sections enabled in Admin Settings.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <strong>Receipts</strong>
            </div>
            <div class="card-body">
                <div class="text-muted mb-2">Receipts are created for:</div>
                <ul class="mb-0">
                    <li>Admission fees</li>
                    <li>Monthly dues</li>
                    <li>Contributions and special contributions</li>
                    <li>Income records</li>
                    <li>Loan repayments</li>
                </ul>
                <div class="text-muted small mt-3">Receipts include club name, logo, payment type, and a PAID watermark.</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mt-4">
    <div class="card-header bg-white">
        <strong>{{ $isTreasurer ? 'Treasurer Tasks' : 'Common Tasks' }}</strong>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @if($isTreasurer)
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <span class="badge text-bg-secondary p-3"><i class="bi bi-person-plus"></i></span>
                        <div>
                            <div class="fw-semibold">Register a member</div>
                            <div class="text-muted small">Members → Add Member → Save. Leave admission fee unchecked if unpaid; edit later to record payment.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <span class="badge text-bg-primary p-3"><i class="bi bi-calendar-check"></i></span>
                        <div>
                            <div class="fw-semibold">Record monthly dues</div>
                            <div class="text-muted small">Dues → Select member + month → Save.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <span class="badge text-bg-success p-3"><i class="bi bi-stars"></i></span>
                        <div>
                            <div class="fw-semibold">Record special contribution</div>
                            <div class="text-muted small">Special Contributions → Add amount (min GHS 100) + purpose → Save. Appears as Paid on viewer dashboard if enabled.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <span class="badge text-bg-warning p-3"><i class="bi bi-receipt"></i></span>
                        <div>
                            <div class="fw-semibold">Print a receipt</div>
                            <div class="text-muted small">Receipts → View / Print → Download if needed.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <span class="badge text-bg-danger p-3"><i class="bi bi-file-earmark-text"></i></span>
                        <div>
                            <div class="fw-semibold">Run reports</div>
                            <div class="text-muted small">Reports → Export PDF/CSV or open detailed report.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <span class="badge text-bg-secondary p-3"><i class="bi bi-bank"></i></span>
                        <div>
                            <div class="fw-semibold">Record loan repayment</div>
                            <div class="text-muted small">Loans → Open loan → Record repayment.</div>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <span class="badge text-bg-secondary p-3"><i class="bi bi-person-plus"></i></span>
                        <div>
                            <div class="fw-semibold">Register a member</div>
                            <div class="text-muted small">Members → Add Member → Save. Leave admission fee unchecked if unpaid; edit later to record payment.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <span class="badge text-bg-primary p-3"><i class="bi bi-calendar-check"></i></span>
                        <div>
                            <div class="fw-semibold">Record monthly dues</div>
                            <div class="text-muted small">Dues → Select member + month → Save.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <span class="badge text-bg-success p-3"><i class="bi bi-stars"></i></span>
                        <div>
                            <div class="fw-semibold">Record special contribution</div>
                            <div class="text-muted small">Special Contributions → Add amount (min GHS 100) + purpose → Save. Appears as Paid on viewer dashboard if enabled.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <span class="badge text-bg-warning p-3"><i class="bi bi-receipt"></i></span>
                        <div>
                            <div class="fw-semibold">Print a receipt</div>
                            <div class="text-muted small">Receipts → View / Print → Download if needed.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <span class="badge text-bg-danger p-3"><i class="bi bi-file-earmark-text"></i></span>
                        <div>
                            <div class="fw-semibold">Run reports</div>
                            <div class="text-muted small">Reports → Export PDF/CSV or open detailed report.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <span class="badge text-bg-secondary p-3"><i class="bi bi-gear"></i></span>
                        <div>
                            <div class="fw-semibold">Update viewer settings</div>
                            <div class="text-muted small">Settings → Viewer options → Choose what is visible on the public dashboard.</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
