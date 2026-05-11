@extends('emails.layout')
@section('title', 'A message has been released for you')
@section('content')
    <h2 style="color:#20c997; margin-top:0;">A message has been released for you</h2>
    <p>Dear{{ $name ? ' ' . e($name) : '' }},</p>
    <p>A Legacy Digital Message (LDMS) left for you by your loved one has now been securely released. These messages were kept encrypted until this moment, and only you can read them.</p>

    <div style="background:#e6fff8; border-left:4px solid #20c997; padding:16px; margin:20px 0;">
        <p style="margin:0 0 8px; font-weight:600;">Message details:</p>
        <p style="margin:0; color:#495057;">
            Type: <strong>{{ strtoupper($ldms->media_type) }}</strong><br>
            Released: <strong>{{ $ldms->date_triggered?->format('d M Y, h:i A') }}</strong>
        </p>
    </div>

    <p style="text-align:center; margin:30px 0;">
        <a href="{{ route('nok.ldms.show', $ldms->ldms_id) }}"
           style="background:#1a6fa8; color:#fff; padding:12px 28px; text-decoration:none; border-radius:6px; font-weight:600; display:inline-block;">
            View Your Message
        </a>
    </p>

    <p style="font-size:13px; color:#6c757d;">For your security, you will be asked to verify your identity (2FA) before accessing the message. The link will require you to log in to the e-Tawassul system.</p>
    <p style="font-size:13px; color:#6c757d;">May Allah grant you patience and ease.</p>
@endsection
