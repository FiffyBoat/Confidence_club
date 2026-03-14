@extends('layouts.app')

@section('content')
<div class="page-hero mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
        <div>
            <div class="hero-badge">User Manual</div>
            <h2 class="page-title mb-1">Confidence Club Members Guide</h2>
            <div class="page-subtitle">Step-by-step instructions for admins and treasurers, plus public viewer access.</div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <span class="pill"><i class="bi bi-book"></i>Reference</span>
                <span class="pill"><i class="bi bi-eye"></i>Public ready</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('help.manual.pdf') }}" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
            </a>
            <div class="hero-icon d-none d-lg-flex"><i class="bi bi-journal-text"></i></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <strong>Quick Start</strong>
            </div>
            <div class="card-body">
                <ol class="mb-0">
                    <li>Open the app in your browser.</li>
                    <li>Admin login: <strong>/admin/login</strong></li>
                    <li>Public viewer: <strong>/viewer</strong> (no login)</li>
                    <li>Public directory: <strong>/viewer/members</strong></li>
                    <li>Transparency portal: <strong>/transparency</strong> (if enabled)</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <strong>Roles & Access</strong>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li><strong>Admin</strong>: Full access, manages users, controls public visibility.</li>
                    <li><strong>Treasurer</strong>: Records payments, receipts, and reports.</li>
                    <li><strong>Public Visitors</strong>: No login, view-only dashboard and directory (admin-controlled sections).</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Members</strong>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Members -> Add Member.</li>
                    <li>If admission fee is not ready, uncheck <strong>Record admission fee</strong>.</li>
                    <li>Edit later to record the admission fee.</li>
                    <li>Deleting a member removes related data.</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Contributions & Dues</strong>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Contributions -> Add (Admission Fee, Monthly Dues, Extra Levies).</li>
                    <li>Dues -> Record Monthly Dues payments.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Special Contributions & Donations</strong>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Special contributions minimum is <strong>GHS 100</strong>.</li>
                    <li>Appear on the public dashboard with "Paid" and description when enabled.</li>
                    <li>Donations track pooled special contributions.</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Income, Expenses & Loans</strong>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Income -> Record other income sources.</li>
                    <li>Expenses -> Record club expenses.</li>
                    <li>Loans -> Track lending and repayments.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Receipts & Reports</strong>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Receipts are auto-generated for payments.</li>
                    <li>Use Reports for financial summaries and exports.</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Announcements & Meetings (Admin)</strong>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Admin -> Announcements -> Publish updates.</li>
                    <li>Admin -> Meetings -> Schedule meetings.</li>
                    <li>Admin -> Settings -> Upload constitution + choose what appears on the public dashboard.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-white">
        <strong>Public Viewer Experience</strong>
    </div>
    <div class="card-body">
        <ul class="mb-0">
            <li><strong>/viewer</strong> shows announcements, meetings, special contributions, birthdays, and transparency snapshot (if enabled).</li>
            <li><strong>/viewer/members</strong> lets visitors search by name, ID, phone, or email and view contact details.</li>
            <li><strong>/constitution</strong> appears when the admin uploads a constitution.</li>
            <li>All public visibility is controlled in <strong>Admin → Settings → Viewer Options</strong>.</li>
        </ul>
    </div>
</div>
@endsection
