@extends('layouts.app')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Finance</div>
        <h2 class="mb-1">Contributions</h2>
        <div class="text-muted">Record admissions, dues, and special contributions.</div>
    </div>
    <a href="{{ route('contributions.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Record Contribution</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('contributions.index') }}" method="GET" class="row g-2 mb-3">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Search by member or type" value="{{ $search ?? '' }}">
                    @if(!empty($search))
                        <a href="{{ route('contributions.index') }}" class="btn btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary">Search</button>
            </div>
        </form>

        @if(!empty($search))
        <div class="small text-muted mb-3">
            Showing {{ $contributions->count() }} of {{ $contributions->total() }} result(s) for <strong>{{ $search }}</strong>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Member</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contributions as $contribution)
                    <tr>
                        <td>{{ $contribution->id }}</td>
                        <td>{{ $contribution->member->full_name ?? 'Unknown' }}</td>
                        <td><span class="badge text-bg-secondary">{{ $contribution->type }}</span></td>
                        <td>{{ $contribution->description ?? '-' }}</td>
                        <td>GHS {{ number_format($contribution->amount, 2) }}</td>
                        <td>{{ $contribution->transaction_date?->format('Y-m-d') }}</td>
                        <td class="text-end">
                            @if($contribution->receipt)
                            <a href="{{ route('receipts.download', $contribution->receipt) }}" class="btn btn-outline-primary btn-sm">Receipt</a>
                            @endif
                            <a href="{{ route('contributions.show', $contribution) }}" class="btn btn-info btn-sm">View</a>
                            <form action="{{ route('contributions.destroy', $contribution) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Delete contribution?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">No contributions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $contributions->links('pagination::bootstrap-5') }}
</div>
@endsection
