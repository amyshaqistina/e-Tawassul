@extends('emails.layout')
@section('title', 'Verified student crisis notification')
@section('content')
    <h2 style="color:#1a6fa8; margin-top:0;">Verified Student Crisis Notification</h2>

    <p>Assalamualaikum{{ $lecturerName ? ' ' . e($lecturerName) : '' }},</p>

    <p>A student in your <strong>{{ $courseCode }}@if($courseName) ({{ $courseName }})@endif</strong>
       class has had a crisis verified by IIUM administration. We kindly request that you consider
       this circumstance for their attendance and academic obligations.</p>

    <table cellpadding="8" cellspacing="0" style="background:#f0f7fc; width:100%; border-radius:6px; margin:20px 0;">
        <tr><td style="color:#6c757d; width:35%;">Student</td><td><strong>{{ $studentName }}</strong></td></tr>
        <tr><td style="color:#6c757d;">Matric</td><td>{{ $studentMatric }}</td></tr>
        <tr><td style="color:#6c757d;">Course</td><td>{{ $courseCode }}@if($courseName) &mdash; {{ $courseName }}@endif</td></tr>
        <tr><td style="color:#6c757d;">Crisis</td><td><strong>{{ $crisisType }}</strong></td></tr>
        <tr><td style="color:#6c757d;">Reported</td><td>{{ $incidentDate }}</td></tr>
        <tr><td style="color:#6c757d;">Verified by</td><td>{{ $verifiedBy }}</td></tr>
        <tr><td style="color:#6c757d;">Verified at</td><td>{{ $verifiedAt }}</td></tr>
        <tr><td style="color:#6c757d;">Status</td><td><span style="background:#28a745; color:#fff; padding:3px 10px; border-radius:12px; font-size:12px;">VERIFIED</span></td></tr>
    </table>

    <p style="margin-bottom:6px;"><strong>Please consider this circumstance when reviewing:</strong></p>
    <ul style="margin:0 0 16px 22px; padding:0; color:#495057; line-height:1.85;">
        <li>Attendance for the coming weeks</li>
        <li>Upcoming deadlines or assessments</li>
        <li>Class participation expectations</li>
    </ul>
    <p>The student may need flexibility, an extension, or time to recover.</p>

    {{-- How to reach the student --}}
    <div style="background:#f8f9fa; border-left:4px solid #1a6fa8; padding:14px 16px; margin:20px 0;">
        <p style="margin:0 0 8px; font-weight:600; color:#1a6fa8;">📞 To reach the student</p>
        <p style="margin:0 0 6px; color:#495057;">
            Simply <strong>reply to this email</strong> &mdash; your message will go directly to
            {{ $studentFirstName }}@if($studentEmail) at
            <a href="mailto:{{ $studentEmail }}" style="color:#1a6fa8; text-decoration:none;">{{ $studentEmail }}</a>@endif.
        </p>
        <p style="margin:6px 0 0; color:#495057;">
            You may also call the IIUM Well-being Office at
            <a href="tel:+60361964000" style="color:#1a6fa8; text-decoration:none;">+60 3-6196 4000</a>
            for support resources.
        </p>
    </div>

    <p style="font-size:13px; color:#6c757d;">May Allah grant ease to your student and patience in your support.</p>

    <p style="margin-top:24px; font-size:11.5px; color:#94a3b8; padding-top:14px; border-top:1px solid #e5e7eb;">
        Verification ID: <strong style="color:#6c757d;">CR-{{ str_pad($reportId, 6, '0', STR_PAD_LEFT) }}</strong>
        &middot; This notification is logged on a tamper-proof audit trail.
    </p>
@endsection
