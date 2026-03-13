@extends('admin.layout')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Admin</div>
        <h2 class="mb-1">User Management</h2>
        <div class="text-muted">Search and manage admin and treasurer accounts.</div>
    </div>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Search name, email, phone">
        </div>
    </div>
    <div class="col-md-2">
        <select name="role" class="form-select">
            <option value="">All Roles</option>
            @foreach(['admin','treasurer'] as $role)
            <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <select name="status" class="form-select">
            <option value="">All Status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="disabled" {{ request('status') === 'disabled' ? 'selected' : '' }}>Disabled</option>
        </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>

@php
    $hasFilters = request('q') || request('role') || request('status');
@endphp
@if($hasFilters)
<div class="small text-muted mb-3">
    Showing {{ $users->count() }} of {{ $users->total() }} result(s)
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone ?: '-' }}</td>
                    <td><span class="badge text-bg-secondary">{{ ucfirst($user->role ?? 'none') }}</span></td>
                    <td>
                        <span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-danger' }}">
                            {{ $user->is_active ? 'Active' : 'Disabled' }}
                        </span>
                    </td>
                    <td>{{ $user->created_at->format('Y-m-d') }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
                        @if(auth()->id() !== $user->id)
                        <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-sm {{ $user->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                {{ $user->is_active ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $users->links('pagination::bootstrap-5') }}
</div>
@endsection
