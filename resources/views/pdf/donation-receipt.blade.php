<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Donation Receipt</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #2c3e50; font-size: 12px; }
        .header { border-bottom: 3px solid #28a745; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { color: #28a745; margin: 0; font-size: 22px; }
        .header p { margin: 4px 0 0; color: #6c757d; font-size: 11px; }
        h2 { color: #1a6fa8; font-size: 14px; border-bottom: 1px solid #e0e0e0; padding-bottom: 4px; margin-top: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        td { padding: 6px; vertical-align: top; }
        .label { background: #f0f7fc; color: #6c757d; width: 35%; font-weight: bold; }
        .amount-box { background: #e8f5e9; border-left: 5px solid #28a745; padding: 16px; margin: 20px 0; text-align: center; }
        .amount-box .value { font-size: 28px; font-weight: bold; color: #28a745; }
        .amount-box .label { background: transparent; color: #6c757d; font-size: 12px; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 10px; color: #6c757d; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Donation Receipt</h1>
        <p>e-Tawassul &middot; International Islamic University Malaysia</p>
        <p>Generated {{ $generatedAt->format('d M Y, h:i A') }}</p>
    </div>

    <div class="amount-box">
        <div class="label">Donation Amount</div>
        <div class="value">RM {{ number_format($donation->donation_amount, 2) }}</div>
    </div>

    <h2>Receipt Details</h2>
    <table>
        <tr><td class="label">Receipt ID</td><td>#{{ $donation->donation_id }}</td></tr>
        <tr><td class="label">Date</td><td>{{ $donation->donation_date?->format('d M Y, h:i A') }}</td></tr>
        <tr><td class="label">Payment Method</td><td>{{ strtoupper($donation->payment_method) }}</td></tr>
    </table>

    <h2>Donor</h2>
    <table>
        <tr><td class="label">Name</td><td>{{ $donation->donor_name }}</td></tr>
        <tr><td class="label">Email</td><td>{{ $donation->donor_email }}</td></tr>
    </table>

    @if($donation->crisis)
        <h2>Beneficiary Case</h2>
        <table>
            <tr><td class="label">Case ID</td><td>#{{ $donation->crisis->crisis_id }}</td></tr>
            <tr><td class="label">Type</td><td>{{ ucwords(str_replace('_',' ', $donation->crisis->crisis_type)) }}</td></tr>
            <tr><td class="label">Location</td><td>{{ $donation->crisis->location ?? '—' }}</td></tr>
        </table>
    @endif

    @if($donation->support_message)
        <h2>Donor's Message</h2>
        <p style="font-style: italic; background: #f8f9fa; padding: 10px;">"{{ $donation->support_message }}"</p>
    @endif

    @if($donation->blockchain_hash)
        <h2>Blockchain Verification</h2>
        <table>
            <tr><td class="label">Hash (SHA-256)</td><td style="font-family: monospace; word-break: break-all;">{{ $donation->blockchain_hash }}</td></tr>
        </table>
        <p style="font-size: 10px; color: #6c757d; margin-top: 6px;">
            This donation has been recorded on the e-Tawassul permissioned audit chain.
            The hash above is the cryptographic fingerprint of the transaction.
        </p>
    @endif

    <div class="footer">
        Thank you for your kindness. May Allah accept your sadaqah.<br>
        e-Tawassul &middot; Student Welfare Initiative &middot; International Islamic University Malaysia
    </div>
</body>
</html>
