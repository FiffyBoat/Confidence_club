@extends('layouts.app')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Finance</div>
        <h2 class="mb-1">Expenses</h2>
        <div class="text-muted">Track outgoing payments and spending for {{ $year }}.</div>
    </div>
    <a href="{{ route('expenses.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Record Expense</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Total Expenses ({{ $year }})</div>
            <div class="stat-value">GHS {{ number_format($totalExpenses, 2) }}</div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('expenses.index') }}" method="GET" class="row g-2 mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Search by category" value="{{ $search ?? '' }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="year" class="form-select">
                    @foreach($availableYears as $availableYear)
                        <option value="{{ $availableYear }}" {{ (int) $year === (int) $availableYear ? 'selected' : '' }}>{{ $availableYear }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button class="btn btn-primary">Search</button>
            </div>
            @if(!empty($search))
                <div class="col-12">
                    <a href="{{ route('expenses.index', ['year' => $year]) }}" class="btn btn-outline-secondary btn-sm">Clear Search</a>
                </div>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                    <tr>
                        <td>{{ $expense->id }}</td>
                        <td>{{ $expense->category }}</td>
                        <td>GHS {{ number_format($expense->amount, 2) }}</td>
                        <td>{{ $expense->transaction_date?->format('Y-m-d') }}</td>
                        <td class="text-end">
                            <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                            <a href="{{ route('expenses.show', $expense) }}" class="btn btn-info btn-sm">View</a>
                            <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Delete expense record?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">No expenses recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $expenses->links('pagination::bootstrap-5') }}
</div>
@endsection
