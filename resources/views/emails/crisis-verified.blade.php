@extends('emails.layout')
@section('title', 'Crisis report verified')
@section('content')
    <h2 style="color:#28a745; margin-top:0;">Your crisis report has been verified</h2>
    <p>Assalamualaikum{{ $name ? ' ' . e($name) : '' }},</p>
    <p>Your crisis report has been reviewed and <strong>verified</strong> by the administration. The case is now active and visible on the public dashboard so that the community can offer support.</p>

    <table cellpadding="8" cellspacing="0" style="background:#e8f5e9; width:100%; border-radius:6px; margin:20px 0;">
        <tr><td style="color:#6c757d;">Report ID</td><td><strong>#{{ $report->report_id }}</strong></td></tr>
        <tr><td style="color:#6c757d;">Verified at</td><td>{{ $report->verified_at?->format('d M Y, h:i A') }}</td></tr>
        <tr><td style="color:#6c757d;">Status</td><td><span style="background:#28a745; color:#fff; padding:3px 10px; border-radius:12px; font-size:12px;">VERIFIED</span></td></tr>
    </table>

    @if($blockchainHash)
        <div style="background:#f8f9fa; border-left:4px solid #1a6fa8; padding:14px; margin:20px 0;">
            <p style="margin:0 0 6px; font-weight:600; color:#1a6fa8;">🔒 Blockchain Audit Reference</p>
            <p style="margin:0; font-family: 'Courier New', monospace; font-size:12px; word-break:break-all; color:#495057;">{{ $blockchainHash }}</p>
            <p style="margin:8px 0 0; font-size:11px; color:#6c757d;">This SHA-256 hash provides tamper-evident proof that your case has been formally recorded.</p>
        </div>
    @endif

    @if($report->admin_remarks)
        <p><strong>Administrator notes:</strong></p>
        <p style="background:#f0f7fc; padding:12px; border-radius:6px;">{{ $report->admin_remarks }}</p>
    @endif

    <p style="font-size:13px; color:#6c757d;">May Allah grant ease to you and your family in this difficult time.</p>
@endsection
