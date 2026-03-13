@extends('layouts.app')

@section('content')
<div class="page-hero mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
        <div>
            <div class="hero-badge">Public Directory</div>
            <h2 class="page-title mb-1">Member Directory</h2>
            <div class="page-subtitle">Public view of members and contact details.</div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <span class="pill"><i class="bi bi-eye"></i>Public</span>
                <span class="pill"><i class="bi bi-people"></i>{{ $members->total() }} members</span>
            </div>
        </div>
        <div class="hero-icon d-none d-lg-flex"><i class="bi bi-people"></i></div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-8">
                <label class="form-label small text-muted mb-1">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Search name, membership ID, phone, or email" value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2 align-items-end">
                <button class="btn btn-primary w-100">Search</button>
                <a href="{{ route('viewer.members') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Membership ID</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                <tr>
                    <td>{{ $member->membership_id }}</td>
                    <td>{{ $member->full_name }}</td>
                    <td>
                        <div class="fw-semibold">{{ $member->phone }}</div>
                        <div class="text-muted small">{{ $member->email ?? '-' }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $member->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ ucfirst($member->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-muted">No members available yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        {{ $members->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
