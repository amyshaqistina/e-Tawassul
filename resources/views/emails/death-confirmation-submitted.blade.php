@extends('emails.layout')
@section('title', 'Death confirmation received')
@section('content')
    <h2 style="color:#1a6fa8; margin-top:0;">Your submission has been received</h2>
    <p>Dear{{ $name ? ' ' . e($name) : '' }},</p>
    <p>Inna lillahi wa inna ilayhi raji'un.</p>
    <p>We are deeply sorry for your loss. Your death confirmation submission has been received and is pending verification by the IIUM administration.</p>

    <table cellpadding="8" cellspacing="0" style="background:#f0f7fc; width:100%; border-radius:6px; margin:20px 0;">
        <tr><td style="color:#6c757d;">Submission ID</td><td><strong>#{{ $confirmation->confirmation_id }}</strong></td></tr>
        <tr><td style="color:#6c757d;">Student ID</td><td>{{ $confirmation->student_id }}</td></tr>
        <tr><td style="color:#6c757d;">Date submitted</td><td>{{ $confirmation->date_triggered?->format('d M Y, h:i A') }}</td></tr>
        <tr><td style="color:#6c757d;">Status</td><td><span style="background:#ffc107; color:#000; padding:3px 10px; border-radius:12px; font-size:12px;">PENDING VERIFICATION</span></td></tr>
    </table>

    <p>Once the verification is complete, you will receive a follow-up email. If the student left any messages or letters for next of kin (LDMS), they will be securely released to you afterwards.</p>
    <p style="font-size:13px; color:#6c757d;">May Allah grant the deceased Jannah and give strength to the family.</p>
@endsection
