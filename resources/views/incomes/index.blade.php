@extends('layouts.app')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Finance</div>
        <h2 class="mb-1">Income</h2>
        <div class="text-muted">Track additional revenue sources.</div>
    </div>
    <a href="{{ route('incomes.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Record Income</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('incomes.index') }}" method="GET" class="row g-2 mb-3">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Search by source" value="{{ $search ?? '' }}">
                    @if(!empty($search))
                        <a href="{{ route('incomes.index') }}" class="btn btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Source</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incomes as $income)
                    <tr>
                        <td>{{ $income->id }}</td>
                        <td>{{ $income->source }}</td>
                        <td>GHS {{ number_format($income->amount, 2) }}</td>
                        <td>{{ $income->transaction_date?->format('Y-m-d') }}</td>
                        <td class="text-end">
                            <a href="{{ route('incomes.edit', $income) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                            <a href="{{ route('incomes.show', $income) }}" class="btn btn-info btn-sm">View</a>
                            <form action="{{ route('incomes.destroy', $income) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Delete income record?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">No income recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $incomes->links('pagination::bootstrap-5') }}
</div>
@endsection
