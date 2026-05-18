@extends('layouts.nok')
@section('title', 'Confirmation #' . $confirmation->confirmation_id)
@section('page-title', 'Death Confirmation #' . $confirmation->confirmation_id)

@php
    $statusKey = $confirmation->status ?? 'pending';
    $statusColor = ['pending' => 'warning', 'verified' => 'success', 'rejected' => 'danger'][$statusKey] ?? 'secondary';
@endphp

@push('styles')
<style>
    .ref-code { font-family:'Courier New',monospace; font-size:11px; color:#1E40AF;
                background:#EFF6FF; padding:3px 8px; border-radius:4px; white-space:nowrap; }

    .status-pill { display:inline-flex; align-items:center; gap:5px;
                   font-size:11px; font-weight:700; padding:5px 12px; border-radius:12px;
                   text-transform:uppercase; letter-spacing:0.3px; }
    .status-pill.pending  { background:#FEF3C7; color:#92400E; }
    .status-pill.verified { background:#D1FAE5; color:#065F46; }
    .status-pill.rejected { background:#FEE2E2; color:#991B1B; }

    /* Timeline (matches student crisis show) */
    .timeline { position:relative; padding-left:24px; }
    .timeline::before { content:''; position:absolute; left:8px; top:6px; bottom:6px;
                        width:2px; background:#E5E7EB; }
    .timeline-item { position:relative; margin-bottom:18px; }
    .timeline-item:last-child { margin-bottom:0; }
    .timeline-dot { position:absolute; left:-23px; top:3px;
                    width:14px; height:14px; border-radius:50%;
                    border:2px solid #fff; box-shadow:0 0 0 2px #E5E7EB; }
    .timeline-item.done .timeline-dot { box-shadow:0 0 0 2px #10B981; }
    .timeline-item.failed .timeline-dot { box-shadow:0 0 0 2px #EF4444; }
    .timeline-item.pending .timeline-dot { background:#D1D5DB !important; box-shadow:0 0 0 2px #E5E7EB; }
    .timeline-item strong { display:block; font-size:14px; color:#111827; }
    .timeline-item .small { font-size:12px; color:#6B7280; }

    .file-card { background:#F9FAFB; border:1px solid #E5E7EB; border-radius:8px;
                 padding:12px 14px; display:flex; align-items:center; gap:12px; }
    .file-card .ic { color:#6B7280; font-size:22px; }
    .file-card .nm { font-family:'Courier New',monospace; font-size:11px;
                     color:#1E40AF; flex:1; word-break:break-all; }
    .file-card .meta { font-size:11px; color:#6B7280; white-space:nowrap; }

    .info-table { width:100%; margin:0; }
    .info-table td { padding:8px 0; border-bottom:1px solid #F3F4F6;
                     vertical-align:top; font-size:13.5px; }
    .info-table td:first-child { color:#6B7280; width:40%;
                                 text-transform:uppercase; font-size:11px;
                                 font-weight:700; letter-spacing:0.5px; }
    .info-table tr:last-child td { border-bottom:none; }

    .ldms-row { display:flex; align-items:center; justify-content:space-between;
                padding:10px 0; border-bottom:1px solid #F3F4F6; }
    .ldms-row:last-child { border-bottom:none; }
    .ldms-row .ldms-meta { font-size:11.5px; color:#6B7280; }
    .ldms-row .btn-view { font-size:11.5px; padding:5px 12px; border-radius:6px;
                          background:#1E40AF; color:#fff; text-decoration:none;
                          display:inline-flex; align-items:center; gap:5px; }
    .ldms-row .btn-view:hover { background:#1E3A8A; color:#fff; }

    .admin-notes { background:#F9FAFB; border-left:3px solid #1E40AF;
                   border-radius:0 8px 8px 0; padding:12px 14px;
                   font-size:13px; color:#374151; line-height:1.5;
                   white-space:pre-wrap; }
    .admin-notes.rejected { border-left-color:#EF4444; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <a href="{{ route('nok.dashboard') }}" class="btn btn-link p-0 mb-3">
        <i class="bi bi-arrow-left"></i> Back to dashboard
    </a>

    <div class="row g-3">
        {{-- LEFT --}}
        <div class="col-lg-8">
            <div class="content-card">
                <div class="d-flex align-items-center mb-3 flex-wrap gap-2">
                    <span class="status-pill {{ $statusKey }}">
                        @if($statusKey==='pending')<i class="bi bi-clock"></i>@endif
                        @if($statusKey==='verified')<i class="bi bi-check-circle-fill"></i>@endif
                        @if($statusKey==='rejected')<i class="bi bi-x-circle-fill"></i>@endif
                        {{ strtoupper($statusKey) }}
                    </span>
                    <span class="ref-code">DC-{{ str_pad($confirmation->confirmation_id, 6, '0', STR_PAD_LEFT) }}</span>
                    <small class="text-muted ms-auto">
                        Submitted {{ $confirmation->date_triggered?->format('d M Y, h:i A') }}
                    </small>
                </div>

                <h4 class="mb-1">Death Confirmation</h4>
                <p class="text-muted small mb-3">
                    Submission for <strong>{{ $confirmation->student?->full_name ?? '—' }}</strong>
                    ({{ $confirmation->student_id }})
                </p>

                <h6 class="text-uppercase text-muted small mt-4 mb-2">Submission details</h6>
                <table class="info-table">
                    <tr>
                        <td>Submitted by</td>
                        <td>
                            <strong>{{ $confirmation->nextOfKin?->full_name ?? '—' }}</strong>
                            <small class="text-muted d-block">
                                {{ $confirmation->nextOfKin?->relationship_to_student ?? 'Next of Kin' }}
                            </small>
                        </td>
                    </tr>
                    <tr>
                        <td>Date triggered</td>
                        <td>{{ $confirmation->date_triggered?->format('d F Y, h:i A') ?? '—' }}</td>
                    </tr>
                    @if($confirmation->date_confirmed)
                        <tr>
                            <td>{{ $statusKey === 'verified' ? 'Verified at' : 'Reviewed at' }}</td>
                            <td>{{ $confirmation->date_confirmed?->format('d F Y, h:i A') }}</td>
                        </tr>
                    @endif
                </table>

                {{-- Supporting document (metadata only — kin uploaded it, they don't need to re-download) --}}
                @if($confirmation->media_file_path)
                    <h6 class="text-uppercase text-muted small mt-4 mb-2">Supporting document</h6>
                    <div class="file-card">
                        <i class="bi bi-file-earmark-medical ic"></i>
                        <span class="nm">{{ $confirmation->media_file_name ?? basename($confirmation->media_file_path) }}</span>
                        <span class="meta">
                            {{ number_format(($confirmation->media_file_size ?? 0) / 1024, 1) }} KB
                        </span>
                        <span class="meta"><i class="bi bi-shield-lock"></i> Encrypted</span>
                    </div>
                    <p class="text-muted small mt-1 mb-0">
                        <i class="bi bi-info-circle"></i>
                        Your uploaded document is stored encrypted on the server and used
                        only for verification.
                    </p>
                @endif

                {{-- Your notes --}}
                @if(trim((string) $confirmation->admin_comments) !== '' && $statusKey === 'pending')
                    {{-- During PENDING the field still holds the kin's submission notes --}}
                    <h6 class="text-uppercase text-muted small mt-4 mb-2">Your notes</h6>
                    <div class="admin-notes">{{ $confirmation->admin_comments }}</div>
                @endif

                {{-- Admin's notes after a decision --}}
                @if(in_array($statusKey, ['verified', 'rejected']) && trim((string) $confirmation->admin_comments) !== '')
                    <h6 class="text-uppercase text-muted small mt-4 mb-2">
                        {{ $statusKey === 'rejected' ? "Administrator's response" : "Administrator's note" }}
                    </h6>
                    <div class="admin-notes {{ $statusKey === 'rejected' ? 'rejected' : '' }}">
                        {{ $confirmation->admin_comments }}
                    </div>
                @endif

                @if($confirmation->blockchain_reference)
                    <h6 class="text-uppercase text-muted small mt-4 mb-2">Blockchain proof</h6>
                    <code style="font-size:11px; color:#1E40AF; background:#EFF6FF; padding:6px 10px; border-radius:4px; display:inline-block; word-break:break-all;">
                        <i class="bi bi-shield-check"></i>
                        {{ $confirmation->blockchain_reference }}
                    </code>
                    <p class="text-muted small mt-1 mb-0">
                        This verification event is permanently recorded on a tamper-proof audit chain.
                    </p>
                @endif
            </div>

            {{-- Released LDMS (only if verified + messages exist) --}}
            @if($statusKey === 'verified' && $releasedLdms->isNotEmpty())
                <div class="content-card mt-3">
                    <h5 class="mb-3">
                        <i class="bi bi-envelope-paper-fill text-primary"></i>
                        Messages released to you
                    </h5>
                    <p class="text-muted small mb-3">
                        The following final messages were released as part of this confirmation.
                        Click to read.
                    </p>
                    @foreach($releasedLdms as $ldms)
                        <div class="ldms-row">
                            <div>
                                <div class="fw-semibold">
                                    LDMS-{{ str_pad($ldms->ldms_id, 4, '0', STR_PAD_LEFT) }}
                                    <span class="badge bg-secondary" style="font-size:9px;">
                                        {{ strtoupper($ldms->media_type ?? 'text') }}
                                    </span>
                                </div>
                                <div class="ldms-meta">
                                    Released {{ $ldms->date_triggered?->diffForHumans() }}
                                </div>
                            </div>
                            <a href="{{ route('nok.ldms.show', $ldms->ldms_id) }}" class="btn-view">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- RIGHT --}}
        <div class="col-lg-4">
            <div class="content-card">
                <h6 class="text-uppercase text-muted small mb-3">Status timeline</h6>
                <div class="timeline">
                    {{-- Step 1: Submitted --}}
                    <div class="timeline-item done">
                        <div class="timeline-dot bg-primary"></div>
                        <strong>Submitted</strong>
                        <div class="small">{{ $confirmation->date_triggered?->format('d M Y, h:i A') }}</div>
                    </div>

                    {{-- Step 2: Admin review --}}
                    @if($statusKey === 'verified')
                        <div class="timeline-item done">
                            <div class="timeline-dot bg-success"></div>
                            <strong>Verified by administrator</strong>
                            <div class="small">{{ $confirmation->date_confirmed?->format('d M Y, h:i A') }}</div>
                        </div>
                    @elseif($statusKey === 'rejected')
                        <div class="timeline-item failed">
                            <div class="timeline-dot bg-danger"></div>
                            <strong>Reviewed — needs follow-up</strong>
                            <div class="small">{{ $confirmation->date_confirmed?->format('d M Y, h:i A') }}</div>
                        </div>
                    @else
                        <div class="timeline-item pending">
                            <div class="timeline-dot bg-secondary"></div>
                            <strong>Administrator review</strong>
                            <div class="small">Pending</div>
                        </div>
                    @endif

                    {{-- Step 3: Outcome --}}
                    @if($statusKey === 'verified')
                        @if($releasedLdms->isNotEmpty())
                            <div class="timeline-item done">
                                <div class="timeline-dot bg-success"></div>
                                <strong>Messages released</strong>
                                <div class="small">{{ $releasedLdms->count() }} message(s) available to view</div>
                            </div>
                        @else
                            <div class="timeline-item pending">
                                <div class="timeline-dot bg-secondary"></div>
                                <strong>Awaiting message release</strong>
                                <div class="small">Administrator will release any final messages soon.</div>
                            </div>
                        @endif
                    @elseif($statusKey === 'rejected')
                        <div class="timeline-item pending">
                            <div class="timeline-dot bg-secondary"></div>
                            <strong>Resubmit if needed</strong>
                            <div class="small">Please contact the administrator for next steps.</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="content-card mt-3">
                <h6 class="text-uppercase text-muted small mb-2">What happens next</h6>
                @if($statusKey === 'pending')
                    <p class="small text-muted mb-0">
                        Administrators receive your submission and verify the supporting
                        document. You'll be emailed when a decision is made. If your
                        submission is verified, any final messages the student left will
                        be released to you.
                    </p>
                @elseif($statusKey === 'verified')
                    <p class="small text-muted mb-0">
                        The confirmation has been verified and the event is now permanently
                        recorded. Any final messages the student left have been (or will
                        shortly be) released to you. You will be emailed when each message
                        becomes available.
                    </p>
                @elseif($statusKey === 'rejected')
                    <p class="small text-muted mb-0">
                        Your submission could not be verified at this time. Please review the
                        administrator's note above and contact the office if you need help.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
