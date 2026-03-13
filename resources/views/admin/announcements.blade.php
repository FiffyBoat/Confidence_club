@extends('admin.layout')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Admin</div>
        <h2 class="mb-1">Announcements</h2>
        <div class="text-muted">Publish short updates for the viewer dashboard.</div>
    </div>
    <span class="text-muted">{{ now()->format('D, d M Y') }}</span>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>New Announcement</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.announcements.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="body" rows="4" class="form-control" required>{{ old('body') }}</textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Start (optional)</label>
                            <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End (optional)</label>
                            <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at') }}">
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" {{ old('is_active', '1') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Publish immediately
                        </label>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-primary">Publish</button>
                        <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Recent Announcements</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Starts</th>
                            <th>Ends</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($announcements as $announcement)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $announcement->title }}</div>
                                <div class="text-muted small">{{ \Illuminate\Support\Str::limit($announcement->body, 70) }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $announcement->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $announcement->is_active ? 'Active' : 'Hidden' }}
                                </span>
                            </td>
                            <td>{{ $announcement->starts_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>{{ $announcement->ends_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                                <form action="{{ route('admin.announcements.toggle', $announcement) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-outline-primary btn-sm">
                                        {{ $announcement->is_active ? 'Hide' : 'Show' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this announcement?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">No announcements yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $announcements->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
