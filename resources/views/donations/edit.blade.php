@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Edit Donation</h2>
    <a href="{{ route('donations.index') }}" class="btn btn-outline-secondary">Back</a>
</div>

@php
    $currentPurpose = old('special_contribution_purpose', $donation->special_contribution_purpose ?? 'General');
@endphp

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('donations.update', $donation) }}" method="POST">
            @csrf
            @method('PUT')

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
                                $disabled = $group['remaining'] <= 0 && $label !== $currentPurpose;
                            @endphp
                            <option value="{{ $label }}" {{ $currentPurpose === $label ? 'selected' : '' }} {{ $disabled ? 'disabled' : '' }}>
                                {{ $label }} - Total GHS {{ $total }} | Remaining GHS {{ $remaining }}
                            </option>
                        @empty
                            <option value="" disabled>No special contributions available</option>
                        @endforelse
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Donation Purpose (optional)</label>
                    <input type="text" name="donation_purpose" class="form-control" value="{{ old('donation_purpose', $donation->donation_purpose) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Donated Amount</label>
                    <input type="number" step="0.01" name="donated_amount" class="form-control" value="{{ old('donated_amount', $donation->donated_amount) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Donation Date</label>
                    <input type="date" name="donation_date" class="form-control" value="{{ old('donation_date', $donation->donation_date?->format('Y-m-d')) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Remaining Use</label>
                    <input type="text" name="remaining_use" class="form-control" value="{{ old('remaining_use', $donation->remaining_use) }}">
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary">Update Donation</button>
                <a href="{{ route('donations.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
