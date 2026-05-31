@extends('layouts.admin')
@section('title', 'LDMS #' . $ldms->ldms_id)
@section('page-title', 'LDMS #' . $ldms->ldms_id)

@push('styles')
<style>
    .status-banner { border-radius:12px; padding:18px 22px; margin-bottom:18px;
                     display:flex; align-items:center; justify-content:space-between;
                     border-left:6px solid; }
    .status-banner.pending  { background:#FFFBEB; border-color:#F59E0B; }
    .status-banner.released { background:#ECFDF5; border-color:#10B981; }
    .status-banner h3 { font-size:22px; font-weight:700; margin:0; color:#111827; }
    .ref-code { font-family:'Courier New',monospace; font-size:11px;
                background:#fff; padding:2px 8px; border-radius:4px; color:#374151;
                border:1px solid #E5E7EB; }
    .status-pill { display:inline-flex; align-items:center; gap:5px;
                   font-size:11px; font-weight:700; padding:5px 12px; border-radius:12px;
                   text-transform:uppercase; letter-spacing:0.3px; }
    .status-pill.pending  { background:#FEF3C7; color:#92400E; }
    .status-pill.released { background:#D1FAE5; color:#065F46; }

    /* === Sealed message — privacy by design === */
    .sealed-card { background:linear-gradient(135deg,#1E40AF 0%, #1E3A8A 100%);
                   color:#fff; border-radius:12px; padding:32px 24px;
                   text-align:center; margin-bottom:16px;
                   position:relative; overflow:hidden; }
    .sealed-card::before { content:''; position:absolute; top:0; left:0; right:0; bottom:0;
                           background-image:repeating-linear-gradient(45deg,
                             rgba(255,255,255,0.04) 0,
                             rgba(255,255,255,0.04) 12px,
                             transparent 12px,
                             transparent 24px);
                           pointer-events:none; }
    .sealed-card i.seal-icon { font-size:54px; color:#FCD34D; margin-bottom:12px; display:block; }
    .sealed-card h4 { font-size:18px; font-weight:700; margin:0 0 8px; }
    .sealed-card p { font-size:13px; margin:0 0 4px; opacity:0.92; line-height:1.55; }
    .sealed-card .small-note { font-size:11px; opacity:0.7; margin-top:14px;
                               border-top:1px solid rgba(255,255,255,0.18); padding-top:12px; }

    /* === Generic info card (matches death-show style) === */
    .info-card { background:#fff; border:1px solid #E5E7EB; border-radius:12px;
                 margin-bottom:16px; overflow:hidden; }
    .info-card-header { padding:14px 20px; border-bottom:1px solid #E5E7EB; background:#F9FAFB;
                        display:flex; align-items:center; gap:8px; }
    .info-card-header h5 { margin:0; font-size:15px; font-weight:700; color:#111827; }
    .info-card-header i { color:#1E40AF; }
    .info-row { display:grid; grid-template-columns:200px 1fr; padding:14px 20px;
                border-bottom:1px solid #F3F4F6; align-items:start; }
    .info-row:last-child { border-bottom:none; }
    .info-row .label { font-size:11px; font-weight:700; color:#6B7280;
                       text-transform:uppercase; letter-spacing:0.5px;
                       display:flex; align-items:center; gap:10px; }
    .label-icon { width:32px; height:32px; border-radius:8px; background:#EFF6FF;
                  color:#1E40AF; display:inline-flex; align-items:center;
                  justify-content:center; font-size:15px; flex-shrink:0; }
    .info-row .value { font-size:14px; color:#111827; line-height:1.55; word-break:break-word; }
    .info-row .value .sub { font-size:12px; color:#6B7280; display:block; margin-top:2px; }
    .student-badge { display:inline-block; font-size:10px; font-weight:700; padding:2px 8px;
                     border-radius:10px; text-transform:uppercase; letter-spacing:0.4px; }
    .student-badge.active   { background:#D1FAE5; color:#065F46; }
    .student-badge.deceased { background:#374151; color:#fff; }
    .media-pill { background:#E5E7EB; color:#374151; font-size:10px; font-weight:700;
                  padding:3px 10px; border-radius:10px; text-transform:uppercase; letter-spacing:0.4px; }

    /* === Sidebar cards === */
    .lect-card, .recipients-card, .release-card { background:#fff; border:1px solid #E5E7EB;
                                                  border-radius:12px; margin-bottom:16px;
                                                  overflow:hidden; }
    .lect-card-header, .recipients-card-header, .release-card-header { padding:14px 20px;
              border-bottom:1px solid #E5E7EB; background:#F9FAFB;
              display:flex; align-items:center; gap:8px; }
    .lect-card-header h5, .recipients-card-header h5, .release-card-header h5 {
              margin:0; font-size:15px; font-weight:700; color:#111827; }
    .lect-card-header i, .recipients-card-header i { color:#1E40AF; }
    .lect-card-body, .recipients-body, .release-card-body { padding:16px 20px; }
    .lect-card-body .blurb { font-size:11.5px; color:#6B7280; margin:0 0 12px; line-height:1.5; }

    .lect-row { padding:10px 0; border-bottom:1px solid #F3F4F6; }
    .lect-row:last-child { border-bottom:none; }
    .lect-row .course { font-size:11px; font-weight:700; color:#1E40AF;
                        letter-spacing:0.3px; text-transform:uppercase; }
    .lect-row .lname { font-size:13px; font-weight:600; color:#111827; margin:2px 0; }
    .lect-row .lemail { font-size:11.5px; color:#6B7280; word-break:break-all; }
    .lect-row .nomatch { font-size:11.5px; color:#92400E; font-style:italic; }

    .recipient-row { padding:10px 0; border-bottom:1px solid #F3F4F6; }
    .recipient-row:last-child { border-bottom:none; }
    .recipient-row .name { font-size:13px; font-weight:700; color:#111827; }
    .recipient-row .meta { font-size:11.5px; color:#6B7280; }

    .release-card.ok       { border-color:#10B981; }
    .release-card.warn     { border-color:#F59E0B; }
    .release-card.danger   { border-color:#EF4444; }
    .release-card h5.ok    { color:#065F46; }
    .release-card h5.warn  { color:#92400E; }
    .release-card h5.danger{ color:#991B1B; }
    .release-card-body p   { font-size:12.5px; color:#6B7280; margin-bottom:10px; line-height:1.5; }

    .btn-release { background:#10B981; color:#fff; font-size:14px; font-weight:600;
                   padding:10px 16px; border-radius:8px; border:none; width:100%;
                   display:inline-flex; align-items:center; justify-content:center; gap:8px; }
    .btn-release:hover { background:#059669; color:#fff; }

    .back-link { color:#1E40AF; text-decoration:none; font-size:14px;
                 display:inline-flex; align-items:center; gap:6px; margin-bottom:12px; }
    .back-link:hover { text-decoration:underline; }
</style>
@endpush

@section('content')
<div class="container-fluid pb-3">

    <a href="{{ route('admin.ldms.index') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to LDMS list
    </a>

    {{-- Status banner --}}
    <div class="status-banner {{ $ldms->is_released ? 'released' : 'pending' }}">
        <div>
            <h3>
                Last Digital Message
                <span style="font-size:14px; color:#6B7280; font-weight:500;">
                    — Pre-written by the student
                </span>
            </h3>
            <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                <span class="ref-code">LDMS-{{ str_pad($ldms->ldms_id, 4, '0', STR_PAD_LEFT) }}</span>
                <small class="text-muted">Created {{ $ldms->created_at?->diffForHumans() }}</small>
            </div>
        </div>
        <span class="status-pill {{ $ldms->is_released ? 'released' : 'pending' }}">
            @if($ldms->is_released)
                <i class="bi bi-check-circle-fill"></i> RELEASED
            @else
                <i class="bi bi-clock"></i> PENDING RELEASE
            @endif
        </span>
    </div>

    <div class="row g-3">
        {{-- LEFT — sealed message + metadata --}}
        <div class="col-lg-8">

            {{-- SEALED MESSAGE NOTICE — admin never reads the body --}}
            <div class="sealed-card">
                <i class="bi bi-envelope-paper-fill seal-icon"></i>
                <h4>This message is sealed</h4>
                <p>The student's final message is intended for the next of kin only.</p>
                <p>To protect privacy, the message contents are <strong>not displayed to administrators</strong>.</p>
                <p>Once you trigger release, the next of kin will receive an email with a secure link to view the message.</p>
                <div class="small-note">
                    <i class="bi bi-shield-lock"></i>
                    Encrypted at rest with AES-256.
                    Attachments stored on an encrypted volume.
                    Release events recorded on a tamper-proof audit chain.
                </div>
            </div>

            {{-- Metadata card --}}
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="bi bi-info-circle-fill"></i> Message Metadata</h5>
                </div>

                <div class="info-row">
                    <div class="label">
                        <span class="label-icon"><i class="bi bi-person-fill"></i></span>
                        Student
                    </div>
                    <div class="value">
                        <strong>{{ $ldms->student?->full_name ?? '—' }}</strong>
                        <span class="text-muted">({{ $ldms->student_id }})</span>
                        <span class="sub">
                            <span class="student-badge {{ $studentDeceased ? 'deceased' : 'active' }}">
                                {{ $studentDeceased ? 'Deceased' : 'Active' }}
                            </span>
                            @if($ldms->student?->email)
                                · {{ $ldms->student->email }}
                            @endif
                        </span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="label">
                        <span class="label-icon"><i class="bi bi-file-earmark"></i></span>
                        Media Type
                    </div>
                    <div class="value">
                        <span class="media-pill">{{ strtoupper($ldms->media_type ?? 'text') }}</span>
                        @if(is_array($ldms->media_file_path) && count($ldms->media_file_path) > 0)
                            <span class="sub">
                                {{ count($ldms->media_file_path) }} attachment(s) — filenames hidden for privacy
                            </span>
                        @else
                            <span class="sub">No additional attachments</span>
                        @endif
                    </div>
                </div>

                <div class="info-row">
                    <div class="label">
                        <span class="label-icon"><i class="bi bi-calendar-event-fill"></i></span>
                        Created
                    </div>
                    <div class="value">
                        {{ $ldms->created_at?->format('d F Y, h:i A') ?? '—' }}
                        <span class="sub">{{ $ldms->created_at?->diffForHumans() }}</span>
                    </div>
                </div>

                @if($ldms->updated_at && $ldms->updated_at->ne($ldms->created_at))
                    <div class="info-row">
                        <div class="label">
                            <span class="label-icon"><i class="bi bi-pencil-square"></i></span>
                            Last Edited
                        </div>
                        <div class="value">
                            {{ $ldms->updated_at?->format('d F Y, h:i A') }}
                            <span class="sub">{{ $ldms->updated_at?->diffForHumans() }}</span>
                        </div>
                    </div>
                @endif

                @if($ldms->is_released)
                    <div class="info-row">
                        <div class="label">
                            <span class="label-icon"><i class="bi bi-send-check"></i></span>
                            Released
                        </div>
                        <div class="value">
                            {{ $ldms->date_triggered?->format('d F Y, h:i A') }}
                            <span class="sub">{{ $ldms->date_triggered?->diffForHumans() }} · sent to {{ $recipients->count() }} next of kin</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT — recipient list + lecturer panel + release action --}}
        <div class="col-lg-4">

            {{-- Recipients --}}
            <div class="recipients-card">
                <div class="recipients-card-header">
                    <i class="bi bi-people-fill"></i>
                    <h5>Recipients (Next of Kin)</h5>
                </div>
                <div class="recipients-body">
                    @if($recipients->isEmpty())
                        <p class="text-muted small mb-0">
                            <i class="bi bi-exclamation-triangle text-warning"></i>
                            No next of kin records found for this student.
                            Records must be added before this message can be released.
                        </p>
                    @else
                        <p class="text-muted small mb-2">
                            {{ $recipients->count() }} kin will receive the message upon release.
                        </p>
                        @foreach($recipients as $nok)
                            <div class="recipient-row">
                                <div class="name">{{ $nok->full_name }}</div>
                                <div class="meta">
                                    {{ $nok->relationship_to_student ?? 'Next of Kin' }} ·
                                    <i class="bi bi-envelope"></i> {{ $nok->email }}
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- Lecturer context (read-only — lecturers are NOT recipients here,
                 they get notified separately when the death is verified) --}}
            <div class="lect-card">
                <div class="lect-card-header">
                    <i class="bi bi-mortarboard-fill"></i>
                    <h5>Student's Lecturers</h5>
                </div>
                <div class="lect-card-body">
                    @if($studentCourses->isEmpty())
                        <p class="blurb">No course/lecturer context available for this student.</p>
                    @else
                        <p class="blurb">
                            <i class="bi bi-info-circle"></i>
                            For reference only. Lecturer notification is part of the death-confirmation
                            flow — releasing this message does <em>not</em> email lecturers.
                        </p>
                        @foreach($studentCourses as $row)
                            <div class="lect-row">
                                <div class="course">
                                    {{ $row->course_code }}{{ $row->course_name ? ' — ' . \Illuminate\Support\Str::limit($row->course_name, 35) : '' }}
                                </div>
                                @if($row->lecturer_id)
                                    <div class="lname">{{ trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '')) }}</div>
                                    <div class="lemail"><i class="bi bi-envelope"></i> {{ $row->email }}</div>
                                @else
                                    <div class="lname">{{ $row->lecturer_name_raw ?: 'Unknown lecturer' }}</div>
                                    <div class="nomatch"><i class="bi bi-exclamation-triangle"></i> Not in directory</div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- Release action --}}
            @if($ldms->is_released)
                <div class="release-card ok">
                    <div class="release-card-header">
                        <h5 class="ok"><i class="bi bi-check-circle-fill text-success"></i> Already Released</h5>
                    </div>
                    <div class="release-card-body">
                        <p>
                            This message was released on
                            <strong>{{ $ldms->date_triggered?->format('d M Y, h:i A') }}</strong>
                            to <strong>{{ $recipients->count() }}</strong> next of kin.
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-shield-check text-success"></i>
                            The release event is permanently recorded on the audit chain.
                        </p>
                    </div>
                </div>
            @elseif(!$studentDeceased)
                <div class="release-card warn">
                    <div class="release-card-header">
                        <h5 class="warn"><i class="bi bi-shield-exclamation"></i> Student Not Deceased</h5>
                    </div>
                    <div class="release-card-body">
                        <p class="mb-0">
                            Messages may only be released after a death confirmation
                            has been verified for this student. Verify the death
                            confirmation first, then return to this page.
                        </p>
                    </div>
                </div>
            @elseif($recipients->isEmpty())
                <div class="release-card danger">
                    <div class="release-card-header">
                        <h5 class="danger"><i class="bi bi-person-x"></i> No Recipients</h5>
                    </div>
                    <div class="release-card-body">
                        <p class="mb-0">
                            This student has no next of kin records on file.
                            Add at least one before releasing the message.
                        </p>
                    </div>
                </div>
            @else
                <div class="release-card ok">
                    <div class="release-card-header">
                        <h5 class="ok"><i class="bi bi-send"></i> Release Message</h5>
                    </div>
                    <div class="release-card-body">
                        <p>
                            Triggering will email each next of kin with a secure link
                            to view this message. The event is recorded on the audit
                            chain and cannot be undone.
                        </p>
                        <form method="POST" action="{{ route('admin.ldms.trigger', $ldms->ldms_id) }}">
                            @csrf
                            <button type="submit" class="btn-release"
                                    onclick="return confirm('Release this message to {{ $recipients->count() }} next of kin? This action cannot be undone.');">
                                <i class="bi bi-unlock"></i> Release to Next of Kin
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
