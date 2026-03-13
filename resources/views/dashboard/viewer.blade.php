@extends('layouts.app')

@section('content')
<div class="page-hero mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
        <div>
            <div class="hero-badge">Viewer Dashboard</div>
            <h2 class="page-title mb-1">Welcome back, {{ auth()->user()->name ?? 'Member' }}</h2>
            <div class="page-subtitle">Snapshot for {{ $today->format('l, d M Y') }}</div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <span class="pill"><i class="bi bi-eye"></i>Public</span>
                <span class="pill"><i class="bi bi-calendar-event"></i>{{ $today->format('M Y') }}</span>
            </div>
        </div>
        <div class="hero-icon d-none d-lg-flex"><i class="bi bi-speedometer2"></i></div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="fw-semibold mb-2">Quick Links</div>
                <div class="d-flex flex-wrap gap-2">
                    @auth
                        <a href="{{ route('role-guide') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-shield-check me-1"></i>Role Guide</a>
                        <a href="{{ route('help.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-journal-text me-1"></i>User Manual</a>
                    @endauth
                    <a href="{{ route('help.manual') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-journal-text me-1"></i>Public Manual</a>
                    @if($visibility['constitution'])
                        <a href="{{ route('constitution.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-text me-1"></i>Constitution</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($visibility['constitution'])
    <div class="col-md-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <div class="fw-semibold">Constitution</div>
                    <div class="text-muted small">
                        @if($constitutionExists)
                            {{ $constitutionName }}
                        @else
                            No constitution uploaded yet.
                        @endif
                    </div>
                </div>
                <a href="{{ route('constitution.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-eye me-1"></i>View Document
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="row g-3">
    @if($visibility['announcements'])
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Latest Announcements</strong>
                <span class="text-muted small">{{ $announcements->count() }} update(s)</span>
            </div>
            <div class="card-body">
                @forelse($announcements as $announcement)
                    <div class="mb-3">
                        <div class="fw-semibold">{{ $announcement->title }}</div>
                        <div class="text-muted small">{{ $announcement->body }}</div>
                        <div class="text-muted small mt-1">
                            @if($announcement->starts_at)
                                Starts {{ $announcement->starts_at->format('Y-m-d H:i') }}
                            @endif
                            @if($announcement->ends_at)
                                - Ends {{ $announcement->ends_at->format('Y-m-d H:i') }}
                            @endif
                        </div>
                    </div>
                    @if(! $loop->last)
                        <hr>
                    @endif
                @empty
                    <div class="text-muted">No announcements at the moment.</div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    @if($visibility['meetings'])
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Upcoming Meetings</strong>
                <span class="text-muted small">Next {{ $meetings->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Meeting</th>
                            <th>Date</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($meetings as $meeting)
                        <tr>
                            <td>{{ $meeting->title }}</td>
                            <td>{{ $meeting->meeting_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $meeting->location ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-muted">No upcoming meetings.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

@if($visibility['directory'] || $visibility['birthdays'])
<div class="row g-3 mt-3">
    @if($visibility['directory'])
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Member Directory (Limited)</strong>
                <a href="{{ route('viewer.members') }}" class="btn btn-outline-primary btn-sm">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Membership ID</th>
                            <th>Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($directory as $member)
                        <tr>
                            <td>{{ $member->membership_id }}</td>
                            <td>{{ $member->full_name }}</td>
                            <td>
                                <span class="badge {{ $member->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ ucfirst($member->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-muted">No members found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($visibility['birthdays'])
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <strong>Birthday Highlights</strong>
            </div>
            <div class="card-body">
                <div class="fw-semibold mb-2">Upcoming (Next 7 Days)</div>
                @forelse($birthdaysUpcoming as $item)
                    <div class="d-flex justify-content-between align-items-center">
                        <div>{{ $item['member']->full_name }}</div>
                        <div class="text-muted small">{{ $item['date']->format('M d') }}</div>
                    </div>
                @empty
                    <div class="text-muted small mb-3">No upcoming birthdays this week.</div>
                @endforelse

                <hr>

                <div class="fw-semibold mb-2">This Month</div>
                @forelse($birthdaysThisMonth as $member)
                    <div class="d-flex justify-content-between align-items-center">
                        <div>{{ $member->full_name }}</div>
                        <div class="text-muted small">{{ \Carbon\Carbon::create(2024, $member->birth_month, $member->birth_day)->format('M d') }}</div>
                    </div>
                @empty
                    <div class="text-muted small">No birthdays recorded this month.</div>
                @endforelse
            </div>
        </div>
    </div>
    @endif
</div>
@endif

@if($visibility['special_contributions'])
<div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Special Contributions</strong>
        <span class="text-muted small">{{ $specialContributions->count() }} payment(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Status</th>
                    <th>Description</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($specialContributions as $contribution)
                <tr>
                    <td>{{ $contribution->member?->full_name ?? 'Member removed' }}</td>
                    <td><span class="badge text-bg-success">Paid</span></td>
                    <td>{{ $contribution->description }}</td>
                    <td>{{ $contribution->transaction_date?->format('Y-m-d') ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-muted">No special contributions recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

@php
    $snapshotVisible = $visibility['transparency_snapshot'] && in_array(true, $transparencyVisibility, true);
@endphp

@if($snapshotVisible)
<div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Transparency Snapshot</strong>
        <a href="{{ route('transparency') }}" class="btn btn-outline-primary btn-sm">View Portal</a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @if($transparencyVisibility['total_members'])
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Total Members</div>
                    <div class="stat-value">{{ $transparency['total_members'] }}</div>
                </div>
            </div>
            @endif
            @if($transparencyVisibility['total_contributions'])
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Total Contributions</div>
                    <div class="stat-value">GHS {{ number_format($transparency['total_contributions'], 2) }}</div>
                </div>
            </div>
            @endif
            @if($transparencyVisibility['total_income'])
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Total Income</div>
                    <div class="stat-value">GHS {{ number_format($transparency['total_income'], 2) }}</div>
                </div>
            </div>
            @endif
            @if($transparencyVisibility['total_repayments'])
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Total Repayments</div>
                    <div class="stat-value">GHS {{ number_format($transparency['total_repayments'], 2) }}</div>
                </div>
            </div>
            @endif
            @if($transparencyVisibility['total_expenses'])
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Total Expenses</div>
                    <div class="stat-value">GHS {{ number_format($transparency['total_expenses'], 2) }}</div>
                </div>
            </div>
            @endif
            @if($transparencyVisibility['net_balance'])
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Net Balance</div>
                    <div class="stat-value">GHS {{ number_format($transparency['net_balance'], 2) }}</div>
                </div>
            </div>
            @endif
            @if($transparencyVisibility['outstanding_loans'])
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Outstanding Loans</div>
                    <div class="stat-value">GHS {{ number_format($transparency['outstanding_loans'], 2) }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif
@endsection
