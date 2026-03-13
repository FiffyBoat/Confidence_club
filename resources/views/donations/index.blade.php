@extends('layouts.app')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Member Support</div>
        <h2 class="mb-1">Donations</h2>
        <div class="text-muted">Convert special contributions into donations and track remaining balances.</div>
    </div>
    <span class="text-muted">{{ now()->format('D, d M Y') }}</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Record Donation From Special Contribution</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('donations.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Special Contribution Pool</label>
                            <select name="special_contribution_purpose" class="form-select" required>
                                <option value="">Select pool</option>
                                @forelse($specialContributionGroups as $group)
                                    @php
                                        $label = $group['label'];
                                        $total = number_format($group['total'], 2);
                                        $remaining = number_format($group['remaining'], 2);
                                        $disabled = $group['remaining'] <= 0;
                                    @endphp
                                    <option value="{{ $label }}" {{ old('special_contribution_purpose') === $label ? 'selected' : '' }} {{ $disabled ? 'disabled' : '' }}>
                                        {{ $label }} - Total GHS {{ $total }} | Remaining GHS {{ $remaining }}
                                    </option>
                                @empty
                                    <option value="" disabled>No special contributions available</option>
                                @endforelse
                            </select>
                            <div class="form-text">Select a purpose to donate from its pooled special contributions.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Donation Purpose (optional)</label>
                            <input type="text" name="donation_purpose" class="form-control" value="{{ old('donation_purpose') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Donated Amount</label>
                            <input type="number" step="0.01" name="donated_amount" class="form-control" value="{{ old('donated_amount') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Donation Date</label>
                            <input type="date" name="donation_date" class="form-control" value="{{ old('donation_date', now()->toDateString()) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remaining Use (e.g. added to balance)</label>
                            <input type="text" name="remaining_use" class="form-control" value="{{ old('remaining_use', 'Added to group balance') }}">
                        </div>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-primary">Save Donation</button>
                        <a href="{{ route('donations.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Donation History</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Special Contribution Pool</th>
                            <th>Donation Purpose</th>
                            <th>Donated</th>
                            <th>Remaining</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($donations as $donation)
                        <tr>
                            <td>{{ $donation->special_contribution_purpose ?? $donation->specialContribution->description ?? '-' }}</td>
                            <td>{{ $donation->donation_purpose ?? '-' }}</td>
                            <td>GHS {{ number_format($donation->donated_amount, 2) }}</td>
                            <td>
                                GHS {{ number_format($donation->remaining_amount, 2) }}
                                @if($donation->remaining_use)
                                    <div class="text-muted small">{{ $donation->remaining_use }}</div>
                                @endif
                            </td>
                            <td>{{ $donation->donation_date?->format('Y-m-d') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">No donations recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $donations->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
