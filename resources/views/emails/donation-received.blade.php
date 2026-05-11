@extends('emails.layout')
@section('title', 'Thank you for your donation')
@section('content')
    <h2 style="color:#28a745; margin-top:0;">Thank you for your generosity</h2>
    <p>Dear {{ e($donation->donor_name) }},</p>
    <p>Your contribution has been received and securely recorded. Your kindness directly supports an IIUM student in need.</p>

    <table cellpadding="10" cellspacing="0" style="background:#f8f9fa; width:100%; border-radius:6px; margin:20px 0;">
        <tr><td style="color:#6c757d; width:40%;">Receipt ID</td><td><strong>#{{ $donation->donation_id }}</strong></td></tr>
        <tr><td style="color:#6c757d;">Date</td><td>{{ $donation->donation_date?->format('d M Y, h:i A') }}</td></tr>
        <tr><td style="color:#6c757d;">Amount</td><td><strong style="color:#28a745; font-size:18px;">RM {{ number_format($donation->donation_amount, 2) }}</strong></td></tr>
        <tr><td style="color:#6c757d;">Payment method</td><td>{{ strtoupper($donation->payment_method) }}</td></tr>
        @if($donation->crisis_id)
            <tr><td style="color:#6c757d;">Case</td><td>Crisis #{{ $donation->crisis_id }}</td></tr>
        @endif
    </table>

    @if($donation->blockchain_hash)
        <div style="background:#f0f7fc; border-left:4px solid #1a6fa8; padding:14px; margin:20px 0;">
            <p style="margin:0 0 6px; font-weight:600; color:#1a6fa8;">🔒 Blockchain Receipt</p>
            <p style="margin:0; font-family: 'Courier New', monospace; font-size:11px; word-break:break-all; color:#495057;">{{ $donation->blockchain_hash }}</p>
            <p style="margin:8px 0 0; font-size:11px; color:#6c757d;">Your donation is permanently recorded on the audit chain for full transparency.</p>
        </div>
    @endif

    <p style="text-align:center; margin:30px 0;">
        <a href="{{ route('pdf.donation', $donation->donation_id) }}"
           style="background:#1a6fa8; color:#fff; padding:10px 24px; text-decoration:none; border-radius:6px; font-weight:600; display:inline-block;">
            Download Receipt (PDF)
        </a>
    </p>

    <p style="font-size:13px; color:#6c757d;">"The believer's shade on the Day of Resurrection will be his charity." — May Allah accept your sadaqah and multiply its reward.</p>
@endsection
