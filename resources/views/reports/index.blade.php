@extends('layouts.app')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Analytics</div>
        <h2 class="mb-1">Reports</h2>
        <div class="text-muted">Monthly summaries and performance snapshots.</div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Total Loans</div>
            <div class="stat-value">{{ $loanSummary['total_loans'] }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Outstanding Balance</div>
            <div class="stat-value">GHS {{ number_format($loanSummary['total_outstanding'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Overdue Loans</div>
            <div class="stat-value">{{ $loanSummary['overdue'] }}</div>
        </div>
    </div>
</div>

@if(in_array(auth()->user()->role ?? 'viewer', ['admin', 'treasurer']))
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
        <div>
            <div class="text-muted small">Treasurer</div>
            <h5 class="mb-1">Detailed Financial Report</h5>
            <div class="text-muted">Full breakdowns by type, source, category, and transaction lists.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.financial') }}" class="btn btn-primary"><i class="bi bi-clipboard-data me-1"></i>Open Report</a>
        </div>
    </div>
</div>
@endif


<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Monthly Snapshot (Last 6)</strong>
        <div class="small text-muted">
            <span class="me-2"><span class="badge text-bg-primary">C</span> Contributions</span>
            <span class="me-2"><span class="badge text-bg-success">I</span> Income</span>
            <span><span class="badge text-bg-danger">E</span> Expenses</span>
        </div>
    </div>
    <div class="card-body">
        @php
            $maxValue = $chartMax > 0 ? $chartMax : 1;
        @endphp
        @forelse($monthlyChart as $item)
            <div class="mb-3">
                <div class="small text-muted mb-1">{{ $item['label'] }}</div>
                <div class="d-flex align-items-center gap-3">
                    <div class="flex-grow-1">
                        <div class="bg-light rounded" style="height: 8px;">
                            <div class="bg-primary rounded" style="height: 8px; width: {{ round(($item['contrib'] / $maxValue) * 100, 2) }}%;"></div>
                        </div>
                        <div class="bg-light rounded mt-1" style="height: 8px;">
                            <div class="bg-success rounded" style="height: 8px; width: {{ round(($item['income'] / $maxValue) * 100, 2) }}%;"></div>
                        </div>
                        <div class="bg-light rounded mt-1" style="height: 8px;">
                            <div class="bg-danger rounded" style="height: 8px; width: {{ round(($item['expenses'] / $maxValue) * 100, 2) }}%;"></div>
                        </div>
                    </div>
                    <div class="text-end small">
                        <div class="text-primary">C {{ number_format($item['contrib'], 0) }}</div>
                        <div class="text-success">I {{ number_format($item['income'], 0) }}</div>
                        <div class="text-danger">E {{ number_format($item['expenses'], 0) }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-muted">No monthly data available.</div>
        @endforelse
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Monthly Contributions</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyContributions as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::create($row->year, $row->month, 1)->format('M Y') }}</td>
                            <td>GHS {{ number_format($row->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2">No data available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Monthly Income</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyIncome as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::create($row->year, $row->month, 1)->format('M Y') }}</td>
                            <td>GHS {{ number_format($row->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2">No data available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Monthly Expenses</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyExpenses as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::create($row->year, $row->month, 1)->format('M Y') }}</td>
                            <td>GHS {{ number_format($row->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2">No data available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
