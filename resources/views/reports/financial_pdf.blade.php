<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Financial Report</title>
    <style>
        @page { margin: 24px 28px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1b1b1b; }
        .header { margin-bottom: 16px; }
        .title { font-size: 16px; font-weight: 700; }
        .subtitle { font-size: 11px; color: #6b6b6b; margin-top: 4px; }
        .section-title { font-size: 12px; font-weight: 700; margin: 14px 0 6px; }
        .summary-grid { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .summary-grid td { padding: 6px; border: 1px solid #e6e6e6; }
        .label { color: #6b6b6b; font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; }
        .value { font-weight: 700; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th { background: #f7e9ee; text-align: left; padding: 6px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; }
        td { padding: 6px; border-bottom: 1px solid #ececec; }
        .text-right { text-align: right; }
        .page-break { page-break-before: always; }
        .meta { font-size: 9px; color: #6b6b6b; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ config('app.name', 'CONFIDENCE CLUB MEMBERS') }} - Detailed Financial Report</div>
        <div class="subtitle">Period: {{ $start->format('Y-m-d') }} to {{ $end->format('Y-m-d') }}</div>
        <div class="meta">Generated: {{ $generatedAt?->format('Y-m-d H:i') }}</div>
    </div>

    <div class="section-title">Summary</div>
    <table class="summary-grid">
        <tr>
            <td>
                <div class="label">Contributions</div>
                <div class="value">GHS {{ number_format($summary['contributions'], 2) }}</div>
            </td>
            <td>
                <div class="label">Income</div>
                <div class="value">GHS {{ number_format($summary['income'], 2) }}</div>
            </td>
            <td>
                <div class="label">Repayments</div>
                <div class="value">GHS {{ number_format($summary['repayments'], 2) }}</div>
            </td>
            <td>
                <div class="label">Expenses</div>
                <div class="value">GHS {{ number_format($summary['expenses'], 2) }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Net Position</div>
                <div class="value">GHS {{ number_format($summary['net'], 2) }}</div>
            </td>
            <td>
                <div class="label">Loans Issued (Period)</div>
                <div class="value">GHS {{ number_format($loanIssuedTotal, 2) }}</div>
            </td>
            <td>
                <div class="label">Outstanding Loans</div>
                <div class="value">GHS {{ number_format($loanOutstanding, 2) }}</div>
            </td>
            <td>
                <div class="label">Report Rows</div>
                <div class="value">{{ $contributions->count() + $incomes->count() + $expenses->count() + $repayments->count() }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Contributions by Type</div>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th class="text-right">Count</th>
                <th class="text-right">Total (GHS)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($byContributionType as $row)
            <tr>
                <td>{{ $row->type }}</td>
                <td class="text-right">{{ $row->count }}</td>
                <td class="text-right">{{ number_format($row->total, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3">No contributions in this period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Income by Source</div>
    <table>
        <thead>
            <tr>
                <th>Source</th>
                <th class="text-right">Count</th>
                <th class="text-right">Total (GHS)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($byIncomeSource as $row)
            <tr>
                <td>{{ $row->source }}</td>
                <td class="text-right">{{ $row->count }}</td>
                <td class="text-right">{{ number_format($row->total, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3">No income in this period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Expenses by Category</div>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th class="text-right">Count</th>
                <th class="text-right">Total (GHS)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($byExpenseCategory as $row)
            <tr>
                <td>{{ $row->category }}</td>
                <td class="text-right">{{ $row->count }}</td>
                <td class="text-right">{{ number_format($row->total, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3">No expenses in this period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>
    <div class="section-title">Contributions (Detailed)</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Member</th>
                <th>Type</th>
                <th>Description</th>
                <th>Method</th>
                <th class="text-right">Amount (GHS)</th>
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
                <td class="text-right">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6">No contributions in this period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>
    <div class="section-title">Income (Detailed)</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Source</th>
                <th>Description</th>
                <th class="text-right">Amount (GHS)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incomes as $item)
            <tr>
                <td>{{ $item->transaction_date?->format('Y-m-d') }}</td>
                <td>{{ $item->source }}</td>
                <td>{{ $item->description ?? '-' }}</td>
                <td class="text-right">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4">No income in this period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>
    <div class="section-title">Expenses (Detailed)</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Description</th>
                <th class="text-right">Amount (GHS)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $item)
            <tr>
                <td>{{ $item->transaction_date?->format('Y-m-d') }}</td>
                <td>{{ $item->category }}</td>
                <td>{{ $item->description ?? '-' }}</td>
                <td class="text-right">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4">No expenses in this period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>
    <div class="section-title">Loan Repayments (Detailed)</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Member</th>
                <th>Loan</th>
                <th class="text-right">Amount (GHS)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($repayments as $item)
            <tr>
                <td>{{ $item->payment_date?->format('Y-m-d') }}</td>
                <td>{{ $item->loan->member->full_name ?? '-' }}</td>
                <td>Loan #{{ $item->loan_id }}</td>
                <td class="text-right">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4">No repayments in this period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>
    <div class="section-title">Loans Issued (Detailed)</div>
    <table>
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
</body>
</html>
