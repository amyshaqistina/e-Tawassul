@extends('layouts.nok')
@section('title', 'Death Confirmation #' . $confirmation->confirmation_id)
@section('page-title', 'Death Confirmation #' . $confirmation->confirmation_id)

@php
    $status = $confirmation->status; // pending | verified | rejected
    $student = $confirmation->student;
@endphp

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .dcsh-wrap {
        --bg:#f5f6fa; --card:#fff; --ink:#1a2238; --ink-soft:#5b6479; --ink-faint:#8a92a6;
        --border:#e8eaf0; --border-soft:#f0f2f7;
        --primary:#2563eb; --primary-tint:#eef3ff; --primary-dark:#1d4ed8;
        --success:#15803d; --success-tint:#e8f6ee;
        --amber:#b45309; --amber-tint:#fdf1de;
        --danger:#b91c1c; --danger-tint:#fdeaea;
        --purple:#6d28d9; --purple-tint:#f0e9fd;
        --shadow:0 1px 2px rgba(20,28,55,.04), 0 4px 16px rgba(20,28,55,.04);
        font-family:'Inter',-apple-system,sans-serif; color:var(--ink); line-height:1.55;
    }
    .dcsh-wrap *,.dcsh-wrap *::before,.dcsh-wrap *::after{box-sizing:border-box}

    .dcsh-back{display:inline-flex;align-items:center;gap:6px;color:var(--primary);text-decoration:none;font-size:14px;font-weight:500;padding:8px 12px;margin:0 0 16px -12px;border-radius:10px}
    .dcsh-back:hover{background:var(--primary-tint)}

    .dcsh-card{background:#fff;border-radius:16px;box-shadow:var(--shadow);margin-bottom:18px;overflow:hidden}
    .dcsh-card-head{display:flex;align-items:center;gap:10px;padding:16px 22px;border-bottom:1px solid var(--border-soft)}
    .dcsh-card-head-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:14px;background:var(--primary-tint);color:var(--primary)}
    .dcsh-card-head-icon.green{background:var(--success-tint);color:var(--success)}
    .dcsh-card-head-icon.purple{background:var(--purple-tint);color:var(--purple)}
    .dcsh-card-head h3{margin:0;font-size:15px;font-weight:700;color:var(--ink)}
    .dcsh-card-body{padding:22px}

    .dcsh-pill{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:4px 10px;border-radius:999px;text-transform:uppercase;letter-spacing:.04em}
    .pill-verified{background:var(--success-tint);color:var(--success)}
    .pill-pending{background:var(--amber-tint);color:var(--amber)}
    .pill-rejected{background:var(--danger-tint);color:var(--danger)}
    .pill-sensitive{background:var(--ink);color:#fff}

    .dcsh-grid{display:grid;grid-template-columns:1fr 380px;gap:18px;align-items:start}
    @media (max-width:1000px){.dcsh-grid{grid-template-columns:1fr}}

    .dcsh-hero{padding:26px 30px;position:relative}
    .dcsh-hero::before{content:"";position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--success),#22c55e)}
    .dcsh-hero.pending::before{background:linear-gradient(90deg,var(--amber),#f59e0b)}
    .dcsh-hero.rejected::before{background:linear-gradient(90deg,var(--danger),#ef4444)}
    .dcsh-hero-pills{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
    .dcsh-hero h1{font-family:'Inter',Georgia,sans-serif;font-weight:600;font-size:28px;letter-spacing:-.018em;margin:0 0 8px;color:var(--ink)}
    .dcsh-hero p{color:var(--ink-soft);font-size:14px;margin:0}

    .dcsh-info-row{display:flex;align-items:flex-start;padding:14px 0;border-bottom:1px solid var(--border-soft)}
    .dcsh-info-row:last-child{border-bottom:none;padding-bottom:0}
    .dcsh-info-row:first-child{padding-top:0}
    .dcsh-info-icon{width:38px;height:38px;border-radius:10px;background:var(--primary-tint);color:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:15px;margin-right:14px}
    .dcsh-info-icon.purple{background:var(--purple-tint);color:var(--purple)}
    .dcsh-info-icon.green{background:var(--success-tint);color:var(--success)}
    .dcsh-info-icon.amber{background:var(--amber-tint);color:var(--amber)}
    .dcsh-info-icon.danger{background:var(--danger-tint);color:var(--danger)}
    .dcsh-info-content{flex:1;min-width:0}
    .dcsh-info-label{font-size:11px;font-weight:700;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px}
    .dcsh-info-value{font-size:14px;line-height:1.5;word-break:break-word;color:var(--ink)}
    .dcsh-info-value.large{font-size:15px;font-weight:500}
    .dcsh-info-value a{color:var(--primary);text-decoration:none}

    .dcsh-desc{background:var(--bg);border:1px solid var(--border-soft);border-radius:10px;padding:14px 16px;font-size:14px;line-height:1.6;color:var(--ink)}

    .dcsh-timeline{padding:4px 0}
    .dcsh-timeline-item{display:flex;gap:12px;padding-bottom:18px;position:relative}
    .dcsh-timeline-item:last-child{padding-bottom:0}
    .dcsh-timeline-item:not(:last-child)::before{content:"";position:absolute;left:7px;top:24px;bottom:0;width:2px;background:var(--border)}
    .dcsh-timeline-dot{width:16px;height:16px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:4px;box-shadow:0 0 0 4px var(--primary-tint)}
    .dcsh-timeline-dot.green{background:var(--success);box-shadow:0 0 0 4px var(--success-tint)}
    .dcsh-timeline-dot.amber{background:var(--amber);box-shadow:0 0 0 4px var(--amber-tint)}
    .dcsh-timeline-dot.red{background:var(--danger);box-shadow:0 0 0 4px var(--danger-tint)}
    .dcsh-timeline-dot.muted{background:#cbd5e1;box-shadow:0 0 0 4px #f1f5f9}
    .dcsh-timeline-text{flex:1}
    .dcsh-timeline-title{font-weight:600;font-size:14px;margin:0 0 2px;color:var(--ink)}
    .dcsh-timeline-meta{font-size:12px;color:var(--ink-faint);line-height:1.4}

    .dcsh-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:9px 16px;border-radius:10px;font-weight:600;font-size:13.5px;cursor:pointer;text-decoration:none;border:1.5px solid transparent;font-family:'Inter',sans-serif;transition:all .15s}
    .dcsh-btn-primary{background:var(--primary);color:#fff}
    .dcsh-btn-primary:hover{background:var(--primary-dark);color:#fff}
    .dcsh-btn-ghost{background:#fff;color:var(--ink);border-color:var(--border)}
    .dcsh-btn-ghost:hover{background:var(--bg);color:var(--ink)}

    .dcsh-notice{padding:14px 16px;border-radius:12px;display:flex;gap:12px;align-items:flex-start;margin-bottom:16px;border:1px solid}
    .dcsh-notice.lock{background:var(--success-tint);border-color:#BBF7D0}
    .dcsh-notice.lock i{color:var(--success);font-size:18px;flex-shrink:0;margin-top:1px}
    .dcsh-notice.lock h4{color:var(--success);margin:0 0 4px;font-size:14px;font-weight:700}
    .dcsh-notice.lock p{margin:0;font-size:13px;color:var(--ink-soft);line-height:1.5}
    .dcsh-notice.editable{background:var(--amber-tint);border-color:#FDE68A}
    .dcsh-notice.editable i{color:var(--amber);font-size:18px;flex-shrink:0;margin-top:1px}
    .dcsh-notice.editable h4{color:var(--amber);margin:0 0 4px;font-size:14px;font-weight:700}
    .dcsh-notice.editable p{margin:0;font-size:13px;color:var(--ink-soft);line-height:1.5}
    .dcsh-notice-actions{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap}

    .dcsh-reject{background:var(--danger-tint);border:1px solid #fecaca;border-radius:12px;padding:14px 16px;margin-bottom:16px}
    .dcsh-reject h4{color:var(--danger);font-size:14px;font-weight:700;margin:0 0 6px;display:flex;align-items:center;gap:6px}
    .dcsh-reject p.lead{margin:0 0 10px;font-size:13px;color:var(--ink-soft);line-height:1.5}
    .dcsh-reject .reason{background:#fff;border-radius:8px;padding:12px 14px;font-size:13px;line-height:1.55;color:var(--ink);margin:0 0 10px;border-left:3px solid var(--danger)}
    .dcsh-reject .meta{margin:0 0 10px;font-size:11.5px;color:var(--ink-faint)}
    .dcsh-reject .actions{display:flex;gap:8px;flex-wrap:wrap}

    .dcsh-help{background:linear-gradient(135deg,var(--primary),#3b82f6);color:#fff;border-radius:16px;overflow:hidden}
    .dcsh-help-body{padding:18px 22px}
    .dcsh-help h3{margin:0 0 6px;color:#fff;font-size:15px;font-weight:700}
    .dcsh-help p{margin:0 0 12px;font-size:13px;color:rgba(255,255,255,.85);line-height:1.5}
    .dcsh-help-tel{display:inline-flex;align-items:center;gap:6px;background:#fff;color:var(--primary);padding:8px 14px;border-radius:10px;text-decoration:none;font-weight:600;font-size:13.5px}
    .dcsh-help-tel:hover{color:var(--primary-dark)}
</style>
@endpush

@section('content')
<div class="dcsh-wrap">

    <a href="{{ route('nok.submissions.index') }}" class="dcsh-back">
        <i class="bi bi-arrow-left"></i> Back to my submissions
    </a>

    @if (session('status'))
        <div class="dcsh-notice lock" style="margin-bottom:16px">
            <i class="bi bi-check-circle-fill"></i>
            <div><h4 style="color:var(--success)">Saved</h4><p>{{ session('status') }}</p></div>
        </div>
    @endif
    @if (session('error'))
        <div class="dcsh-reject">
            <h4><i class="bi bi-exclamation-triangle-fill"></i> Action not allowed</h4>
            <p class="lead">{{ session('error') }}</p>
        </div>
    @endif

    {{-- HERO --}}
    <section class="dcsh-card dcsh-hero {{ $status }}">
        <div class="dcsh-hero-pills">
            @if ($status === 'verified')
                <span class="dcsh-pill pill-verified"><i class="bi bi-shield-check"></i> Verified</span>
            @elseif ($status === 'pending')
                <span class="dcsh-pill pill-pending"><i class="bi bi-hourglass-split"></i> Pending Review</span>
            @else
                <span class="dcsh-pill pill-rejected"><i class="bi bi-x-circle"></i> Needs Revision</span>
            @endif
            <span class="dcsh-pill pill-sensitive"><i class="bi bi-shield-lock"></i> Sensitive</span>
        </div>
        <h1>Death Confirmation</h1>
        @if ($student)
            <p>For: <strong>{{ $student->full_name ?? $student->first_name . ' ' . $student->last_name }}</strong>
                @if ($student->student_id) · Matric: {{ $student->student_id }} @endif
                · Submitted {{ $confirmation->date_triggered?->diffForHumans() }}</p>
        @endif
    </section>

    {{-- STATE-AWARE NOTICE --}}
    @if ($status === 'verified')
        <div class="dcsh-notice lock">
            <i class="bi bi-shield-fill-check"></i>
            <div>
                <h4>Confirmation verified — Inna lillahi wa inna ilayhi raji'un</h4>
                <p>The death confirmation has been verified by our welfare team. Any released last messages will appear below. If you need to update information, please contact the welfare office directly.</p>
            </div>
        </div>

    @elseif ($status === 'pending')
        <div class="dcsh-notice editable">
            <i class="bi bi-clock-history"></i>
            <div style="flex:1">
                <h4>Awaiting welfare team review</h4>
                <p>We've received your submission and our team will review it as soon as possible. You can still update the documentation while it's pending if needed.</p>
                <div class="dcsh-notice-actions">
                    <a href="{{ route('nok.death.edit', $confirmation->confirmation_id) }}" class="dcsh-btn dcsh-btn-primary" style="padding:6px 12px;font-size:12.5px">
                        <i class="bi bi-pencil"></i> Update Documentation
                    </a>
                </div>
            </div>
        </div>

    @else {{-- rejected --}}
        <div class="dcsh-reject">
            <h4><i class="bi bi-info-circle-fill"></i> Additional documentation needed</h4>
            <p class="lead">We're deeply sorry for your loss. Our admin team reviewed your submission with care but needs additional documentation before the confirmation can be verified. You can update the submission below.</p>
            @if ($confirmation->admin_comments)
                <div class="reason">
                    <strong>Admin feedback:</strong> {{ $confirmation->admin_comments }}
                </div>
            @endif
            <p class="meta">
                Reviewed on {{ $confirmation->date_confirmed?->format('d M Y, H:i') ?? 'recent' }}
            </p>
            <div class="actions">
                <a href="{{ route('nok.death.edit', $confirmation->confirmation_id) }}" class="dcsh-btn dcsh-btn-primary">
                    <i class="bi bi-pencil-square"></i> Update Documentation
                </a>
                <a href="tel:+60361964000" class="dcsh-btn dcsh-btn-ghost">
                    <i class="bi bi-telephone"></i> Call Welfare Office
                </a>
            </div>
        </div>
    @endif

    <div class="dcsh-grid">
        {{-- LEFT --}}
        <div>
            <section class="dcsh-card">
                <div class="dcsh-card-head">
                    <div class="dcsh-card-head-icon"><i class="bi bi-file-medical-fill"></i></div>
                    <h3>Submitted Details</h3>
                </div>
                <div class="dcsh-card-body">
                    @if ($student)
                        <div class="dcsh-info-row">
                            <div class="dcsh-info-icon"><i class="bi bi-person-fill"></i></div>
                            <div class="dcsh-info-content">
                                <div class="dcsh-info-label">Deceased Student</div>
                                <div class="dcsh-info-value large">
                                    {{ $student->full_name ?? $student->first_name . ' ' . $student->last_name }}
                                    @if ($student->student_id) · {{ $student->student_id }} @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="dcsh-info-row">
                        <div class="dcsh-info-icon amber"><i class="bi bi-calendar-event"></i></div>
                        <div class="dcsh-info-content">
                            <div class="dcsh-info-label">Date Submitted</div>
                            <div class="dcsh-info-value large">{{ $confirmation->date_triggered?->format('d M Y, H:i') }}</div>
                        </div>
                    </div>

                    @if ($confirmation->admin_comments)
                        <div class="dcsh-info-row">
                            <div class="dcsh-info-icon purple"><i class="bi bi-file-earmark-medical"></i></div>
                            <div class="dcsh-info-content">
                                <div class="dcsh-info-label">{{ $status === 'rejected' ? 'Admin Feedback' : 'Notes' }}</div>
                                <div class="dcsh-desc">{{ $confirmation->admin_comments }}</div>
                            </div>
                        </div>
                    @endif

                    @if ($confirmation->media_file_path)
                        <div class="dcsh-info-row">
                            <div class="dcsh-info-icon green"><i class="bi bi-paperclip"></i></div>
                            <div class="dcsh-info-content">
                                <div class="dcsh-info-label">Supporting Documentation</div>
                                <div class="dcsh-info-value">
                                    <i class="bi bi-file-earmark-text"></i> {{ $confirmation->media_file_name ?? 'Document' }}
                                    @if ($confirmation->media_file_size)
                                        <small style="color:var(--ink-faint)">({{ number_format($confirmation->media_file_size / 1024, 0) }} KB)</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($status === 'verified' && $confirmation->blockchain_reference)
                        <div class="dcsh-info-row">
                            <div class="dcsh-info-icon green"><i class="bi bi-shield-fill-check"></i></div>
                            <div class="dcsh-info-content">
                                <div class="dcsh-info-label">Blockchain Reference</div>
                                <div class="dcsh-info-value" style="font-family:ui-monospace,monospace;font-size:12px">{{ \Illuminate\Support\Str::limit($confirmation->blockchain_reference, 30, '...') }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Released LDMS (verified only) --}}
            @if ($status === 'verified' && $releasedLdms->count() > 0)
                <section class="dcsh-card">
                    <div class="dcsh-card-head">
                        <div class="dcsh-card-head-icon purple"><i class="bi bi-envelope-paper-heart"></i></div>
                        <h3>Released Last Messages</h3>
                    </div>
                    <div class="dcsh-card-body">
                        @foreach ($releasedLdms as $ldms)
                            <a href="{{ route('nok.ldms.show', $ldms->ldms_id) }}" style="display:block;padding:14px;background:var(--bg);border-radius:10px;margin-bottom:8px;text-decoration:none">
                                <div style="display:flex;align-items:center;gap:10px">
                                    <i class="bi bi-envelope-paper-fill" style="color:var(--purple);font-size:18px"></i>
                                    <div style="flex:1">
                                        <div style="font-weight:600;color:var(--ink)">{{ $ldms->title ?? 'Message' }}</div>
                                        <div style="font-size:12px;color:var(--ink-faint)">Released {{ $ldms->date_triggered?->diffForHumans() }}</div>
                                    </div>
                                    <i class="bi bi-arrow-right" style="color:var(--ink-faint)"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        {{-- RIGHT SIDEBAR --}}
        <div>
            <section class="dcsh-card">
                <div class="dcsh-card-head">
                    <div class="dcsh-card-head-icon"><i class="bi bi-list-check"></i></div>
                    <h3>Status Timeline</h3>
                </div>
                <div class="dcsh-card-body">
                    <div class="dcsh-timeline">
                        <div class="dcsh-timeline-item">
                            <div class="dcsh-timeline-dot green"></div>
                            <div class="dcsh-timeline-text">
                                <p class="dcsh-timeline-title">Submitted by NOK</p>
                                <div class="dcsh-timeline-meta">{{ $confirmation->date_triggered?->format('d M Y, H:i') }}</div>
                            </div>
                        </div>

                        @if ($status === 'pending')
                            <div class="dcsh-timeline-item">
                                <div class="dcsh-timeline-dot amber"></div>
                                <div class="dcsh-timeline-text">
                                    <p class="dcsh-timeline-title">Awaiting Welfare Review</p>
                                    <div class="dcsh-timeline-meta">Our team will respond as soon as possible</div>
                                </div>
                            </div>
                            <div class="dcsh-timeline-item">
                                <div class="dcsh-timeline-dot muted"></div>
                                <div class="dcsh-timeline-text">
                                    <p class="dcsh-timeline-title">Decision</p>
                                    <div class="dcsh-timeline-meta">Pending</div>
                                </div>
                            </div>
                        @elseif ($status === 'verified')
                            <div class="dcsh-timeline-item">
                                <div class="dcsh-timeline-dot green"></div>
                                <div class="dcsh-timeline-text">
                                    <p class="dcsh-timeline-title">Verified</p>
                                    <div class="dcsh-timeline-meta">{{ $confirmation->date_confirmed?->format('d M Y, H:i') }}</div>
                                </div>
                            </div>
                            <div class="dcsh-timeline-item">
                                <div class="dcsh-timeline-dot green"></div>
                                <div class="dcsh-timeline-text">
                                    <p class="dcsh-timeline-title">Last Messages Released</p>
                                    <div class="dcsh-timeline-meta">{{ $releasedLdms->count() }} message(s) available</div>
                                </div>
                            </div>
                        @else {{-- rejected --}}
                            <div class="dcsh-timeline-item">
                                <div class="dcsh-timeline-dot red"></div>
                                <div class="dcsh-timeline-text">
                                    <p class="dcsh-timeline-title">Rejected — needs revision</p>
                                    <div class="dcsh-timeline-meta">{{ $confirmation->date_confirmed?->format('d M Y, H:i') }}</div>
                                </div>
                            </div>
                            <div class="dcsh-timeline-item">
                                <div class="dcsh-timeline-dot muted"></div>
                                <div class="dcsh-timeline-text">
                                    <p class="dcsh-timeline-title">Awaiting Your Update</p>
                                    <div class="dcsh-timeline-meta">Please re-upload documentation</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Help card --}}
            <section class="dcsh-help">
                <div class="dcsh-help-body">
                    <h3><i class="bi bi-headset"></i> Need help?</h3>
                    <p>Our welfare office is here to support you during this difficult time. Call us anytime for guidance.</p>
                    <a href="tel:+60361964000" class="dcsh-help-tel">
                        <i class="bi bi-telephone-fill"></i> +60 3 6196 4000
                    </a>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
