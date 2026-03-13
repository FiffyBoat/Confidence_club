@extends('layouts.app')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Public Summary</div>
        <h2 class="mb-1">Transparency Portal</h2>
        <div class="text-muted">Read-only totals and monthly snapshots.</div>
    </div>
    <span class="text-muted">{{ now()->format('D, d M Y') }}</span>
</div>

@if(! $showAny)
    <div class="alert alert-info">
        The administrator has not enabled public financial details yet.
    </div>
@endif

@if($showAny)
<div class="row g-3 mb-4">
    @if($visibility['total_members'])
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Total Members</div>
            <div class="stat-value">{{ $totalMembers }}</div>
        </div>
    </div>
    @endif
    @if($visibility['total_contributions'])
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Total Contributions</div>
            <div class="stat-value">GHS {{ number_format($totalContributions, 2) }}</div>
        </div>
    </div>
    @endif
    @if($visibility['total_income'])
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Total Income</div>
            <div class="stat-value">GHS {{ number_format($totalIncome, 2) }}</div>
        </div>
    </div>
    @endif
    @if($visibility['net_balance'])
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Net Balance</div>
            <div class="stat-value">GHS {{ number_format($netBalance, 2) }}</div>
        </div>
    </div>
    @endif
</div>

<div class="row g-3 mb-4">
    @if($visibility['total_repayments'])
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Total Repayments</div>
            <div class="stat-value">GHS {{ number_format($totalRepayments, 2) }}</div>
        </div>
    </div>
    @endif
    @if($visibility['total_expenses'])
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Total Expenses</div>
            <div class="stat-value">GHS {{ number_format($totalExpenses, 2) }}</div>
        </div>
    </div>
    @endif
    @if($visibility['outstanding_loans'])
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Outstanding Loans</div>
            <div class="stat-value">GHS {{ number_format($loanSummary['total_outstanding'], 2) }}</div>
        </div>
    </div>
    @endif
</div>
@endif

@if($visibility['monthly_contributions'] || $visibility['monthly_expenses'])
<div class="row g-3">
    @if($visibility['monthly_contributions'])
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Monthly Contributions (Last 6 Months)</strong>
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
    @endif
    @if($visibility['monthly_expenses'])
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Monthly Expenses (Last 6 Months)</strong>
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
    @endif
</div>
@endif

@if($visibility['expense_breakdown'] || $visibility['loan_summary'])
<div class="row g-3 mt-3">
    @if($visibility['expense_breakdown'])
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Expense Breakdown</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenseBreakdown as $expense)
                        <tr>
                            <td>{{ $expense->category }}</td>
                            <td>GHS {{ number_format($expense->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2">No expense data.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    @if($visibility['loan_summary'])
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <strong>Loan Summary</strong>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Total Loans Issued</div>
                        <div class="fw-semibold">{{ $loanSummary['total_loans'] }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Overdue Loans</div>
                        <div class="fw-semibold">{{ $loanSummary['overdue'] }}</div>
                    </div>
                </div>
                <div class="mt-3 text-muted small">
                    This portal shows aggregate totals only. Individual member data is hidden by design.
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endif
@endsection
