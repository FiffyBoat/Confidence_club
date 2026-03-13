<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Receipt' }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #1d1d1d; font-size: 12px; }
        .watermark {
            position: fixed;
            top: 40%;
            left: 10%;
            font-size: 120px;
            color: rgba(176, 0, 32, 0.08);
            transform: rotate(-28deg);
            font-weight: 700;
            letter-spacing: 10px;
            z-index: 0;
        }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .header-table td { vertical-align: middle; }
        .logo {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #b00020;
            color: #fff;
            text-align: center;
            line-height: 64px;
            font-weight: 700;
            font-size: 18px;
        }
        .club-name { font-size: 20px; font-weight: 700; color: #1c1c1c; }
        .club-tagline { font-size: 11px; color: #6b6b6b; text-transform: uppercase; letter-spacing: 0.12em; margin-top: 4px; }
        .receipt-box {
            border: 1px solid #e8d7dc;
            border-radius: 8px;
            padding: 10px 12px;
            text-align: right;
        }
        .receipt-box .label { font-size: 10px; color: #6b6b6b; text-transform: uppercase; letter-spacing: 0.08em; }
        .receipt-box .value { font-size: 14px; font-weight: 700; margin-top: 4px; }
        .paid-pill {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 10px;
            border-radius: 999px;
            background: #b00020;
            color: #fff;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .section-title { font-size: 12px; font-weight: 700; margin: 14px 0 8px; }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .details-table td {
            padding: 6px 0;
            vertical-align: top;
        }
        .label { color: #6b6b6b; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; }
        .value { font-weight: 600; }
        .line-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .line-items th {
            text-align: left;
            background: #fff2f5;
            padding: 8px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            border-bottom: 1px solid #e8d7dc;
        }
        .line-items td {
            padding: 10px 8px;
            border-bottom: 1px solid #f0e0e4;
        }
        .amount { text-align: right; font-weight: 600; }
        .total-row {
            width: 100%;
            margin-top: 12px;
            border-top: 2px solid #b00020;
            padding-top: 10px;
            text-align: right;
            font-size: 14px;
            font-weight: 700;
        }
        .footer {
            margin-top: 18px;
            font-size: 10px;
            color: #6b6b6b;
            text-align: center;
        }
    </style>
</head>
<body>
@php
    $clubName = config('app.name', 'Confidence Club Members');
    $clubTagline = 'Official Receipt';
    $receiptDate = $transactionDate?->format('Y-m-d') ?? now()->format('Y-m-d');
    $paymentTypeLabel = $paymentType ?? $reference ?? ($title ?? 'Payment');
    $paymentDescription = $description ?? $reference ?? $paymentTypeLabel;
    $memberName = $member?->full_name ?? 'General Income';
    $membershipId = $member?->membership_id;
    $paymentMethodLabel = $paymentMethod ? strtoupper($paymentMethod) : '-';
    $recordedByLabel = $recordedBy ?? '-';
    $logoPath = public_path('images/ccm-logo.png');
    $logoData = file_exists($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : null;
@endphp

<div class="watermark">PAID</div>

<table class="header-table">
    <tr>
        <td style="width: 80px;">
            @if($logoData)
                <img src="{{ $logoData }}" alt="Club Logo" style="width:64px;height:64px;border-radius:50%;object-fit:cover;">
            @else
                <div class="logo">CCM</div>
            @endif
        </td>
        <td>
            <div class="club-name">{{ $clubName }}</div>
            <div class="club-tagline">{{ $clubTagline }}</div>
        </td>
        <td style="width: 210px;">
            <div class="receipt-box">
                <div class="label">Receipt #</div>
                <div class="value">{{ $receiptNumber }}</div>
                <div class="label" style="margin-top:6px;">Date</div>
                <div class="value">{{ $receiptDate }}</div>
                <span class="paid-pill">Paid</span>
            </div>
        </td>
    </tr>
</table>

<div class="section-title">Payment Details</div>
<table class="details-table">
    <tr>
        <td style="width: 50%;">
            <div class="label">Member</div>
            <div class="value">{{ $memberName }}</div>
        </td>
        <td style="width: 50%;">
            <div class="label">Payment Type</div>
            <div class="value">{{ $paymentTypeLabel }}</div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="label">Membership ID</div>
            <div class="value">{{ $membershipId ?? '-' }}</div>
        </td>
        <td>
            <div class="label">Payment Method</div>
            <div class="value">{{ $paymentMethodLabel }}</div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="label">Reference</div>
            <div class="value">{{ $reference }}</div>
        </td>
        <td>
            <div class="label">Recorded By</div>
            <div class="value">{{ $recordedByLabel }}</div>
        </td>
    </tr>
</table>

<table class="line-items">
    <thead>
        <tr>
            <th>Description</th>
            <th style="text-align:right;">Amount (GHS)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $paymentDescription }}</td>
            <td class="amount">{{ number_format($amount, 2) }}</td>
        </tr>
    </tbody>
</table>

<div class="total-row">Total Paid: GHS {{ number_format($amount, 2) }}</div>

<div class="footer">
    Thank you for your payment. This receipt was generated by the CCM system and is valid without signature.
</div>
</body>
</html>
