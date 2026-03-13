@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Treasurer Role Guide</h2>
    <span class="text-muted">{{ now()->format('D, d M Y') }}</span>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <p class="mb-2">As a <strong>Treasurer</strong>, you manage day-to-day financial and member records.</p>
        <ul class="mb-0">
            <li>Register members and record admission fees (GHS 200).</li>
            <li>Record monthly dues (GHS 50) and track arrears.</li>
            <li>Record contributions, special contributions, and donations.</li>
            <li>Track income, expenses, loans, and receipts.</li>
            <li>Generate reports and export summaries.</li>
        </ul>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3">
            <div class="fw-semibold mb-2">Members</div>
            <a href="{{ route('members.index') }}" class="btn btn-outline-primary btn-sm">Manage Members</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3">
            <div class="fw-semibold mb-2">Dues</div>
            <a href="{{ route('dues.index') }}" class="btn btn-outline-primary btn-sm">Monthly Dues</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3">
            <div class="fw-semibold mb-2">Special Contributions</div>
            <a href="{{ route('special-contributions.index') }}" class="btn btn-outline-primary btn-sm">Record Special</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3">
            <div class="fw-semibold mb-2">Donations</div>
            <a href="{{ route('donations.index') }}" class="btn btn-outline-primary btn-sm">Record Donations</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3">
            <div class="fw-semibold mb-2">Reports</div>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-primary btn-sm">View Reports</a>
        </div>
    </div>
</div>
@endsection
