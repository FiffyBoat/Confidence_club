@extends('layouts.app')

@section('content')
<style>
    .birthday-card {
        position: relative;
        overflow: hidden;
    }
    .birthday-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255, 214, 224, 0.35), transparent 40%);
        pointer-events: none;
    }
    .birthday-spotlight {
        position: relative;
        background: linear-gradient(135deg, rgba(176, 0, 32, 0.08), rgba(255, 247, 249, 0.98));
        border: 1px solid rgba(176, 0, 32, 0.12);
        border-radius: 1.1rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .birthday-initials {
        width: 3.25rem;
        height: 3.25rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        letter-spacing: 0.08em;
        color: #8f0019;
        background: rgba(176, 0, 32, 0.12);
        border: 1px solid rgba(176, 0, 32, 0.14);
        flex-shrink: 0;
    }
    .birthday-chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
    }
    .birthday-today-pill {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.55rem 0.8rem;
        border-radius: 999px;
        background: rgba(34, 197, 94, 0.08);
        border: 1px solid rgba(34, 197, 94, 0.15);
    }
    .birthday-today-pill .birthday-initials {
        width: 2.35rem;
        height: 2.35rem;
        font-size: 0.82rem;
    }
    .birthday-quote {
        font-style: italic;
        color: #7a4350;
    }
    .birthday-countdown-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.85rem;
        padding: 0.65rem 0;
        border-bottom: 1px dashed rgba(0, 0, 0, 0.08);
    }
    .birthday-countdown-item:last-child {
        border-bottom: none;
    }
    .birthday-month-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 0.75rem;
    }
    .birthday-month-card {
        border: 1px solid rgba(176, 0, 32, 0.12);
        border-radius: 1rem;
        background: #fff9fa;
        padding: 0.85rem;
    }
    .birthday-month-card .birthday-initials {
        width: 2.5rem;
        height: 2.5rem;
        font-size: 0.85rem;
        margin-bottom: 0.65rem;
    }
    .birthday-countdown-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
        background: rgba(176, 0, 32, 0.08);
        color: #8f0019;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .birthday-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }
    .birthday-copy-feedback {
        display: none;
        font-size: 0.82rem;
        color: #198754;
        margin-top: 0.4rem;
    }
    .birthday-copy-feedback.is-visible {
        display: block;
    }
    .birthday-confetti {
        position: absolute;
        inset: 0;
        overflow: hidden;
        pointer-events: none;
    }
    .birthday-confetti span {
        position: absolute;
        top: -1.2rem;
        width: 0.7rem;
        height: 1.1rem;
        border-radius: 0.25rem;
        opacity: 0.75;
        animation: birthday-confetti-fall 4.5s linear infinite;
    }
    .birthday-confetti span:nth-child(odd) {
        background: #f06292;
    }
    .birthday-confetti span:nth-child(even) {
        background: #ffd166;
    }
    .birthday-confetti span:nth-child(3n) {
        background: #7dd3fc;
    }
    .birthday-confetti span:nth-child(1) { left: 8%; animation-delay: 0s; }
    .birthday-confetti span:nth-child(2) { left: 18%; animation-delay: 1.1s; }
    .birthday-confetti span:nth-child(3) { left: 28%; animation-delay: 0.6s; }
    .birthday-confetti span:nth-child(4) { left: 40%; animation-delay: 1.8s; }
    .birthday-confetti span:nth-child(5) { left: 54%; animation-delay: 0.3s; }
    .birthday-confetti span:nth-child(6) { left: 66%; animation-delay: 1.4s; }
    .birthday-confetti span:nth-child(7) { left: 78%; animation-delay: 0.8s; }
    .birthday-confetti span:nth-child(8) { left: 90%; animation-delay: 1.9s; }
    @keyframes birthday-confetti-fall {
        0% { transform: translateY(0) rotate(0deg); opacity: 0; }
        10% { opacity: 0.8; }
        100% { transform: translateY(360px) rotate(320deg); opacity: 0; }
    }
    .birthday-modal .modal-content {
        background: linear-gradient(135deg, #fff6f8 0%, #ffffff 45%, #fff1f3 100%);
        border-radius: 1.2rem;
        overflow: hidden;
    }
    .birthday-modal .modal-header {
        background: rgba(176, 0, 32, 0.08);
    }
    .birthday-modal .modal-title {
        font-weight: 700;
    }
    .birthday-modal .celebrant {
        font-size: 1.25rem;
        font-weight: 700;
        color: #8f0019;
    }
    .birthday-modal .heart-badge {
        width: 56px;
        height: 56px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(176, 0, 32, 0.12);
        color: #b00020;
        font-size: 1.4rem;
    }
    .birthday-modal .message-box {
        background: #ffffff;
        border: 1px solid rgba(176, 0, 32, 0.12);
        border-radius: 0.9rem;
        padding: 1rem;
    }
    .birthday-modal .modal-footer {
        background: rgba(176, 0, 32, 0.04);
    }
</style>

<div class="page-hero mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
        <div>
            <div class="hero-badge">Viewer Dashboard</div>
            <h2 class="page-title mb-1">Welcome back, {{ auth()->user()->name ?? 'Member' }}</h2>
            <div class="page-subtitle">Snapshot for {{ $today->format('l, d M Y') }}</div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <span class="pill"><i class="bi bi-eye"></i>Public</span>
                <span class="pill"><i class="bi bi-calendar-event"></i>{{ $today->format('M Y') }}</span>
            </div>
        </div>
        <div class="hero-icon d-none d-lg-flex"><i class="bi bi-speedometer2"></i></div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="fw-semibold mb-2">Quick Links</div>
                <div class="d-flex flex-wrap gap-2">
                    @auth
                        <a href="{{ route('role-guide') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-shield-check me-1"></i>Role Guide</a>
                        <a href="{{ route('help.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-journal-text me-1"></i>User Manual</a>
                    @endauth
                    <a href="{{ route('help.manual') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-journal-text me-1"></i>Public Manual</a>
                    @if($visibility['constitution'])
                        <a href="{{ route('constitution.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-text me-1"></i>Constitution</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($visibility['constitution'])
    <div class="col-md-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <div class="fw-semibold">Constitution</div>
                    <div class="text-muted small">
                        @if($constitutionExists)
                            {{ $constitutionName }}
                        @else
                            No constitution uploaded yet.
                        @endif
                    </div>
                </div>
                <a href="{{ route('constitution.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-eye me-1"></i>View Document
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="row g-3">
    @if($visibility['announcements'])
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Latest Announcements</strong>
                <span class="text-muted small">{{ $announcements->count() }} update(s)</span>
            </div>
            <div class="card-body">
                @forelse($announcements as $announcement)
                    <div class="mb-3">
                        <div class="fw-semibold">{{ $announcement->title }}</div>
                        <div class="text-muted small">{{ $announcement->body }}</div>
                        <div class="text-muted small mt-1">
                            @if($announcement->starts_at)
                                Starts {{ $announcement->starts_at->format('Y-m-d H:i') }}
                            @endif
                            @if($announcement->ends_at)
                                - Ends {{ $announcement->ends_at->format('Y-m-d H:i') }}
                            @endif
                        </div>
                    </div>
                    @if(! $loop->last)
                        <hr>
                    @endif
                @empty
                    <div class="text-muted">No announcements at the moment.</div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    @if($visibility['meetings'])
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Upcoming Meetings</strong>
                <span class="text-muted small">Next {{ $meetings->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Meeting</th>
                            <th>Date</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($meetings as $meeting)
                        <tr>
                            <td>{{ $meeting->title }}</td>
                            <td>{{ $meeting->meeting_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $meeting->location ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-muted">No upcoming meetings.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

@if($visibility['directory'] || $visibility['birthdays'])
<div class="row g-3 mt-3">
    @if($visibility['directory'])
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Member Directory (Limited)</strong>
                <a href="{{ route('viewer.members') }}" class="btn btn-outline-primary btn-sm">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Membership ID</th>
                            <th>Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($directory as $member)
                        <tr>
                            <td>{{ $member->membership_id }}</td>
                            <td>{{ $member->full_name }}</td>
                            <td>
                                <span class="badge {{ $member->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ ucfirst($member->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-muted">No members found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($visibility['birthdays'])
    <div class="col-lg-5">
        <div class="card birthday-card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <strong>Birthday Highlights</strong>
            </div>
            <div class="card-body">
                @if($birthdaysToday->isNotEmpty())
                    <div class="birthday-confetti" aria-hidden="true">
                        <span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span>
                    </div>
                @endif

                @if($birthdaySpotlight)
                    <div class="birthday-spotlight">
                        <div class="d-flex align-items-start gap-3">
                            <div class="birthday-initials">{{ $birthdaySpotlight['initials'] }}</div>
                            <div class="flex-grow-1">
                                <div class="text-muted small">
                                    {{ $birthdaySpotlight['days'] === 0 ? 'Today\'s spotlight' : 'Next celebrant' }}
                                </div>
                                <div class="fw-semibold">{{ $birthdaySpotlight['member']->full_name }}</div>
                                <div class="text-muted small">{{ $birthdaySpotlight['date']->format('l, M d') }}</div>
                                <div class="birthday-countdown-badge mt-2">
                                    @if($birthdaySpotlight['days'] === 0)
                                        <i class="bi bi-stars"></i> Celebrating today
                                    @else
                                        <i class="bi bi-hourglass-split"></i>
                                        {{ $birthdaySpotlight['days'] }} {{ $birthdaySpotlight['days'] === 1 ? 'day' : 'days' }} to go
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="birthday-quote mt-3">"{{ $birthdaySpotlight['quote'] }}"</div>
                        <div class="birthday-actions mt-3">
                            <button
                                type="button"
                                class="btn btn-outline-primary btn-sm js-copy-birthday"
                                data-copy-text="{{ $birthdaySpotlight['share_text'] }}"
                                data-feedback-target="spotlight-copy-feedback"
                            >
                                <i class="bi bi-clipboard me-1"></i>Copy Message
                            </button>
                            <a href="{{ $birthdaySpotlight['whatsapp_url'] }}" target="_blank" rel="noopener" class="btn btn-success btn-sm">
                                <i class="bi bi-whatsapp me-1"></i>Send via WhatsApp
                            </a>
                        </div>
                        <div id="spotlight-copy-feedback" class="birthday-copy-feedback">Birthday message copied.</div>
                    </div>
                @endif

                <div class="fw-semibold mb-2">Today</div>
                @forelse($birthdaysToday as $item)
                    <div class="birthday-today-pill">
                        <div class="birthday-initials">{{ $item['initials'] }}</div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $item['member']->full_name }}</div>
                            <div class="text-muted small">{{ $item['quote'] }}</div>
                        </div>
                        <a href="{{ $item['whatsapp_url'] }}" target="_blank" rel="noopener" class="btn btn-success btn-sm">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                @empty
                    <div class="text-muted small mb-3">No birthdays today.</div>
                @endforelse

                <hr>

                <div class="fw-semibold mb-2">Birthday Countdown</div>
                @forelse($birthdaysUpcoming as $item)
                    <div class="birthday-countdown-item">
                        <div class="d-flex align-items-center gap-2">
                            <div class="birthday-initials" style="width: 2.35rem; height: 2.35rem; font-size: 0.82rem;">{{ $item['initials'] }}</div>
                            <div>
                                <div class="fw-semibold">{{ $item['member']->full_name }}</div>
                                <div class="text-muted small">{{ $item['quote'] }}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small">{{ $item['date']->format('M d') }}</div>
                            <div class="birthday-countdown-badge mt-1">
                                {{ $item['days'] }} {{ $item['days'] === 1 ? 'day' : 'days' }} to go
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small mb-3">No birthday countdowns in the next 7 days.</div>
                @endforelse

                <hr>

                <div class="fw-semibold mb-2">This Month</div>
                <div class="birthday-month-grid">
                @forelse($birthdaysThisMonth as $member)
                    <div class="birthday-month-card">
                        <div class="birthday-initials">{{ $member['initials'] }}</div>
                        <div class="fw-semibold">{{ $member['member']->full_name }}</div>
                        <div class="text-muted small">{{ $member['date']->format('M d') }}</div>
                        <div class="mt-2">
                            @if($member['days'] === 0)
                                <span class="badge text-bg-success">Today</span>
                            @elseif($member['days'] !== null)
                                <span class="birthday-countdown-badge">{{ $member['days'] }} {{ $member['days'] === 1 ? 'day' : 'days' }} left</span>
                            @else
                                <span class="text-muted small">Earlier this month</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">No birthdays recorded this month.</div>
                @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endif

@if($visibility['special_contributions'])
<div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Special Contributions</strong>
        <span class="text-muted small">{{ $specialContributions->count() }} payment(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Status</th>
                    <th>Description</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($specialContributions as $contribution)
                <tr>
                    <td>{{ $contribution->member?->full_name ?? 'Member removed' }}</td>
                    <td><span class="badge text-bg-success">Paid</span></td>
                    <td>{{ $contribution->description }}</td>
                    <td>{{ $contribution->transaction_date?->format('Y-m-d') ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-muted">No special contributions recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

@php
    $snapshotVisible = $visibility['transparency_snapshot'] && in_array(true, $transparencyVisibility, true);
@endphp

@if($snapshotVisible)
<div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Transparency Snapshot</strong>
        <a href="{{ route('transparency') }}" class="btn btn-outline-primary btn-sm">View Portal</a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @if($transparencyVisibility['total_members'])
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Total Members</div>
                    <div class="stat-value">{{ $transparency['total_members'] }}</div>
                </div>
            </div>
            @endif
            @if($transparencyVisibility['total_contributions'])
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Total Contributions</div>
                    <div class="stat-value">GHS {{ number_format($transparency['total_contributions'], 2) }}</div>
                </div>
            </div>
            @endif
            @if($transparencyVisibility['total_income'])
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Total Income</div>
                    <div class="stat-value">GHS {{ number_format($transparency['total_income'], 2) }}</div>
                </div>
            </div>
            @endif
            @if($transparencyVisibility['total_repayments'])
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Total Repayments</div>
                    <div class="stat-value">GHS {{ number_format($transparency['total_repayments'], 2) }}</div>
                </div>
            </div>
            @endif
            @if($transparencyVisibility['total_expenses'])
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Total Expenses</div>
                    <div class="stat-value">GHS {{ number_format($transparency['total_expenses'], 2) }}</div>
                </div>
            </div>
            @endif
            @if($transparencyVisibility['net_balance'])
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Net Balance</div>
                    <div class="stat-value">GHS {{ number_format($transparency['net_balance'], 2) }}</div>
                </div>
            </div>
            @endif
            @if($transparencyVisibility['outstanding_loans'])
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Outstanding Loans</div>
                    <div class="stat-value">GHS {{ number_format($transparency['outstanding_loans'], 2) }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

@if($visibility['birthdays'] && $birthdaysToday->isNotEmpty())
    @php
        $celebrant = $birthdaysToday->first();
        $otherCelebrants = $birthdaysToday->slice(1);
    @endphp
    <div class="modal fade birthday-modal" id="viewerBirthdayModal" tabindex="-1" aria-labelledby="viewerBirthdayModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="viewerBirthdayModalLabel">Happy Birthday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="birthday-initials">{{ $celebrant['initials'] }}</div>
                        <div>
                            <div class="text-muted small">Celebrant of the Day</div>
                            <div class="celebrant">Happy Birthday, {{ $celebrant['member']->full_name }}!</div>
                        </div>
                    </div>
                    <div class="message-box">
                        <div>{!! nl2br(e($celebrant['message'])) !!}</div>
                        <div class="birthday-quote mt-3">"{{ $celebrant['quote'] }}"</div>
                    </div>
                    @if($otherCelebrants->isNotEmpty())
                        <hr>
                        <div class="text-muted small mb-2">Also celebrating today:</div>
                        <ul class="mb-0">
                            @foreach($otherCelebrants as $item)
                                <li>{{ $item['member']->full_name }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="modal-footer border-0">
                    <button
                        type="button"
                        class="btn btn-outline-primary js-copy-birthday"
                        data-copy-text="{{ $celebrant['share_text'] }}"
                        data-feedback-target="modal-copy-feedback"
                    >
                        <i class="bi bi-clipboard me-1"></i>Copy Message
                    </button>
                    <a href="{{ $celebrant['whatsapp_url'] }}" target="_blank" rel="noopener" class="btn btn-success">
                        <i class="bi bi-whatsapp me-1"></i>Wish on WhatsApp
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Celebrate</button>
                </div>
                <div id="modal-copy-feedback" class="birthday-copy-feedback px-3 pb-3">Birthday message copied.</div>
            </div>
        </div>
    </div>
@endif

@if($visibility['birthdays'] && ($birthdaySpotlight || $birthdaysToday->isNotEmpty()))
    <script>
        document.addEventListener('click', async function (event) {
            var button = event.target.closest('.js-copy-birthday');
            if (!button) {
                return;
            }

            var text = button.getAttribute('data-copy-text') || '';
            var feedbackId = button.getAttribute('data-feedback-target');
            var feedback = feedbackId ? document.getElementById(feedbackId) : null;

            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(text);
                } else {
                    var temp = document.createElement('textarea');
                    temp.value = text;
                    document.body.appendChild(temp);
                    temp.select();
                    document.execCommand('copy');
                    document.body.removeChild(temp);
                }

                if (feedback) {
                    feedback.classList.add('is-visible');
                    window.setTimeout(function () {
                        feedback.classList.remove('is-visible');
                    }, 2200);
                }
            } catch (error) {
                console.error('Birthday copy failed', error);
            }
        });

        @if($birthdaysToday->isNotEmpty())
        window.addEventListener('load', function () {
            if (typeof bootstrap !== 'undefined') {
                var modal = new bootstrap.Modal(document.getElementById('viewerBirthdayModal'));
                modal.show();
            }
        });
        @endif
    </script>
@endif
@endsection
