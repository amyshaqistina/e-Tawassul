@extends('emails.layout')
@section('title', 'Crisis report update')
@section('content')
    <h2 style="color:#dc3545; margin-top:0;">Update on your crisis report</h2>
    <p>Assalamualaikum{{ $name ? ' ' . e($name) : '' }},</p>
    <p>After reviewing your crisis report (#{{ $report->report_id }}), the administrator was unable to verify it at this time. This does not mean we don't believe you — additional information or documentation is needed before we can proceed.</p>

    @if($reason)
        <div style="background:#fff5f5; border-left:4px solid #dc3545; padding:14px; margin:20px 0;">
            <p style="margin:0 0 6px; font-weight:600; color:#dc3545;">Administrator's note:</p>
            <p style="margin:0; color:#495057;">{{ $reason }}</p>
        </div>
    @endif

    <p>You may submit a new report with the additional information requested, or contact a student welfare officer for in-person support.</p>
    <p style="font-size:13px; color:#6c757d;">If you need help understanding this decision or wish to appeal, please reach out to the IIUM Student Welfare Office.</p>
@endsection
