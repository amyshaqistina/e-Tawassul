@extends('layouts.admin')
@section('title', 'Report #' . $report->report_id)
@section('page-title', 'Crisis Report #' . $report->report_id)

@push('styles')
<style>
    /* ===== Status banner ===== */
    .status-banner {
        border-radius: 12px; padding: 18px 22px; margin-bottom: 18px;
        display: flex; align-items: center; justify-content: space-between;
        border-left: 6px solid;
    }
    .status-banner.s-pending  { background:#FFFBEB; border-color:#F59E0B; }
    .status-banner.s-verified { background:#ECFDF5; border-color:#10B981; }
    .status-banner.s-rejected { background:#FEF2F2; border-color:#EF4444; }

    .status-banner h3 { font-size: 22px; font-weight: 700; margin: 0; color:#111827; }
    .status-banner .ref-code {
        font-family: 'Courier New', monospace; font-size: 11px;
        background: #fff; padding: 2px 8px; border-radius: 4px; color:#374151;
        border:1px solid #E5E7EB;
    }

    .status-pill {
        display:inline-flex; align-items:center; gap:5px;
        font-size:11px; font-weight:700; padding:5px 12px; border-radius:12px;
        text-transform:uppercase; letter-spacing:0.3px;
    }
    .status-pill.pending  { background:#FEF3C7; color:#92400E; }
    .status-pill.verified { background:#D1FAE5; color:#065F46; }
    .status-pill.rejected { background:#FEE2E2; color:#991B1B; }

    /* ===== Crisis info card ===== */
    .info-card {
        background:#fff; border:1px solid #E5E7EB; border-radius:12px;
        margin-bottom: 16px; overflow:hidden;
    }
    .info-card-header {
        padding: 14px 20px; border-bottom: 1px solid #E5E7EB;
        background:#F9FAFB; display:flex; align-items:center; gap:8px;
    }
    .info-card-header h5 { margin:0; font-size:15px; font-weight:700; color:#111827; }
    .info-card-header i { color:#1E40AF; }
    .info-card-body { padding: 0; }

    .info-row {
        display: grid; grid-template-columns: 160px 1fr;
        padding: 14px 20px; border-bottom: 1px solid #F3F4F6;
        align-items: start;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row .label {
        font-size: 11px; font-weight: 700; color: #6B7280;
        text-transform: uppercase; letter-spacing: 0.5px;
    }
    .info-row .value { font-size: 14px; color: #111827; }
    .info-row .value .sub {
        font-size: 12px; color: #6B7280; display:block; margin-top:2px;
    }

    /* ===== Student profile card ===== */
    .profile-card {
        background:#fff; border:1px solid #E5E7EB; border-radius:12px;
        margin-bottom:16px; overflow:hidden;
    }
    .profile-card-header {
        padding:14px 20px; border-bottom:1px solid #E5E7EB; background:#F9FAFB;
        display:flex; align-items:center; gap:8px;
    }
    .profile-card-header h5 { margin:0; font-size:15px; font-weight:700; color:#111827; }
    .profile-card-header i { color:#1E40AF; }

    .profile-body { padding: 24px 20px; text-align:center; }
    .profile-avatar {
        width:84px; height:84px; border-radius:50%;
        background: linear-gradient(135deg, #3B82F6, #1E40AF);
        color:#fff; font-size:34px; font-weight:700;
        display:flex; align-items:center; justify-content:center;
        margin: 0 auto 12px;
    }
    .profile-body .name { font-size:16px; font-weight:700; color:#111827; margin-bottom:2px; }
    .profile-body .matric { font-size:12px; color:#6B7280; margin-bottom:14px; }
    .profile-meta { text-align:left; padding-top:12px; border-top:1px solid #F3F4F6; }
    .profile-meta div { display:flex; align-items:center; gap:8px; font-size:13px; color:#374151;
                        padding:6px 0; }
    .profile-meta i { color:#6B7280; width:16px; }

    /* ===== Case verified card ===== */
    .verified-card {
        background:#ECFDF5; border:1px solid #10B981; border-radius:12px;
        padding:18px 20px; margin-bottom:16px;
    }
    .verified-card h5 {
        font-size:15px; font-weight:700; color:#065F46; margin:0 0 6px 0;
        display:flex; align-items:center; gap:8px;
    }
    .verified-card p { margin:0; font-size:13px; color:#047857; }

    /* ===== Verify / reject action cards ===== */
    .action-card {
        background:#fff; border:1px solid #E5E7EB; border-radius:12px;
        padding:18px 20px; margin-bottom:16px;
    }
    .action-card.verify { border-color:#10B981; }
    .action-card.reject { border-color:#EF4444; }
    .action-card h5 { font-size:15px; font-weight:700; margin:0 0 12px 0;
                      display:flex; align-items:center; gap:8px; }
    .action-card.verify h5 { color:#065F46; }
    .action-card.reject h5 { color:#991B1B; }
    .action-card label { font-size:12px; font-weight:600; color:#374151; margin-bottom:4px; }
    .action-card .form-control, .action-card .form-select { font-size:13px; }

    /* ===== Evidence + details boxes ===== */
    .details-box {
        background:#F9FAFB; border:1px solid #E5E7EB; border-radius:8px;
        padding:12px 14px; font-size:13px; color:#374151; line-height:1.55;
    }
    .evidence-list { list-style:none; padding:0; margin:0; }
    .evidence-list li {
        padding: 8px 12px; border:1px solid #E5E7EB; border-radius:8px;
        margin-bottom: 6px; display:flex; align-items:center; gap:10px;
        font-size: 13px; background:#fff;
    }
    .evidence-list li i { color:#6B7280; }
    .evidence-list li .file-name { font-family:'Courier New',monospace; font-size:11px;
                                   color:#1E40AF; word-break:break-all; }
    .evidence-list li .file-meta { font-size:11px; color:#6B7280; margin-left:auto; }

    .back-link { color:#1E40AF; text-decoration:none; font-size:14px;
                 display:inline-flex; align-items:center; gap:6px; }
    .back-link:hover { text-decoration:underline; }

    .btn-print { background:#374151; color:#fff; }
    .btn-print:hover { background:#1F2937; color:#fff; }

    @media print {
        .no-print, .action-card, .back-link { display:none !important; }
        .info-card, .profile-card, .verified-card { box-shadow:none !important; border:1px solid #E5E7EB !important; }
        .status-banner { border-left-width: 4px !important; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="{{ route('admin.crisis.index') }}" class="back-link">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
        <button type="button" class="btn btn-print btn-sm" onclick="window.print()">
            <i class="bi bi-printer"></i> Print / Export PDF
        </button>
    </div>

    {{-- Status banner --}}
    @php
        $statusKey = $report->report_status ?? 'pending';
        $bannerClass = 's-' . $statusKey;
    @endphp
    <div class="status-banner {{ $bannerClass }}">
        <div>
            <h3 class="mb-2">{{ ucwords(str_replace('_',' ', $report->crisis?->crisis_type ?? 'Crisis Report')) }}</h3>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="ref-code">CR-{{ str_pad($report->report_id, 6, '0', STR_PAD_LEFT) }}</span>
                <small class="text-muted">Reported {{ $report->date_reported?->diffForHumans() }}</small>
            </div>
        </div>
        <div>
            <span class="status-pill {{ $statusKey }}">
                @if($statusKey==='pending')<i class="bi bi-clock"></i>@endif
                @if($statusKey==='verified')<i class="bi bi-check-circle-fill"></i>@endif
                @if($statusKey==='rejected')<i class="bi bi-x-circle-fill"></i>@endif
                {{ strtoupper($statusKey) }}
            </span>
        </div>
    </div>

    <div class="row g-3">
        {{-- LEFT: Crisis information --}}
        <div class="col-lg-8">

            <div class="info-card">
                <div class="info-card-header">
                    <i class="bi bi-info-circle-fill"></i>
                    <h5>Crisis Information</h5>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <div class="label">Student</div>
                        <div class="value">
                            <strong>{{ $report->student?->full_name ?? '—' }}</strong>
                            ({{ $report->student_id }})
                            <span class="sub">
                                {{ $report->student?->faculty ?? '' }}
                                @if($report->student?->email) • {{ $report->student->email }} @endif
                            </span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="label">Reported By</div>
                        <div class="value">{{ $report->student?->full_name ?? '—' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="label">Crisis Type</div>
                        <div class="value">
                            {{ ucwords(str_replace('_',' ', $report->crisis?->crisis_type ?? '—')) }}
                            @if($report->crisis?->sub_category)
                                <span class="sub">{{ ucwords(str_replace('_',' ', $report->crisis->sub_category)) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="label">Incident Date</div>
                        <div class="value">
                            {{ $report->crisis?->incident_date?->format('d F Y') ?? $report->date_reported?->format('d F Y') }}
                        </div>
                    </div>
                    @if($report->crisis?->location)
                    <div class="info-row">
                        <div class="label">Location</div>
                        <div class="value"><i class="bi bi-geo-alt"></i> {{ $report->crisis->location }}</div>
                    </div>
                    @endif
                    <div class="info-row">
                        <div class="label">Description</div>
                        <div class="value">{{ $report->crisis?->crisis_description ?? '—' }}</div>
                    </div>
                    @if($report->crisis?->crisis_details)
                    <div class="info-row">
                        <div class="label">Additional Details</div>
                        <div class="value">
                            <div class="details-box">{{ $report->crisis->crisis_details }}</div>
                        </div>
                    </div>
                    @endif
                    <div class="info-row">
                        <div class="label">Personal Statement</div>
                        <div class="value">{{ $report->report_description }}</div>
                    </div>
                </div>
            </div>

            {{-- Supporting evidence --}}
            @if($report->supporting_evidence_path && count((array)$report->supporting_evidence_path) > 0)
            <div class="info-card">
                <div class="info-card-header">
                    <i class="bi bi-paperclip"></i>
                    <h5>Supporting Evidence</h5>
                </div>
                <div class="info-card-body" style="padding:14px 20px;">
                    <ul class="evidence-list">
                        @foreach((array)$report->supporting_evidence_path as $p)
                            <li>
                                <i class="bi bi-file-earmark-lock"></i>
                                <span class="file-name">{{ basename($p) }}</span>
                                <span class="file-meta">Stored encrypted</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            {{-- Admin remarks (if any) --}}
            @if($report->admin_remarks)
            <div class="info-card">
                <div class="info-card-header">
                    <i class="bi bi-chat-left-text"></i>
                    <h5>Administrator's Notes</h5>
                </div>
                <div class="info-card-body" style="padding:14px 20px;">
                    <div class="details-box">{{ $report->admin_remarks }}</div>
                </div>
            </div>
            @endif

            {{-- Blockchain record --}}
            @if($report->blockchain_hash)
            <div class="info-card">
                <div class="info-card-header">
                    <i class="bi bi-link-45deg"></i>
                    <h5>Blockchain Record</h5>
                </div>
                <div class="info-card-body" style="padding:14px 20px;">
                    <x-blockchain-badge :hash="$report->blockchain_hash" />
                </div>
            </div>
            @endif
        </div>

        {{-- RIGHT: Sidebar --}}
        <div class="col-lg-4">

            @if($statusKey === 'verified')
            <div class="verified-card">
                <h5><i class="bi bi-check-circle-fill"></i> Case Verified</h5>
                <p>
                    Verified by <strong>{{ $report->verifier?->admin_name ?? 'System' }}</strong>
                    @if($report->verified_at)
                        on {{ $report->verified_at->format('d M Y, h:i') }}
                    @endif
                </p>
            </div>
            @elseif($statusKey === 'rejected')
            <div class="verified-card" style="background:#FEF2F2; border-color:#EF4444;">
                <h5 style="color:#991B1B;"><i class="bi bi-x-circle-fill"></i> Report Rejected</h5>
                <p style="color:#B91C1C;">
                    Reviewed by <strong>{{ $report->verifier?->admin_name ?? 'System' }}</strong>
                    @if($report->verified_at)
                        on {{ $report->verified_at->format('d M Y, h:i') }}
                    @endif
                </p>
            </div>
            @endif

            {{-- Student profile --}}
            <div class="profile-card">
                <div class="profile-card-header">
                    <i class="bi bi-person-circle"></i>
                    <h5>Student Profile</h5>
                </div>
                <div class="profile-body">
                    <div class="profile-avatar">
                        {{ strtoupper(substr($report->student?->full_name ?? '?', 0, 1)) }}
                    </div>
                    <div class="name">{{ $report->student?->full_name ?? '—' }}</div>
                    <div class="matric">{{ $report->student_id }}</div>

                    <div class="profile-meta">
                        @if($report->student?->email)
                        <div><i class="bi bi-envelope"></i> {{ $report->student->email }}</div>
                        @endif
                        @if($report->student?->phone)
                        <div><i class="bi bi-telephone"></i> {{ $report->student->phone }}</div>
                        @endif
                        @if($report->student?->faculty)
                        <div><i class="bi bi-building"></i> {{ $report->student->faculty }}</div>
                        @endif
                        @if($report->student?->programme)
                        <div><i class="bi bi-mortarboard"></i> {{ $report->student->programme }}</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Verify / Reject panels (only when pending) --}}
            @if($statusKey === 'pending')
                <div class="action-card verify no-print">
                    <h5><i class="bi bi-shield-check"></i> Verify Report</h5>
                    <form method="POST" action="{{ route('admin.crisis.verify', $report->report_id) }}">
                        @csrf
                        <div class="mb-2">
                            <label>Donation Target (RM)</label>
                            <input type="number" name="donation_target" min="0" max="1000000" step="100"
                                   value="{{ $report->crisis?->donation_target ?? 0 }}"
                                   class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label>Internal Remarks (optional)</label>
                            <textarea name="admin_remarks" rows="2" maxlength="2000"
                                      class="form-control form-control-sm"></textarea>
                        </div>
                        <button class="btn btn-success w-100 btn-sm" type="submit">
                            <i class="bi bi-check-circle"></i> Verify &amp; Publish
                        </button>
                    </form>
                </div>

                <div class="action-card reject no-print">
                    <h5><i class="bi bi-x-circle"></i> Reject Report</h5>
                    <form method="POST" action="{{ route('admin.crisis.reject', $report->report_id) }}">
                        @csrf
                        <div class="mb-2">
                            <label>Reason (required, min 10 chars)</label>
                            <textarea name="admin_remarks" rows="3" minlength="10" maxlength="2000"
                                      class="form-control form-control-sm" required></textarea>
                        </div>
                        <button class="btn btn-outline-danger w-100 btn-sm" type="submit">
                            <i class="bi bi-x-circle"></i> Reject Report
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
