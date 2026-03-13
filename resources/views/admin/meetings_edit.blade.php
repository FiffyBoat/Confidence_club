@extends('admin.layout')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Admin</div>
        <h2 class="mb-1">Edit Meeting</h2>
        <div class="text-muted">Update the meeting schedule and details.</div>
    </div>
    <span class="text-muted">{{ now()->format('D, d M Y') }}</span>
</div>

@php
    $meetingAt = old('meeting_at', $meeting->meeting_at?->format('Y-m-d\\TH:i'));
@endphp

<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <strong>Meeting Details</strong>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.meetings.update', $meeting) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $meeting->title) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Date & Time</label>
                <input type="datetime-local" name="meeting_at" class="form-control" value="{{ $meetingAt }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-control" value="{{ old('location', $meeting->location) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Notes (optional)</label>
                <textarea name="notes" rows="3" class="form-control">{{ old('notes', $meeting->notes) }}</textarea>
            </div>
            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" value="1" id="meeting_active" name="is_active" {{ old('is_active', $meeting->is_active ? '1' : '0') ? 'checked' : '' }}>
                <label class="form-check-label" for="meeting_active">
                    Publish to viewer dashboard
                </label>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.meetings.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </form>
    </div>
</div>
@endsection
