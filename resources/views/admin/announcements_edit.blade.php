@extends('admin.layout')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Admin</div>
        <h2 class="mb-1">Edit Announcement</h2>
        <div class="text-muted">Update the announcement details.</div>
    </div>
    <span class="text-muted">{{ now()->format('D, d M Y') }}</span>
</div>

@php
    $startsAt = old('starts_at', $announcement->starts_at?->format('Y-m-d\\TH:i'));
    $endsAt = old('ends_at', $announcement->ends_at?->format('Y-m-d\\TH:i'));
@endphp

<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <strong>Announcement Details</strong>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.announcements.update', $announcement) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $announcement->title) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea name="body" rows="4" class="form-control" required>{{ old('body', $announcement->body) }}</textarea>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Start (optional)</label>
                    <input type="datetime-local" name="starts_at" class="form-control" value="{{ $startsAt }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">End (optional)</label>
                    <input type="datetime-local" name="ends_at" class="form-control" value="{{ $endsAt }}">
                </div>
            </div>
            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" {{ old('is_active', $announcement->is_active ? '1' : '0') ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                    Visible to viewers
                </label>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </form>
    </div>
</div>
@endsection
