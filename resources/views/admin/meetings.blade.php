@extends('admin.layout')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Admin</div>
        <h2 class="mb-1">Meetings</h2>
        <div class="text-muted">Create and manage upcoming meetings for members.</div>
    </div>
    <span class="text-muted">{{ now()->format('D, d M Y') }}</span>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Schedule Meeting</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.meetings.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date & Time</label>
                        <input type="datetime-local" name="meeting_at" class="form-control" value="{{ old('meeting_at') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" value="{{ old('location') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="meeting_active" name="is_active" {{ old('is_active', '1') ? 'checked' : '' }}>
                        <label class="form-check-label" for="meeting_active">
                            Publish to viewer dashboard
                        </label>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-primary">Save</button>
                        <a href="{{ route('admin.meetings.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Upcoming Meetings</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($meetings as $meeting)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $meeting->title }}</div>
                                <div class="text-muted small">{{ \Illuminate\Support\Str::limit($meeting->notes, 60) }}</div>
                            </td>
                            <td>{{ $meeting->meeting_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $meeting->location ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $meeting->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $meeting->is_active ? 'Active' : 'Hidden' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.meetings.edit', $meeting) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                                <form action="{{ route('admin.meetings.toggle', $meeting) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-outline-primary btn-sm">
                                        {{ $meeting->is_active ? 'Hide' : 'Show' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.meetings.destroy', $meeting) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this meeting?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">No meetings scheduled.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $meetings->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
