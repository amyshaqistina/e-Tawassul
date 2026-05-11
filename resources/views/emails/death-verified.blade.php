@extends('emails.layout')
@section('title', 'Death confirmation verified')
@section('content')
    <h2 style="color:#28a745; margin-top:0;">Death confirmation verified</h2>
    <p>Dear{{ $name ? ' ' . e($name) : '' }},</p>
    <p>The death confirmation you submitted has been reviewed and verified by the IIUM administration. The student's records have been updated accordingly.</p>

    <table cellpadding="8" cellspacing="0" style="background:#e8f5e9; width:100%; border-radius:6px; margin:20px 0;">
        <tr><td style="color:#6c757d;">Submission ID</td><td><strong>#{{ $confirmation->confirmation_id }}</strong></td></tr>
        <tr><td style="color:#6c757d;">Verified at</td><td>{{ $confirmation->date_confirmed?->format('d M Y, h:i A') }}</td></tr>
        <tr><td style="color:#6c757d;">Status</td><td><span style="background:#28a745; color:#fff; padding:3px 10px; border-radius:12px; font-size:12px;">VERIFIED</span></td></tr>
    </table>

    @if($blockchainHash)
        <div style="background:#f8f9fa; border-left:4px solid #1a6fa8; padding:14px; margin:20px 0;">
            <p style="margin:0 0 6px; font-weight:600; color:#1a6fa8;">🔒 Blockchain Audit Reference</p>
            <p style="margin:0; font-family: 'Courier New', monospace; font-size:12px; word-break:break-all; color:#495057;">{{ $blockchainHash }}</p>
            <p style="margin:8px 0 0; font-size:11px; color:#6c757d;">This tamper-evident SHA-256 hash provides cryptographic proof of the verification.</p>
        </div>
    @endif

    <p>Any final messages the student may have left (LDMS) will be released to you shortly. You will receive a separate notification when they are available to view.</p>
    <p style="font-size:13px; color:#6c757d;">Our prayers and condolences are with you and your family.</p>
@endsection
