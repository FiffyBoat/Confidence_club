@extends('layouts.app')

@section('content')
<h2 class="mb-3">Issue Loan</h2>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('loans.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Member</label>
                    <select name="member_id" class="form-select" required>
                        <option value="">Select member</option>
                        @foreach($members as $member)
                        <option value="{{ $member->id }}" {{ (int) old('member_id') === $member->id ? 'selected' : '' }}>
                            {{ $member->membership_id }} - {{ $member->full_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Principal</label>
                    <input type="number" step="0.01" name="principal" class="form-control" value="{{ old('principal') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Interest Rate (%)</label>
                    <input type="number" step="0.01" name="interest_rate" class="form-control" value="{{ old('interest_rate', 0) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Issue Date</label>
                    <input type="date" name="issue_date" class="form-control" value="{{ old('issue_date', now()->toDateString()) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}" required>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Issue Loan</button>
                <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
