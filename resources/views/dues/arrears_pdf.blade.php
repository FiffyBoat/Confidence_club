<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Dues Arrears List</title>
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
        .summary-grid { width: 100%; border-collapse: collapse; margin: 10px 0 14px; }
        .summary-grid td { padding: 8px; border: 1px solid #e6e6e6; }
        .label { color: #6b6b6b; font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; }
        .value { font-weight: 700; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th { background: #f7e9ee; text-align: left; padding: 6px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; }
        td { padding: 6px; border-bottom: 1px solid #ececec; vertical-align: top; }
        .text-right { text-align: right; }
        .status-none { color: #b00020; font-weight: 700; }
        .status-partial { color: #9c6b00; font-weight: 700; }
        .muted { color: #6b6b6b; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/ccm-logo.png');
        $logoData = extension_loaded('gd') && file_exists($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : null;
        $arrearsCount = $outstandingRows->count();
        $noPaymentCount = $outstandingRows->filter(fn ($row) => $row['payment_status'] === 'none')->count();
        $totalOutstanding = $outstandingRows->sum('balance_end');
    @endphp

    <div class="header">
        @if($logoData)
            <img src="{{ $logoData }}" alt="Club Logo" class="logo">
        @else
            <div class="logo-fallback">CCM</div>
        @endif
        <div>
            <div class="title">{{ config('app.name', 'CONFIDENCE CLUB MEMBERS') }} - Dues Arrears</div>
            <div class="subtitle">Outstanding dues as of {{ $monthsList[$asOfMonth] }} {{ $year }}</div>
            <div class="meta">Generated: {{ $generatedAt?->format('Y-m-d H:i') }}</div>
        </div>
    </div>

    <table class="summary-grid">
        <tr>
            <td>
                <div class="label">Members Owing</div>
                <div class="value">{{ $arrearsCount }}</div>
            </td>
            <td>
                <div class="label">No Dues Paid Yet</div>
                <div class="value">{{ $noPaymentCount }}</div>
            </td>
            <td>
                <div class="label">Outstanding Balance</div>
                <div class="value">GHS {{ number_format($totalOutstanding, 2) }}</div>
            </td>
            <td>
                <div class="label">Monthly Rate</div>
                <div class="value">GHS {{ number_format($monthlyRate, 2) }}</div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Member</th>
                <th>Status</th>
                <th class="text-right">Months Due</th>
                <th class="text-right">Paid</th>
                <th>Unpaid Months</th>
                <th class="text-right">Amount Owed</th>
            </tr>
        </thead>
        <tbody>
            @forelse($outstandingRows as $row)
            <tr>
                <td>
                    <strong>{{ $row['member']->full_name }}</strong><br>
                    <span class="muted">{{ $row['member']->membership_id }}</span>
                </td>
                <td>
                    @if($row['payment_status'] === 'none')
                        <span class="status-none">No dues paid</span>
                    @else
                        <span class="status-partial">Partly paid</span>
                    @endif
                </td>
                <td class="text-right">{{ $row['months_due'] }}</td>
                <td class="text-right">GHS {{ number_format($row['paid_to_as_of'], 2) }}</td>
                <td>
                    {{ collect($row['arrears_months'])->pluck('label')->implode(', ') ?: 'None' }}
                </td>
                <td class="text-right"><strong>GHS {{ number_format($row['balance_end'], 2) }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="6">No members are owing dues for this period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
