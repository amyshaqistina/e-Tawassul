@extends('layouts.admin')
@section('title', 'Death Confirmation #' . $confirmation->confirmation_id)

@php
    $statusKey = $confirmation->status ?? 'pending';
    $currentAdmin = auth('admin')->user();

    $studentLdms = \App\Models\Ldms::where('student_id', $confirmation->student_id)
        ->orderByDesc('updated_at')
        ->get();
@endphp

@push('styles')
<style>
    .status-banner {
        border-radius: 12px;
        padding: 18px 22px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-left: 6px solid;
        gap: 16px;
        flex-wrap: wrap;
    }
    .status-banner.s-pending  { background:#FFFBEB; border-color:#F59E0B; }
    .status-banner.s-verified { background:#ECFDF5; border-color:#10B981; }
    .status-banner.s-rejected { background:#FEF2F2; border-color:#EF4444; }
    .status-banner h3 {
        font-size: 22px; font-weight: 700; margin: 0; color: #111827;
    }
    .status-banner .ref-code {
        font-family: 'Courier New', monospace;
        font-size: 11px; background: #fff; padding: 2px 8px;
        border-radius: 4px; color: #374151; border: 1px solid #E5E7EB;
    }
    .status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 11px; font-weight: 700; padding: 5px 12px;
        border-radius: 12px; text-transform: uppercase; letter-spacing: 0.3px;
    }
    .status-pill.pending  { background:#FEF3C7; color:#92400E; }
    .status-pill.verified { background:#D1FAE5; color:#065F46; }
    .status-pill.rejected { background:#FEE2E2; color:#991B1B; }

    .info-card {
        background: #fff; border: 1px solid #E5E7EB;
        border-radius: 12px; margin-bottom: 16px; overflow: hidden;
    }
    .info-card-header {
        padding: 14px 20px; border-bottom: 1px solid #E5E7EB;
        background: #F9FAFB; display: flex; align-items: center; gap: 8px;
    }
    .info-card-header h5 {
        margin: 0; font-size: 15px; font-weight: 700; color: #111827;
        display: flex; align-items: center; gap: 8px;
    }
    .info-card-header i.title-icon { color: #1E40AF; }

    .info-row {
        display: grid;
        grid-template-columns: 200px minmax(0, 1fr);
        padding: 14px 20px;
        border-bottom: 1px solid #F3F4F6;
        align-items: start;
        gap: 16px;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row .label {
        font-size: 11px; font-weight: 700; color: #6B7280;
        text-transform: uppercase; letter-spacing: 0.5px;
        display: flex; align-items: center; gap: 10px; min-width: 0;
    }
    .label-icon {
        width: 32px; height: 32px; border-radius: 8px;
        background: #EFF6FF; color: #1E40AF;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 15px; flex-shrink: 0;
    }
    .info-row .value {
        font-size: 14px; color: #111827; line-height: 1.55;
        word-break: break-word; overflow-wrap: anywhere; min-width: 0;
    }
    .info-row .value .sub {
        font-size: 12px; color: #6B7280; display: block; margin-top: 2px;
    }
    @media (max-width: 991px) {
        .info-row { grid-template-columns: 1fr; gap: 8px; }
    }

    .desc-box {
        background: #F9FAFB; border: 1px solid #E5E7EB;
        border-radius: 8px; padding: 10px 14px;
        font-size: 13.5px; color: #111827; line-height: 1.6;
        white-space: pre-wrap;
    }

    /* Document file styling — now clickable */
    .evidence-inline {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .evidence-inline .file {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        border-radius: 6px;
        padding: 10px 14px;
        font-size: 12px;
        text-decoration: none;
        color: inherit;
        transition: all 0.15s;
    }
    .evidence-inline .file:hover {
        background: #EFF6FF;
        border-color: #93C5FD;
        color: inherit;
    }
    .evidence-inline .file i.file-type-icon {
        color: #6B7280;
        font-size: 18px;
        flex-shrink: 0;
    }
    .evidence-inline .file:hover i.file-type-icon { color: #1E40AF; }
    .evidence-inline .file .file-name {
        font-family: 'Courier New', monospace;
        font-size: 11.5px;
        color: #1E40AF;
        word-break: break-all;
        flex: 1;
        min-width: 0;
        font-weight: 600;
    }
    .evidence-inline .file .file-meta {
        font-size: 11px;
        color: #6B7280;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .evidence-inline .file .file-action {
        font-size: 11px;
        color: #1E40AF;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-weight: 600;
    }

    .profile-card, .lect-card, .ldms-card {
        background: #fff; border: 1px solid #E5E7EB;
        border-radius: 12px; margin-bottom: 16px; overflow: hidden;
    }
    .profile-card-header, .lect-card-header, .ldms-card-header {
        padding: 14px 20px; border-bottom: 1px solid #E5E7EB;
        background: #F9FAFB; display: flex; align-items: center; gap: 8px;
    }
    .profile-card-header h5, .lect-card-header h5, .ldms-card-header h5 {
        margin: 0; font-size: 15px; font-weight: 700; color: #111827;
    }
    .profile-card-header i, .lect-card-header i, .ldms-card-header i {
        color: #1E40AF;
    }
    .profile-body { padding: 20px; text-align: center; }
    .profile-avatar {
        width: 70px; height: 70px; border-radius: 50%;
        background: linear-gradient(135deg, #6B7280, #374151);
        color: #fff; font-size: 28px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 10px;
    }
    .profile-body .name { font-size: 15px; font-weight: 700; color: #111827; }
    .profile-body .matric { font-size: 12px; color: #6B7280; margin-bottom: 12px; }
    .profile-meta { text-align: left; padding-top: 10px; border-top: 1px solid #F3F4F6; }
    .profile-meta div {
        display: flex; align-items: flex-start; gap: 8px;
        font-size: 12.5px; color: #374151; padding: 5px 0;
        word-break: break-word;
    }
    .profile-meta i { color: #6B7280; width: 14px; flex-shrink: 0; margin-top: 2px; }

    .lect-card-body, .ldms-card-body { padding: 16px 20px; }
    .lect-card-body .blurb {
        font-size: 11.5px; color: #6B7280;
        margin: 0 0 12px; line-height: 1.5;
    }
    .lect-row { padding: 10px 0; border-bottom: 1px solid #F3F4F6; }
    .lect-row:last-child { border-bottom: none; }
    .lect-row .course {
        font-size: 11px; font-weight: 700; color: #1E40AF;
        letter-spacing: 0.3px; text-transform: uppercase;
    }
    .lect-row .lname {
        font-size: 13px; font-weight: 600; color: #111827; margin: 2px 0;
    }
    .lect-row .lemail { font-size: 11.5px; color: #6B7280; word-break: break-all; }
    .lect-row .nomatch { font-size: 11.5px; color: #92400E; font-style: italic; }
    .lect-empty { font-size: 12px; color: #6B7280; padding: 8px 0; }
    .lect-test-banner {
        background: #FFFBEB; border: 1px solid #FCD34D;
        border-radius: 8px; padding: 10px 12px;
        font-size: 11.5px; color: #92400E; margin-top: 12px; line-height: 1.45;
    }
    .lect-test-banner i { margin-right: 5px; }

    .ldms-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 0; border-bottom: 1px solid #F3F4F6; gap: 8px;
    }
    .ldms-row:last-child { border-bottom: none; }
    .ldms-row .ldms-info { flex: 1; min-width: 0; }
    .ldms-row .ldms-id { font-size: 12px; font-weight: 700; color: #111827; }
    .ldms-row .ldms-status { font-size: 11px; color: #6B7280; }
    .ldms-row .btn-release {
        font-size: 11px; padding: 5px 10px; background: #10B981; color: #fff;
        border: none; border-radius: 6px; text-decoration: none;
        display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;
    }
    .ldms-row .btn-release:hover { background: #059669; color: #fff; }
    .ldms-row .btn-view {
        font-size: 11px; padding: 5px 10px; background: #F3F4F6; color: #374151;
        border: none; border-radius: 6px; text-decoration: none; white-space: nowrap;
    }
    .ldms-row .btn-view:hover { background: #E5E7EB; color: #111827; }

    .decision-card {
        background: #fff; border: 1px solid #E5E7EB;
        border-radius: 12px; overflow: hidden; margin-bottom: 16px;
    }
    .decision-card-header {
        padding: 14px 20px; border-bottom: 1px solid #E5E7EB; background: #F9FAFB;
    }
    .decision-card-header h5 {
        margin: 0; font-size: 15px; font-weight: 700; color: #111827;
        display: flex; align-items: center; gap: 8px;
    }
    .decision-card-header i { color: #1E40AF; }
    .decision-card-body { padding: 18px 20px; }
    .decision-card-body label {
        font-size: 12px; font-weight: 600; color: #374151;
        margin-bottom: 5px; display: block;
    }
    .decision-card-body .form-control { font-size: 13px; }

    .outcome-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .outcome-option {
        border: 1.5px solid #E5E7EB; border-radius: 10px;
        padding: 14px 8px; text-align: center; cursor: pointer;
        transition: all 0.15s; background: #fff;
    }
    .outcome-option:hover { border-color: #9CA3AF; }
    .outcome-option.active.verify {
        border-color: #10B981; background: #ECFDF5; color: #065F46;
    }
    .outcome-option.active.reject {
        border-color: #EF4444; background: #FEF2F2; color: #991B1B;
    }
    .outcome-option i {
        font-size: 20px; display: block; margin-bottom: 4px; color: #9CA3AF;
    }
    .outcome-option.active.verify i { color: #10B981; }
    .outcome-option.active.reject i { color: #EF4444; }
    .outcome-option .label-text { font-size: 12px; font-weight: 600; }
    .outcome-option input[type="radio"] { display: none; }
    .help-text { font-size: 11px; color: #6B7280; margin-top: 4px; }
    .btn-decision {
        font-size: 13px; font-weight: 600; padding: 9px 14px;
        border-radius: 8px; width: 100%; border: none;
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    }
    .btn-decision:disabled { background: #9CA3AF; color: #fff; cursor: not-allowed; }
    .btn-verify-action { background: #10B981; color: #fff; }
    .btn-verify-action:hover { background: #059669; color: #fff; }
    .btn-reject-action { background: #EF4444; color: #fff; }
    .btn-reject-action:hover { background: #DC2626; color: #fff; }

    .verdict-card {
        border-radius: 12px; padding: 16px 18px;
        margin-bottom: 16px; border: 1px solid;
    }
    .verdict-card.verified { background: #ECFDF5; border-color: #10B981; }
    .verdict-card.rejected { background: #FEF2F2; border-color: #EF4444; }
    .verdict-card h5 {
        font-size: 14px; font-weight: 700; margin: 0 0 6px 0;
        display: flex; align-items: center; gap: 8px;
    }
    .verdict-card.verified h5 { color: #065F46; }
    .verdict-card.rejected h5 { color: #991B1B; }
    .verdict-card p { margin: 0; font-size: 13px; line-height: 1.5; }
    .verdict-card.verified p { color: #047857; }
    .verdict-card.rejected p { color: #B91C1C; }

    .back-link {
        color: #1E40AF; text-decoration: none; font-size: 14px;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .back-link:hover { text-decoration: underline; }
    .btn-print { background: #374151; color: #fff; }
    .btn-print:hover { background: #1F2937; color: #fff; }

    @media print {
        .no-print, .back-link, .decision-card { display: none !important; }
        .info-card, .profile-card { border: 1px solid #E5E7EB !important; }
    }

    /* ============ Donation Summary Card (read-only) ============ */
    .donation-summary-card {
        background: #fff; border: 1px solid #E5E7EB;
        border-radius: 12px; overflow: hidden;
        margin-bottom: 16px; position: relative;
    }
    .donation-summary-card::before {
        content: ""; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, #15803d, #22c55e);
    }
    .donation-summary-card.closed::before {
        background: linear-gradient(90deg, #374151, #1f2937);
    }
    .ds-header {
        padding: 14px 18px; border-bottom: 1px solid #F3F4F6;
        display: flex; align-items: center; gap: 10px;
    }
    .ds-header-icon {
        width: 32px; height: 32px; border-radius: 9px;
        background: #E8F6EE; color: #15803d;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; flex-shrink: 0;
    }
    .donation-summary-card.closed .ds-header-icon {
        background: #F3F4F6; color: #6B7280;
    }
    .ds-header h5 { margin: 0; font-size: 14px; font-weight: 700; color: #111827; }
    .ds-header .ds-sub { margin: 0; font-size: 11.5px; color: #9CA3AF; }
    .ds-body { padding: 16px 18px; }
    .ds-status-row {
        display: flex; align-items: center; gap: 8px;
        padding: 9px 12px; border-radius: 7px; margin-bottom: 12px;
        font-size: 12.5px; font-weight: 600;
    }
    .ds-status-row.open { background: #E8F6EE; color: #15803d; }
    .ds-status-row.closed { background: #F3F4F6; color: #374151; }
    .ds-status-row i { font-size: 14px; }
    .ds-amount-row {
        display: flex; justify-content: space-between; align-items: baseline;
        margin-bottom: 8px;
    }
    .ds-amount-raised {
        font-size: 22px; font-weight: 700; color: #111827; line-height: 1.1;
        letter-spacing: -0.01em;
    }
    .ds-amount-target { font-size: 12px; color: #6B7280; }
    .ds-amount-target strong { color: #111827; }
    .ds-bar {
        height: 8px; background: #F3F4F6;
        border-radius: 999px; overflow: hidden; margin-bottom: 10px;
    }
    .ds-bar-fill {
        height: 100%; border-radius: 999px;
        background: linear-gradient(90deg, #15803d, #22c55e);
    }
    .ds-bar-fill.hit { background: linear-gradient(90deg, #a16207, #eab308); }
    .ds-stats-row {
        display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;
        font-size: 11.5px; color: #6B7280; margin-bottom: 12px;
    }
    .ds-stat { padding: 8px 10px; background: #F9FAFB; border-radius: 7px; }
    .ds-stat strong { display: block; font-size: 14px; color: #111827; font-weight: 700; }
    .ds-stat span { font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #9CA3AF; }
    .ds-closed-note {
        background: #FEF3C7; border: 1px solid #FCD34D;
        border-radius: 7px; padding: 9px 11px; margin-bottom: 12px;
        font-size: 11.5px; color: #78350F; line-height: 1.5;
    }
    .ds-closed-note strong { color: #92400E; }
    .ds-manage-link {
        display: flex; align-items: center; justify-content: space-between;
        gap: 8px; padding: 10px 13px; background: #EFF6FF;
        border: 1px solid #BFDBFE; border-radius: 8px;
        color: #1E40AF; text-decoration: none; font-weight: 600;
        font-size: 12.5px; transition: all 0.15s ease;
    }
    .ds-manage-link:hover {
        background: #1E40AF; color: #fff; border-color: #1E40AF;
    }
    .ds-manage-link i { font-size: 13px; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="{{ route('admin.death.index') }}" class="back-link">
            <i class="bi bi-arrow-left"></i> Back to Death Confirmations
        </a>
        <button type="button" class="btn btn-print btn-sm" onclick="window.print()">
            <i class="bi bi-printer"></i> Print / Export PDF
        </button>
    </div>

    <div class="status-banner s-{{ $statusKey }}">
        <div>
            <h3 class="mb-2">
                Death Confirmation
                <span style="font-size:14px; color:#6B7280; font-weight:500;">
                    — Submitted by Next of Kin
                </span>
            </h3>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="ref-code">DC-{{ str_pad($confirmation->confirmation_id, 6, '0', STR_PAD_LEFT) }}</span>
                <small class="text-muted">Submitted {{ $confirmation->date_triggered?->diffForHumans() }}</small>
            </div>
        </div>
        <span class="status-pill {{ $statusKey }}">
            @if($statusKey === 'pending')<i class="bi bi-clock"></i>@endif
            @if($statusKey === 'verified')<i class="bi bi-check-circle-fill"></i>@endif
            @if($statusKey === 'rejected')<i class="bi bi-x-circle-fill"></i>@endif
            {{ strtoupper($statusKey) }}
        </span>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="bi bi-info-circle-fill title-icon"></i> Confirmation Information</h5>
                </div>

                <div class="info-row">
                    <div class="label">
                        <span class="label-icon"><i class="bi bi-person-fill"></i></span>
                        Student
                    </div>
                    <div class="value">
                        <strong>{{ $confirmation->student?->full_name ?? '—' }}</strong>
                        <span class="text-muted">({{ $confirmation->student_id }})</span>
                        @if($confirmation->student?->faculty)
                            <span class="sub">{{ $confirmation->student->faculty }}</span>
                        @endif
                    </div>
                </div>

                <div class="info-row">
                    <div class="label">
                        <span class="label-icon"><i class="bi bi-pencil-square"></i></span>
                        Submitted By
                    </div>
                    <div class="value">
                        <strong>{{ $confirmation->nextOfKin?->full_name ?? '—' }}</strong>
                        <span class="text-muted">
                            ({{ $confirmation->nextOfKin?->relationship_to_student ?? 'Next of Kin' }})
                        </span>
                        <span class="sub">
                            Next-of-kin submission •
                            {{ $confirmation->date_triggered?->format('d M Y, H:i') }}
                        </span>
                    </div>
                </div>

                @if($confirmation->nextOfKin?->email || $confirmation->nextOfKin?->phone)
                    <div class="info-row">
                        <div class="label">
                            <span class="label-icon"><i class="bi bi-telephone-fill"></i></span>
                            Kin Contact
                        </div>
                        <div class="value">
                            @if($confirmation->nextOfKin?->email)
                                <i class="bi bi-envelope"></i> {{ $confirmation->nextOfKin->email }}
                            @endif
                            @if($confirmation->nextOfKin?->phone)
                                <span class="sub">
                                    <i class="bi bi-phone"></i> {{ $confirmation->nextOfKin->phone }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="info-row">
                    <div class="label">
                        <span class="label-icon"><i class="bi bi-calendar-event-fill"></i></span>
                        Date Reported
                    </div>
                    <div class="value">
                        {{ $confirmation->date_triggered?->format('d F Y, H:i') ?? '—' }}
                    </div>
                </div>

                @if($confirmation->crisis_id)
                    <div class="info-row">
                        <div class="label">
                            <span class="label-icon"><i class="bi bi-link-45deg"></i></span>
                            Linked Crisis
                        </div>
                        <div class="value">
                            <a href="{{ route('admin.crisis.show', $confirmation->crisis_id) }}">
                                Crisis #{{ $confirmation->crisis_id }}
                            </a>
                            <span class="sub">
                                This confirmation is associated with a prior crisis report.
                            </span>
                        </div>
                    </div>
                @endif

                <div class="info-row">
                    <div class="label">
                        <span class="label-icon"><i class="bi bi-file-text-fill"></i></span>
                        Notes / Description
                    </div>
                    <div class="value">
                        <div class="desc-box">
                            {{ $confirmation->admin_comments ?: 'No additional notes provided by the next of kin.' }}
                        </div>
                    </div>
                </div>

                {{-- Supporting Document — now clickable --}}
                <div class="info-row">
                    <div class="label">
                        <span class="label-icon"><i class="bi bi-paperclip"></i></span>
                        Supporting Document
                    </div>
                    <div class="value">
                        @if($confirmation->media_file_path)
                            @php
                                $docName = $confirmation->media_file_name ?: basename($confirmation->media_file_path);
                                $ext = strtolower(pathinfo($docName, PATHINFO_EXTENSION));
                                $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                $isPdf = $ext === 'pdf';
                                $icon = $isImg ? 'bi-file-earmark-image'
                                      : ($isPdf ? 'bi-file-earmark-pdf'
                                      : 'bi-file-earmark-medical');
                            @endphp
                            <div class="evidence-inline">
                                <a href="{{ route('admin.death.document.download', $confirmation->confirmation_id) }}"
                                   target="_blank" rel="noopener"
                                   class="file">
                                    <i class="bi {{ $icon }} file-type-icon"></i>
                                    <span class="file-name">{{ $docName }}</span>
                                    @if($confirmation->media_file_size)
                                        <span class="file-meta">
                                            {{ number_format($confirmation->media_file_size / 1024, 1) }} KB
                                        </span>
                                    @endif
                                    <span class="file-action">
                                        <i class="bi bi-box-arrow-up-right"></i> Open
                                    </span>
                                </a>
                            </div>
                            <div class="help-text" style="margin-top:6px;">
                                <i class="bi bi-shield-lock"></i>
                                Stored securely on the server. Click to download and verify offline before approval.
                            </div>
                        @else
                            <em class="text-muted">No supporting document provided</em>
                        @endif
                    </div>
                </div>

                @if($confirmation->status !== 'pending' && $confirmation->admin_comments)
                    <div class="info-row">
                        <div class="label">
                            <span class="label-icon"><i class="bi bi-chat-left-text-fill"></i></span>
                            Reviewer's Notes
                        </div>
                        <div class="value">
                            <div class="desc-box">{{ $confirmation->admin_comments }}</div>
                        </div>
                    </div>
                @endif

                @if($confirmation->blockchain_reference)
                    <div class="info-row">
                        <div class="label">
                            <span class="label-icon"><i class="bi bi-link-45deg"></i></span>
                            Blockchain
                        </div>
                        <div class="value">
                            <x-blockchain-badge :hash="$confirmation->blockchain_reference" />
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">

            @if($statusKey === 'verified')
                <div class="verdict-card verified">
                    <h5><i class="bi bi-check-circle-fill"></i> Confirmation Verified</h5>
                    <p>
                        Verified by <strong>System Administrator</strong>
                        @if($confirmation->date_confirmed)
                            <br><small>{{ $confirmation->date_confirmed->format('d M Y, h:i A') }}</small>
                        @endif
                        <br><small>Student marked deceased. Lecturers notified.</small>
                    </p>
                </div>
            @elseif($statusKey === 'rejected')
                <div class="verdict-card rejected">
                    <h5><i class="bi bi-x-circle-fill"></i> Confirmation Rejected</h5>
                    <p>
                        Rejected by <strong>System Administrator</strong>
                        @if($confirmation->date_confirmed)
                            <br><small>{{ $confirmation->date_confirmed->format('d M Y, h:i A') }}</small>
                        @endif
                    </p>
                </div>
            @endif

            {{-- Donation Summary (read-only) — shows context while reviewing the death --}}
            @if($confirmation->crisis_id && $confirmation->crisis)
                @php
                    $linkedCrisis = $confirmation->crisis;
                    $dRaised      = (float) $linkedCrisis->donation_raised;
                    $dTarget      = (float) $linkedCrisis->donation_target;
                    $dPercent     = $dTarget > 0 ? min(100, ($dRaised / $dTarget) * 100) : 0;
                    $dDonors      = $linkedCrisis->donations()->count();
                    $dIsOpen      = (bool) $linkedCrisis->donation_open;
                @endphp

                <div class="donation-summary-card {{ $dIsOpen ? '' : 'closed' }}">
                    <div class="ds-header">
                        <div class="ds-header-icon">
                            <i class="bi bi-heart-fill"></i>
                        </div>
                        <div>
                            <h5>Donation Summary</h5>
                            <p class="ds-sub">For linked Crisis #{{ $linkedCrisis->crisis_id }}</p>
                        </div>
                    </div>
                    <div class="ds-body">

                        <div class="ds-status-row {{ $dIsOpen ? 'open' : 'closed' }}">
                            @if($dIsOpen)
                                <i class="bi bi-broadcast"></i>
                                <span>Donations are OPEN</span>
                            @else
                                <i class="bi bi-lock-fill"></i>
                                <span>Donations are CLOSED</span>
                            @endif
                        </div>

                        <div class="ds-amount-row">
                            <div class="ds-amount-raised">RM {{ number_format($dRaised, 2) }}</div>
                            <div class="ds-amount-target">of <strong>RM {{ number_format($dTarget, 2) }}</strong></div>
                        </div>

                        <div class="ds-bar">
                            <div class="ds-bar-fill {{ $dPercent >= 100 ? 'hit' : '' }}" style="width: {{ $dPercent }}%"></div>
                        </div>

                        <div class="ds-stats-row">
                            <div class="ds-stat">
                                <strong>{{ $dDonors }}</strong>
                                <span>{{ \Illuminate\Support\Str::plural('Donor', $dDonors) }}</span>
                            </div>
                            <div class="ds-stat">
                                <strong>{{ number_format($dPercent, 0) }}%</strong>
                                <span>Funded</span>
                            </div>
                        </div>

                        @if(!$dIsOpen && $linkedCrisis->donation_closed_reason)
                            <div class="ds-closed-note">
                                <strong>Closed:</strong> {{ $linkedCrisis->donation_closed_reason }}
                                @if($linkedCrisis->donation_closed_at)
                                    <br><small>{{ $linkedCrisis->donation_closed_at->diffForHumans() }}</small>
                                @endif
                            </div>
                        @endif

                        @if($statusKey === 'pending' && $dIsOpen)
                            <div class="ds-closed-note">
                                <strong>Note:</strong> If you verify this death, donations will automatically close and the public donate page will show a respectful closed message.
                            </div>
                        @endif

                        <a href="{{ route('admin.crisis.show', $linkedCrisis->crisis_id) }}" class="ds-manage-link">
                            <span><i class="bi bi-sliders"></i> Manage donation on crisis page</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            @endif

            <div class="profile-card">
                <div class="profile-card-header">
                    <i class="bi bi-person-circle"></i>
                    <h5>Student Profile</h5>
                </div>
                <div class="profile-body">
                    <div class="profile-avatar">
                        {{ strtoupper(substr($confirmation->student?->full_name ?? '?', 0, 1)) }}
                    </div>
                    <div class="name">{{ $confirmation->student?->full_name ?? '—' }}</div>
                    <div class="matric">{{ $confirmation->student_id }}</div>

                    <div class="profile-meta">
                        @if($confirmation->student?->email)
                            <div><i class="bi bi-envelope"></i> <span>{{ $confirmation->student->email }}</span></div>
                        @endif
                        @if($confirmation->student?->phone)
                            <div><i class="bi bi-telephone"></i> <span>{{ $confirmation->student->phone }}</span></div>
                        @endif
                        @if($confirmation->student?->programme)
                            <div><i class="bi bi-mortarboard"></i> <span>{{ $confirmation->student->programme }}</span></div>
                        @endif
                        @if($confirmation->student?->status)
                            @php
                                $isDeceased = $confirmation->student->status === 'deceased';
                                $dotColor = $isDeceased ? '#6B7280' : '#10B981';
                            @endphp
                            <div>
                                <i class="bi bi-circle-fill" style="font-size:7px; color:{{ $dotColor }}; margin-top:5px;"></i>
                                <span>{{ ucfirst($confirmation->student->status) }} student</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="lect-card">
                <div class="lect-card-header">
                    <i class="bi bi-mortarboard-fill"></i>
                    <h5>Student's Lecturers</h5>
                </div>
                <div class="lect-card-body">
                    @if(($studentCourses ?? collect())->isEmpty())
                        <div class="lect-empty">
                            <i class="bi bi-info-circle"></i>
                            No courses on file for this student. Lecturers cannot be notified
                            unless the student's timetable was synced via iMaalum.
                        </div>
                    @else
                        <p class="blurb">
                            These {{ count($studentCourses) }} lecturer(s) will be notified by
                            email when this confirmation is verified.
                        </p>

                        @foreach($studentCourses as $row)
                            <div class="lect-row">
                                <div class="course">{{ $row->course_code }}{{ $row->course_name ? ' — ' . \Illuminate\Support\Str::limit($row->course_name, 40) : '' }}</div>
                                @if($row->lecturer_id)
                                    <div class="lname">
                                        {{ trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '')) }}
                                    </div>
                                    <div class="lemail">
                                        <i class="bi bi-envelope"></i> {{ $row->email }}
                                    </div>
                                @else
                                    <div class="lname">{{ $row->lecturer_name_raw ?: 'Unknown lecturer' }}</div>
                                    <div class="nomatch">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        Not in directory — will not be notified
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        @if(env('TESTING_MODE_REDIRECT_LECTURER_EMAILS', false))
                            <div class="lect-test-banner">
                                <i class="bi bi-cone-striped"></i>
                                <strong>Test mode:</strong> all lecturer emails are redirected to
                                <code>{{ env('TESTING_MODE_LECTURER_REDIRECT_EMAIL') }}</code>
                                until the safety flag is turned off.
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            @if($statusKey === 'verified')
                <div class="ldms-card">
                    <div class="ldms-card-header">
                        <i class="bi bi-envelope-paper-fill"></i>
                        <h5>Last Digital Messages</h5>
                    </div>
                    <div class="ldms-card-body">
                        @if($studentLdms->isEmpty())
                            <p class="text-muted small mb-0">
                                <i class="bi bi-info-circle"></i>
                                This student did not leave any LDMS messages.
                            </p>
                        @else
                            <p class="lect-card-body blurb" style="padding:0;">
                                {{ $studentLdms->count() }} message(s) from this student.
                                Release each one to notify the next of kin.
                            </p>
                            @foreach($studentLdms as $ldms)
                                <div class="ldms-row">
                                    <div class="ldms-info">
                                        <div class="ldms-id">
                                            LDMS #{{ $ldms->ldms_id }}
                                            <span class="badge bg-secondary"
                                                  style="font-size:9px; vertical-align:middle;">
                                                {{ strtoupper($ldms->media_type ?? 'text') }}
                                            </span>
                                        </div>
                                        <div class="ldms-status">
                                            @if($ldms->is_released)
                                                <i class="bi bi-check-circle text-success"></i>
                                                Released {{ $ldms->date_triggered?->diffForHumans() }}
                                            @else
                                                <i class="bi bi-clock text-warning"></i>
                                                Pending release
                                            @endif
                                        </div>
                                    </div>
                                    @if($ldms->is_released)
                                        <a href="{{ route('admin.ldms.show', $ldms->ldms_id) }}" class="btn-view">
                                            View
                                        </a>
                                    @else
                                        <a href="{{ route('admin.ldms.show', $ldms->ldms_id) }}" class="btn-release">
                                            <i class="bi bi-send"></i> Release
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif

            @if($statusKey === 'pending')
                <div class="decision-card no-print">
                    <div class="decision-card-header">
                        <h5><i class="bi bi-shield-check"></i> Verify Confirmation</h5>
                    </div>
                    <div class="decision-card-body">
                        <form id="verificationForm" method="POST" action="">
                            @csrf
                            <input type="hidden" name="decision" id="decisionInput" value="">

                            <div class="mb-3">
                                <label>Staff ID</label>
                                <input type="text" name="staff_id" class="form-control"
                                    value="{{ $currentAdmin?->admin_id ? 'STAFF' . str_pad($currentAdmin->admin_id, 3, '0', STR_PAD_LEFT) : '' }}"
                                    placeholder="e.g. STAFF001" required>
                                @if($currentAdmin)
                                    <div class="help-text">Logged in as <strong>{{ $currentAdmin->admin_name }}</strong></div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label>Decision <span style="color:#EF4444;">*</span></label>
                                <div class="outcome-grid">
                                    <label class="outcome-option" id="opt-verify" onclick="setOutcome('verify')">
                                        <input type="radio" name="outcome" value="verify" required>
                                        <i class="bi bi-check-circle"></i>
                                        <div class="label-text">Verify</div>
                                    </label>
                                    <label class="outcome-option" id="opt-reject" onclick="setOutcome('reject')">
                                        <input type="radio" name="outcome" value="reject" required>
                                        <i class="bi bi-x-circle"></i>
                                        <div class="label-text">Reject</div>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label id="notesLabel">Notes (Optional)</label>
                                <textarea name="admin_comments" id="admin_comments" rows="3" class="form-control" maxlength="2000"
                                    placeholder="Add any notes..."></textarea>
                                <div class="help-text" id="notesHelp">Optional notes for the audit trail.</div>
                            </div>

                            <button type="submit" id="submitBtn" class="btn-decision" disabled
                                    onclick="return confirmSubmit(event);">
                                <i class="bi bi-arrow-right-circle"></i> Choose a decision first
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    const verifyAction = "{{ route('admin.death.verify', $confirmation->confirmation_id) }}";

    function setOutcome(choice) {
        const form = document.getElementById('verificationForm');
        const optV = document.getElementById('opt-verify');
        const optR = document.getElementById('opt-reject');
        const btn  = document.getElementById('submitBtn');
        const lbl  = document.getElementById('notesLabel');
        const help = document.getElementById('notesHelp');
        const txt  = document.getElementById('admin_comments');
        const decisionInput = document.getElementById('decisionInput');

        optV.classList.remove('active', 'verify', 'reject');
        optR.classList.remove('active', 'verify', 'reject');

        form.action = verifyAction;

        if (choice === 'verify') {
            optV.classList.add('active', 'verify');
            decisionInput.value = 'verified';
            btn.classList.remove('btn-reject-action');
            btn.classList.add('btn-verify-action');
            btn.innerHTML = '<i class="bi bi-check2-circle"></i> Verify &amp; Record';
            lbl.innerHTML = 'Notes (Optional)';
            help.textContent = 'Optional notes for the audit trail.';
            txt.required = false;
            txt.removeAttribute('minlength');
        } else {
            optR.classList.add('active', 'reject');
            decisionInput.value = 'rejected';
            btn.classList.remove('btn-verify-action');
            btn.classList.add('btn-reject-action');
            btn.innerHTML = '<i class="bi bi-x-circle"></i> Reject Confirmation';
            lbl.innerHTML = 'Reason for Rejection <span style="color:#EF4444;">*</span>';
            help.textContent = 'Required (min 10 characters). Recorded in the audit log.';
            txt.required = true;
            txt.minLength = 10;
        }
        btn.disabled = false;
    }

    function confirmSubmit(e) {
        const outcome = document.querySelector('input[name="outcome"]:checked')?.value;
        if (!outcome) { e.preventDefault(); return false; }
        const msg = outcome === 'verify'
            ? 'Verify this death confirmation? This will mark the student as deceased and record the event on the blockchain. Lecturers will be notified by email.'
            : 'Reject this death confirmation? The next of kin will be informed.';
        if (!confirm(msg)) { e.preventDefault(); return false; }
        return true;
    }
</script>
@endpush
@endsection
