<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CCM Import Summary</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #222;
        }
        h1 {
            font-size: 18px;
            margin: 0 0 8px 0;
        }
        h2 {
            font-size: 14px;
            margin: 16px 0 6px 0;
        }
        .muted {
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background: #f2f2f2;
        }
        .right {
            text-align: right;
        }
    </style>
</head>
<body>
    <h1>CCM Import Summary</h1>
    <div class="muted">Generated: {{ $generatedAt->format('Y-m-d H:i') }}</div>

    <h2>Live Totals</h2>
    <table>
        <tr>
            <th>Metric</th>
            <th class="right">Value</th>
        </tr>
        <tr>
            <td>Members</td>
            <td class="right">{{ number_format($summary['members']) }}</td>
        </tr>
        <tr>
            <td>Contributions</td>
            <td class="right">{{ number_format($summary['contributions']) }}</td>
        </tr>
        <tr>
            <td>Contributions Total (GHS)</td>
            <td class="right">{{ number_format($summary['contributions_total'], 2) }}</td>
        </tr>
    </table>

    <h2>Contributions by Type</h2>
    <table>
        <tr>
            <th>Type</th>
            <th class="right">Count</th>
            <th class="right">Total (GHS)</th>
        </tr>
        @foreach($summary['by_type'] as $row)
        <tr>
            <td>{{ $row->type }}</td>
            <td class="right">{{ number_format($row->cnt) }}</td>
            <td class="right">{{ number_format((float) $row->total, 2) }}</td>
        </tr>
        @endforeach
    </table>

    <h2>Expected Totals (Staging)</h2>
    <table>
        <tr>
            <th>Label</th>
            <th class="right">Value</th>
        </tr>
        <tr>
            <td>Admission Fee (GHS)</td>
            <td class="right">{{ number_format($summary['expected']['Admission Fee'], 2) }}</td>
        </tr>
        <tr>
            <td>Professor Donation (GHS)</td>
            <td class="right">{{ number_format($summary['expected']['Professor Donation'], 2) }}</td>
        </tr>
        <tr>
            <td>Lawyer Donation (GHS)</td>
            <td class="right">{{ number_format($summary['expected']['Lawyer Donation'], 2) }}</td>
        </tr>
        <tr>
            <td>Extra Levies (GHS)</td>
            <td class="right">{{ number_format($summary['expected']['Extra Levies'], 2) }}</td>
        </tr>
        <tr>
            <td>Monthly Dues (GHS)</td>
            <td class="right">{{ number_format($summary['expected']['Monthly Dues'], 2) }}</td>
        </tr>
        <tr>
            <td>Monthly Dues Count</td>
            <td class="right">{{ number_format($summary['expected']['Monthly Dues Count']) }}</td>
        </tr>
        <tr>
            <td>Expected Total (GHS)</td>
            <td class="right">{{ number_format($summary['expected_total'], 2) }}</td>
        </tr>
        <tr>
            <td>Delta (Live - Expected)</td>
            <td class="right">{{ number_format($summary['delta'], 2) }}</td>
        </tr>
    </table>
</body>
</html>
