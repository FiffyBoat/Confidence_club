@extends('layouts.app')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Treasurer Report</div>
        <h2 class="mb-1">Detailed Financial Report</h2>
        <div class="text-muted">From {{ $start->format('Y-m-d') }} to {{ $end->format('Y-m-d') }}</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('reports.financial.print', ['start' => $start->toDateString(), 'end' => $end->toDateString()]) }}" class="btn btn-primary" target="_blank"><i class="bi bi-printer me-1"></i>View / Print</a>
        <a href="{{ route('reports.financial.pdf', ['start' => $start->toDateString(), 'end' => $end->toDateString()]) }}" class="btn btn-outline-primary"><i class="bi bi-download me-1"></i>Download PDF</a>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.financial') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Start Date</label>
                <input type="date" name="start" class="form-control" value="{{ $start->toDateString() }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">End Date</label>
                <input type="date" name="end" class="form-control" value="{{ $end->toDateString() }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Apply</button>
                <a href="{{ route('reports.financial') }}" class="btn btn-outline-secondary">This Month</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Total Contributions</div>
            <div class="stat-value">GHS {{ number_format($summary['contributions'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Total Income</div>
            <div class="stat-value">GHS {{ number_format($summary['income'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Loan Repayments</div>
            <div class="stat-value">GHS {{ number_format($summary['repayments'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Total Expenses</div>
            <div class="stat-value">GHS {{ number_format($summary['expenses'], 2) }}</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Net Position</div>
            <div class="stat-value">GHS {{ number_format($summary['net'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Loans Issued (Period)</div>
            <div class="stat-value">GHS {{ number_format($loanIssuedTotal, 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Outstanding Loans</div>
            <div class="stat-value">GHS {{ number_format($loanOutstanding, 2) }}</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <strong>Contributions by Type</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th class="text-end">Count</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byContributionType as $row)
                        <tr>
                            <td>{{ $row->type }}</td>
                            <td class="text-end">{{ $row->count }}</td>
                            <td class="text-end">GHS {{ number_format($row->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3">No contributions in range.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <strong>Income by Source</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th class="text-end">Count</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byIncomeSource as $row)
                        <tr>
                            <td>{{ $row->source }}</td>
                            <td class="text-end">{{ $row->count }}</td>
                            <td class="text-end">GHS {{ number_format($row->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3">No income in range.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <strong>Expenses by Category</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th class="text-end">Count</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byExpenseCategory as $row)
                        <tr>
                            <td>{{ $row->category }}</td>
                            <td class="text-end">{{ $row->count }}</td>
                            <td class="text-end">GHS {{ number_format($row->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3">No expenses in range.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="accordion" id="financialDetails">
    <div class="accordion-item mb-3">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#contribDetails">
                Contributions ({{ $contributions->count() }})
            </button>
        </h2>
        <div id="contribDetails" class="accordion-collapse collapse show" data-bs-parent="#financialDetails">
            <div class="accordion-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Member</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Method</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contributions as $item)
                            <tr>
                                <td>{{ $item->transaction_date?->format('Y-m-d') }}</td>
                                <td>{{ $item->member->full_name ?? '-' }}</td>
                                <td>{{ $item->type }}</td>
                                <td>{{ $item->description ?? '-' }}</td>
                                <td>{{ strtoupper($item->payment_method) }}</td>
                                <td class="text-end">GHS {{ number_format($item->amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">No contributions in this period.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion-item mb-3">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#incomeDetails">
                Income ({{ $incomes->count() }})
            </button>
        </h2>
        <div id="incomeDetails" class="accordion-collapse collapse" data-bs-parent="#financialDetails">
            <div class="accordion-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Source</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($incomes as $item)
                            <tr>
                                <td>{{ $item->transaction_date?->format('Y-m-d') }}</td>
                                <td>{{ $item->source }}</td>
                                <td>{{ $item->description ?? '-' }}</td>
                                <td class="text-end">GHS {{ number_format($item->amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4">No income in this period.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion-item mb-3">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#expenseDetails">
                Expenses ({{ $expenses->count() }})
            </button>
        </h2>
        <div id="expenseDetails" class="accordion-collapse collapse" data-bs-parent="#financialDetails">
            <div class="accordion-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $item)
                            <tr>
                                <td>{{ $item->transaction_date?->format('Y-m-d') }}</td>
                                <td>{{ $item->category }}</td>
                                <td>{{ $item->description ?? '-' }}</td>
                                <td class="text-end">GHS {{ number_format($item->amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4">No expenses in this period.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion-item mb-3">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#repaymentDetails">
                Loan Repayments ({{ $repayments->count() }})
            </button>
        </h2>
        <div id="repaymentDetails" class="accordion-collapse collapse" data-bs-parent="#financialDetails">
            <div class="accordion-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Member</th>
                                <th>Loan</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($repayments as $item)
                            <tr>
                                <td>{{ $item->payment_date?->format('Y-m-d') }}</td>
                                <td>{{ $item->loan->member->full_name ?? '-' }}</td>
                                <td>Loan #{{ $item->loan_id }}</td>
                                <td class="text-end">GHS {{ number_format($item->amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4">No repayments in this period.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion-item mb-3">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#loanDetails">
                Loans Issued ({{ $loans->count() }})
            </button>
        </h2>
        <div id="loanDetails" class="accordion-collapse collapse" data-bs-parent="#financialDetails">
            <div class="accordion-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Issue Date</th>
                                <th>Member</th>
                                <th>Principal</th>
                                <th>Interest %</th>
                                <th>Total Payable</th>
                                <th>Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loans as $item)
                            <tr>
                                <td>{{ $item->issue_date?->format('Y-m-d') }}</td>
                                <td>{{ $item->member->full_name ?? '-' }}</td>
                                <td>GHS {{ number_format($item->principal, 2) }}</td>
                                <td>{{ number_format($item->interest_rate, 2) }}%</td>
                                <td>GHS {{ number_format($item->total_payable, 2) }}</td>
                                <td>GHS {{ number_format($item->balance, 2) }}</td>
                                <td>{{ ucfirst($item->status) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7">No loans issued in this period.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
