@extends('emails.layout')
@section('title', 'Crisis report received')
@section('content')
    <h2 style="color:#1a6fa8; margin-top:0;">Your crisis report has been received</h2>
    <p>Assalamualaikum{{ $name ? ' ' . e($name) : '' }},</p>
    <p>We have received your crisis report and the administrators are reviewing it. You will be notified as soon as the verification decision is made.</p>

    <table cellpadding="8" cellspacing="0" style="background:#f0f7fc; width:100%; border-radius:6px; margin:20px 0;">
        <tr><td style="color:#6c757d;">Report ID</td><td><strong>#{{ $report->report_id }}</strong></td></tr>
        <tr><td style="color:#6c757d;">Date submitted</td><td>{{ $report->date_reported?->format('d M Y, h:i A') }}</td></tr>
        <tr><td style="color:#6c757d;">Status</td><td><span style="background:#ffc107; color:#000; padding:3px 10px; border-radius:12px; font-size:12px;">PENDING REVIEW</span></td></tr>
    </table>

    <p>You can view the latest status of your report at any time from your student dashboard.</p>
    <p style="font-size:13px; color:#6c757d;">May Allah ease your hardship and grant patience. Our team will assist you as quickly as possible.</p>
@endsection
