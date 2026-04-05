<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Member Statement</title>
    <style>
        @page { margin: 24px 28px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1b1b1b; }
        .header { margin-bottom: 16px; display: flex; align-items: center; gap: 12px; }
        .title { font-size: 16px; font-weight: 700; }
        .subtitle { font-size: 11px; color: #6b6b6b; margin-top: 4px; }
        .meta { font-size: 9px; color: #6b6b6b; }
        .logo {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: inline-block;
        }
        .logo-fallback {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: inline-block;
            background: #b00020;
            color: #fff;
            text-align: center;
            line-height: 72px;
            font-weight: 700;
            font-size: 18px;
        }
        .summary-grid, .member-grid {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 14px;
        }
        .summary-grid td, .member-grid td {
            padding: 8px;
            border: 1px solid #e6e6e6;
            vertical-align: top;
        }
        .label { color: #6b6b6b; font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; }
        .value { font-weight: 700; font-size: 11px; }
        .section-title { font-size: 12px; font-weight: 700; margin: 16px 0 6px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .data-table th { background: #f7e9ee; text-align: left; padding: 6px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; }
        .data-table td { padding: 6px; border-bottom: 1px solid #ececec; vertical-align: top; }
        .text-right { text-align: right; }
        .muted { color: #6b6b6b; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/ccm-logo.png');
        $logoData = extension_loaded('gd') && file_exists($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : null;
    @endphp

    <div class="header">
        @if($logoData)
            <img src="{{ $logoData }}" alt="Club Logo" class="logo">
        @else
            <div class="logo-fallback">CCM</div>
        @endif
        <div>
            <div class="title">{{ config('app.name', 'CONFIDENCE CLUB MEMBERS') }} - Member Statement</div>
            <div class="subtitle">Payments and unpaid monthly dues as of {{ $asOfDate->format('Y-m-d') }}</div>
            <div class="meta">Generated: {{ $generatedAt?->format('Y-m-d H:i') }}</div>
        </div>
    </div>

    <table class="member-grid">
        <tr>
            <td>
                <div class="label">Member</div>
                <div class="value">{{ $member->full_name }}</div>
            </td>
            <td>
                <div class="label">Membership ID</div>
                <div class="value">{{ $member->membership_id }}</div>
            </td>
            <td>
                <div class="label">Phone</div>
                <div class="value">{{ $member->phone }}</div>
            </td>
            <td>
                <div class="label">Status</div>
                <div class="value">{{ ucfirst($member->status) }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Email</div>
                <div class="value">{{ $member->email ?? '-' }}</div>
            </td>
            <td>
                <div class="label">Join Date</div>
                <div class="value">{{ $member->join_date?->format('Y-m-d') ?? '-' }}</div>
            </td>
            <td>
                <div class="label">Monthly Dues Rate</div>
                <div class="value">GHS {{ number_format($monthlyRate, 2) }}</div>
            </td>
            <td>
                <div class="label">Unpaid Dues Months</div>
                <div class="value">{{ $unpaidDues->count() }}</div>
            </td>
        </tr>
    </table>

    <table class="summary-grid">
        <tr>
            <td>
                <div class="label">Payments Recorded</div>
                <div class="value">{{ $paymentSummary['payment_count'] }}</div>
            </td>
            <td>
                <div class="label">Total Paid</div>
                <div class="value">GHS {{ number_format($paymentSummary['total_paid'], 2) }}</div>
            </td>
            <td>
                <div class="label">Dues Paid To Date</div>
                <div class="value">GHS {{ number_format($duesSummary['paid_to_date'], 2) }}</div>
            </td>
            <td>
                <div class="label">Dues Outstanding</div>
                <div class="value">GHS {{ number_format($duesSummary['outstanding'], 2) }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Payments</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Type</th>
                <th>Description</th>
                <th>Date</th>
                <th class="text-right">Amount (GHS)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
            <tr>
                <td>{{ $payment['type'] }}</td>
                <td>{{ $payment['description'] }}</td>
                <td>{{ $payment['date']?->format('Y-m-d') }}</td>
                <td class="text-right">{{ number_format($payment['amount'], 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4">No payments recorded.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Unpaid Monthly Dues</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Month</th>
                <th class="text-right">Expected</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Outstanding</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($unpaidDues as $dueMonth)
            <tr>
                <td>{{ $dueMonth['month_label'] }}</td>
                <td class="text-right">{{ number_format($dueMonth['expected'], 2) }}</td>
                <td class="text-right">{{ number_format($dueMonth['paid'], 2) }}</td>
                <td class="text-right">{{ number_format($dueMonth['outstanding'], 2) }}</td>
                <td>{{ ucfirst($dueMonth['status']) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5">No unpaid dues as of {{ $asOfDate->format('Y-m-d') }}.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
