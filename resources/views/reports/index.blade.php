@extends('layouts.app')

@section('content')
<style>
    .reports-bar-chart {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(108px, 1fr));
        gap: 1rem;
        align-items: end;
        min-height: 320px;
    }

    .reports-bar-group {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .reports-bar-canvas {
        position: relative;
        width: 100%;
        height: 220px;
        padding: 1rem 0.5rem 2rem;
        border-radius: 18px;
        background:
            linear-gradient(to top, rgba(176, 0, 32, 0.04), rgba(176, 0, 32, 0.01)),
            repeating-linear-gradient(
                to top,
                rgba(31, 31, 31, 0.08) 0,
                rgba(31, 31, 31, 0.08) 1px,
                transparent 1px,
                transparent 25%
            );
        border: 1px solid rgba(176, 0, 32, 0.08);
        display: flex;
        align-items: end;
        justify-content: center;
    }

    .reports-bar-stack {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: end;
        justify-content: center;
        gap: 0.4rem;
    }

    .reports-bar {
        width: 24px;
        min-height: 6px;
        border-radius: 999px 999px 8px 8px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
    }

    .reports-bar-contrib { background: linear-gradient(180deg, #6aa4ff 0%, #0d6efd 100%); }
    .reports-bar-income { background: linear-gradient(180deg, #7ce2b8 0%, #198754 100%); }
    .reports-bar-expenses { background: linear-gradient(180deg, #ff8f8f 0%, #dc3545 100%); }

    .reports-bar-axis {
        position: absolute;
        left: 12px;
        right: 12px;
        bottom: 14px;
        border-top: 1px solid rgba(31, 31, 31, 0.16);
    }

    .reports-bar-label {
        text-align: center;
        font-weight: 600;
        color: #5b5b5b;
        font-size: 0.84rem;
    }

    .reports-bar-values {
        width: 100%;
        display: grid;
        gap: 0.25rem;
        font-size: 0.8rem;
        text-align: center;
    }

    .reports-bar-values .contrib { color: #0d6efd; }
    .reports-bar-values .income { color: #198754; }
    .reports-bar-values .expenses { color: #dc3545; }
</style>

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
            @if($loop->first)
                <div class="reports-bar-chart">
            @endif
            <div class="reports-bar-group">
                <div class="reports-bar-canvas">
                    <div class="reports-bar-stack">
                        <div
                            class="reports-bar reports-bar-contrib"
                            style="height: {{ max(6, round(($item['contrib'] / $maxValue) * 160, 2)) }}px;"
                            title="Contributions: GHS {{ number_format($item['contrib'], 2) }}"
                        ></div>
                        <div
                            class="reports-bar reports-bar-income"
                            style="height: {{ max(6, round(($item['income'] / $maxValue) * 160, 2)) }}px;"
                            title="Income: GHS {{ number_format($item['income'], 2) }}"
                        ></div>
                        <div
                            class="reports-bar reports-bar-expenses"
                            style="height: {{ max(6, round(($item['expenses'] / $maxValue) * 160, 2)) }}px;"
                            title="Expenses: GHS {{ number_format($item['expenses'], 2) }}"
                        ></div>
                    </div>
                    <div class="reports-bar-axis"></div>
                </div>
                <div class="reports-bar-label">{{ $item['label'] }}</div>
                <div class="reports-bar-values">
                    <div class="contrib">C {{ number_format($item['contrib'], 0) }}</div>
                    <div class="income">I {{ number_format($item['income'], 0) }}</div>
                    <div class="expenses">E {{ number_format($item['expenses'], 0) }}</div>
                </div>
            </div>
            @if($loop->last)
                </div>
            @endif
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
