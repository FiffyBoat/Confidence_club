@extends('layouts.app')

@section('content')
<h2 class="mb-3">Add Member</h2>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('members.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Membership ID</label>
                    <input type="text" name="membership_id" class="form-control" value="{{ old('membership_id') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="{{ old('full_name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email (optional)</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Join Date</label>
                    <input type="date" name="join_date" class="form-control" value="{{ old('join_date', now()->toDateString()) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Birth Month (optional)</label>
                    <select name="birth_month" class="form-select">
                        <option value="">Select month</option>
                        @foreach(range(1, 12) as $month)
                            <option value="{{ $month }}" {{ (int) old('birth_month') === $month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(2024, $month, 1)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Birth Day (optional)</label>
                    <input type="number" name="birth_day" class="form-control" min="1" max="31" value="{{ old('birth_day') }}">
                    <div class="form-text">Year is not required. Feb 29 birthdays will show on Feb 28.</div>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="record_admission_fee" name="record_admission_fee" {{ old('record_admission_fee', '1') ? 'checked' : '' }}>
                        <label class="form-check-label" for="record_admission_fee">
                            Record admission fee (GHS 200)
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Admission Payment Method</label>
                    <select name="admission_payment_method" class="form-select">
                        <option value="">Select method</option>
                        @foreach(['cash' => 'Cash', 'momo' => 'Mobile Money', 'bank' => 'Bank', 'card' => 'Card'] as $value => $label)
                        <option value="{{ $value }}" {{ old('admission_payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Admission Payment Date</label>
                    <input type="date" name="admission_transaction_date" class="form-control" value="{{ old('admission_transaction_date', now()->toDateString()) }}">
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Save Member</button>
                <a href="{{ route('members.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
