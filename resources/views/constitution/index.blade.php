@extends('layouts.app')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Documents</div>
        <h2 class="mb-1">Club Constitution</h2>
        <div class="text-muted">View the latest constitution approved by the club.</div>
    </div>
    <span class="text-muted">{{ now()->format('D, d M Y') }}</span>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        @if($constitutionExists)
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="fw-semibold">{{ $constitutionName }}</div>
                    <div class="text-muted small">Use the button to download or open the document.</div>
                </div>
                <a href="{{ route('constitution.download') }}" class="btn btn-outline-primary">
                    <i class="bi bi-download me-1"></i>Download Constitution
                </a>
            </div>
        @else
            <div class="alert alert-info mb-0">
                The constitution has not been uploaded yet. Please check back later.
            </div>
        @endif
    </div>
</div>
@endsection
