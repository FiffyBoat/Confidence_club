@extends('layouts.app')

@section('content')
<style>
    .birthday-hero {
        background: linear-gradient(120deg, rgba(176, 0, 32, 0.08), rgba(255, 242, 245, 0.8));
        border: 1px solid rgba(176, 0, 32, 0.1);
    }
    .birthday-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(176, 0, 32, 0.12);
        color: #8f0019;
        border-radius: 999px;
        padding: 0.3rem 0.7rem;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .birthday-list-item {
        border-bottom: 1px dashed rgba(0, 0, 0, 0.06);
    }
    .birthday-list-item:last-child {
        border-bottom: none;
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

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Members</div>
        <h2 class="mb-1">Birthdays</h2>
        <div class="text-muted">Today and the next {{ $windowDays }} days.</div>
    </div>
    <span class="text-muted">{{ $today->format('D, d M Y') }}</span>
</div>

<div class="card birthday-hero shadow-sm border-0 mb-4">
    <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
        <div>
            <div class="birthday-chip"><i class="bi bi-balloon-heart"></i>Celebrations</div>
            <h5 class="mt-2 mb-1">Celebrate our members</h5>
            <div class="text-muted">Stay ahead of birthdays and share warm wishes on behalf of the club.</div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="stat-card">
                <div class="stat-label">Today</div>
                <div class="stat-value">{{ count($birthdaysToday) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Upcoming</div>
                <div class="stat-value">{{ count($birthdaysUpcoming) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Birthdays Today</strong>
                <span class="text-muted small">{{ $today->format('M d') }}</span>
            </div>
            <div class="card-body">
                @forelse($birthdaysToday as $item)
                    <div class="birthday-list-item d-flex align-items-center justify-content-between py-2">
                        <div>
                            <div class="fw-semibold">{{ $item['member']->full_name }}</div>
                            <div class="text-muted small">{{ $item['member']->membership_id }}</div>
                        </div>
                        <span class="badge text-bg-success">Today</span>
                    </div>
                @empty
                    <div class="text-muted">No birthdays today.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Upcoming Birthdays</strong>
                <span class="text-muted small">Next {{ $windowDays }} days</span>
            </div>
            <div class="card-body">
                @forelse($birthdaysUpcoming as $item)
                    <div class="birthday-list-item d-flex align-items-center justify-content-between py-2">
                        <div>
                            <div class="fw-semibold">{{ $item['member']->full_name }}</div>
                            <div class="text-muted small">{{ $item['member']->membership_id }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-semibold">{{ $item['date']->format('M d') }}</div>
                            <div class="text-muted small">{{ $item['days'] }} day(s)</div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted">No upcoming birthdays in the next {{ $windowDays }} days.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <strong>All Birthdays (Sorted by Next Occurrence)</strong>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Membership ID</th>
                    <th>Next Birthday</th>
                    <th class="text-end">Days Away</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allBirthdays as $item)
                <tr>
                    <td>{{ $item['member']->full_name }}</td>
                    <td>{{ $item['member']->membership_id }}</td>
                    <td>{{ $item['date']->format('M d, Y') }}</td>
                    <td class="text-end">
                        @if($item['days'] === 0)
                            <span class="badge text-bg-success">Today</span>
                        @else
                            {{ $item['days'] }}
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">No birthdays recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(count($birthdaysToday) > 0)
    @php
        $celebrant = $birthdaysToday[0]['member'];
        $otherCelebrants = array_slice($birthdaysToday, 1);
    @endphp
    <div class="modal fade birthday-modal" id="birthdayModal" tabindex="-1" aria-labelledby="birthdayModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="birthdayModalLabel">Happy Birthday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="heart-badge"><i class="bi bi-heart-fill"></i></div>
                        <div>
                            <div class="text-muted small">Celebrant of the Day</div>
                            <div class="celebrant">Happy Birthday, {{ $celebrant->full_name }}!</div>
                        </div>
                    </div>
                    <div class="message-box">
                        <p class="mb-2">
                            On behalf of the Confidence Club Members, we celebrate you today. May this new year of life
                            bring you strength, joy, good health, and abundant blessings. You are valued, and we are grateful
                            to have you as part of our family.
                        </p>
                        <p class="mb-0">With love and warm wishes from all Club Members.</p>
                    </div>
                    @if(count($otherCelebrants) > 0)
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
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Send Wishes</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.addEventListener('load', function () {
            if (typeof bootstrap !== 'undefined') {
                var modal = new bootstrap.Modal(document.getElementById('birthdayModal'));
                modal.show();
            }
        });
    </script>
@endif
@endsection
