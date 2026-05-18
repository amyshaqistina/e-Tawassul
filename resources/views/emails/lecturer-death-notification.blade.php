@extends('emails.layout')
@section('title', 'Student bereavement notification')
@section('content')
    <h2 style="color:#374151; margin-top:0;">In Sympathy — Student Bereavement</h2>

    <p>Assalamualaikum{{ $lecturerName ? ' ' . e($lecturerName) : '' }},</p>

    <p>We regret to inform you that <strong>{{ $studentName }}</strong>, a student enrolled in your
       <strong>{{ $courseCode }}@if($courseName) ({{ $courseName }})@endif</strong> class,
       has passed away. The death confirmation has been verified by IIUM administration.</p>

    <table cellpadding="8" cellspacing="0" style="background:#F9FAFB; width:100%; border-radius:6px; margin:20px 0; border:1px solid #E5E7EB;">
        <tr><td style="color:#6c757d; width:35%;">Student</td><td><strong>{{ $studentName }}</strong></td></tr>
        <tr><td style="color:#6c757d;">Matric</td><td>{{ $studentMatric }}</td></tr>
        <tr><td style="color:#6c757d;">Course</td><td>{{ $courseCode }}@if($courseName) &mdash; {{ $courseName }}@endif</td></tr>
        <tr><td style="color:#6c757d;">Verified at</td><td>{{ $verifiedAt }}</td></tr>
        <tr><td style="color:#6c757d;">Status</td><td><span style="background:#6B7280; color:#fff; padding:3px 10px; border-radius:12px; font-size:12px;">DECEASED</span></td></tr>
    </table>

    <p style="margin-bottom:6px;"><strong>You may wish to take the following steps:</strong></p>
    <ul style="margin:0 0 16px 22px; padding:0; color:#495057; line-height:1.85;">
        <li>Update your class records to mark the student as deceased</li>
        <li>Excuse any outstanding attendance, assessments, or coursework</li>
        <li>Inform other students sensitively if appropriate</li>
        <li>Coordinate with the kulliyyah on any final academic matters (e.g. posthumous grades)</li>
    </ul>

    <div style="background:#FEF3C7; border-left:4px solid #F59E0B; padding:14px 16px; margin:20px 0;">
        <p style="margin:0; color:#92400E; font-size:13px;">
            <strong>Please handle this information with discretion and compassion.</strong>
            The family has been in contact with our administration. For coordination
            on academic matters please contact the Student Well-being Office.
        </p>
    </div>

    <p style="font-size:13px; color:#6c757d;">
        إِنَّا لِلَّٰهِ وَإِنَّا إِلَيْهِ رَاجِعُونَ — <em>To Allah we belong and to Him we return.</em>
        May Allah grant the deceased a place in Jannah and patience to those left behind.
    </p>

    <p style="margin-top:24px; font-size:11.5px; color:#94a3b8; padding-top:14px; border-top:1px solid #e5e7eb;">
        Verification ID: <strong style="color:#6c757d;">DC-{{ str_pad($confirmationId, 6, '0', STR_PAD_LEFT) }}</strong>
        &middot; This notification is logged on a tamper-proof audit trail.
    </p>
@endsection
