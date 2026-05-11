@extends('emails.layout')
@section('title', 'Your verification code')
@section('content')
    <h2 style="color:#1a6fa8; margin-top:0;">Verification code</h2>
    <p>Hello{{ $name ? ' ' . e($name) : '' }},</p>
    <p>Use the code below to complete your sign-in. This code is valid for <strong>5 minutes</strong>.</p>
    <div style="text-align:center; margin:30px 0;">
        <div style="display:inline-block; padding:18px 32px; background:#f0f7fc; border:2px dashed #1a6fa8; border-radius:8px;">
            <span style="font-size:34px; font-weight:700; letter-spacing:10px; color:#1a6fa8;">{{ $code }}</span>
        </div>
    </div>
    <p style="font-size:13px; color:#6c757d;">If you didn't request this code, please ignore this email or contact the system administrator if you're concerned about your account security.</p>
@endsection
