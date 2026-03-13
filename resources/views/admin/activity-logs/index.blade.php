@extends('admin.layout')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Admin</div>
        <h2 class="mb-1">Activity Logs</h2>
        <div class="text-muted">Recent actions across the system.</div>
    </div>
    <form method="POST" action="{{ route('admin.activity-logs.clear') }}" onsubmit="return confirm('Clear all activity logs? This cannot be undone.');">
        @csrf
        @method('DELETE')
        <button class="btn btn-outline-danger"><i class="bi bi-trash3 me-1"></i>Clear Logs</button>
    </form>
</div>

<div class="card border-0 shadow-sm">
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
                @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $log->user->name ?? 'System' }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->description }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">No activity logs found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $logs->links('pagination::bootstrap-5') }}
</div>
@endsection
