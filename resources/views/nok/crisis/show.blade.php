@extends('layouts.nok')
@section('title', 'Report #' . $report->report_id)

@php
    $status = $report->report_status; // pending | verified | rejected
    $crisis = $report->crisis;

    // Decode crisis_details JSON for sub_category / immediate_actions / incident_at
    $details = [];
    if ($crisis && $crisis->crisis_details) {
        $decoded = json_decode($crisis->crisis_details, true);
        if (is_array($decoded)) $details = $decoded;
    }

    $impactLabel = ucfirst($crisis->impact_level ?? 'medium');
    $impactClass = match($crisis->impact_level ?? 'medium') {
        'critical' => 'pill-critical',
        'high'     => 'pill-high',
        'medium'   => 'pill-medium',
        default    => 'pill-low',
    };

    $statusClass = match($status) {
        'verified' => 'verified',
        'rejected' => 'rejected',
        default    => 'pending',
    };

    $statusPill = match($status) {
        'verified' => ['class' => 'pill-verified', 'icon' => 'bi-shield-fill-check', 'label' => 'Verified'],
        'rejected' => ['class' => 'pill-rejected', 'icon' => 'bi-x-circle-fill', 'label' => 'Rejected'],
        default    => ['class' => 'pill-pending',  'icon' => 'bi-clock-fill', 'label' => 'Pending Review'],
    };
@endphp

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .crsh-wrap {
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
    .crsh-wrap *,.crsh-wrap *::before,.crsh-wrap *::after{box-sizing:border-box}

    .crsh-back{display:inline-flex;align-items:center;gap:6px;color:var(--primary);text-decoration:none;font-size:14px;font-weight:500;padding:8px 12px;margin:0 0 16px -12px;border-radius:10px;transition:background .15s}
    .crsh-back:hover{background:var(--primary-tint);color:var(--primary-dark)}

    .crsh-card{background:#fff;border-radius:16px;box-shadow:var(--shadow);margin-bottom:18px;overflow:hidden}
    .crsh-card-head{display:flex;align-items:center;gap:10px;padding:16px 22px;border-bottom:1px solid var(--border-soft)}
    .crsh-card-head-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:14px;background:var(--primary-tint);color:var(--primary)}
    .crsh-card-head-icon.green{background:var(--success-tint);color:var(--success)}
    .crsh-card-head-icon.amber{background:var(--amber-tint);color:var(--amber)}
    .crsh-card-head-icon.purple{background:var(--purple-tint);color:var(--purple)}
    .crsh-card-head-icon.danger{background:var(--danger-tint);color:var(--danger)}
    .crsh-card-head h3{margin:0;font-size:15px;font-weight:700;color:var(--ink);font-family:'Inter',sans-serif}
    .crsh-card-body{padding:22px}

    .crsh-pill{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:4px 10px;border-radius:999px;text-transform:uppercase;letter-spacing:.04em}
    .pill-verified{background:var(--success-tint);color:var(--success)}
    .pill-pending{background:var(--amber-tint);color:var(--amber)}
    .pill-rejected{background:var(--danger-tint);color:var(--danger)}
    .pill-low{background:#dbeafe;color:#1e40af}
    .pill-medium{background:var(--primary-tint);color:var(--primary)}
    .pill-high{background:var(--amber-tint);color:var(--amber)}
    .pill-critical{background:var(--danger-tint);color:var(--danger)}
    .pill-nok{background:#fff7ed;color:#c2410c;border:1px solid #fed7aa}

    .crsh-grid{display:grid;grid-template-columns:1fr 380px;gap:18px;align-items:start}
    @media (max-width:1000px){.crsh-grid{grid-template-columns:1fr}}

    .crsh-hero{padding:26px 30px;position:relative}
    .crsh-hero::before{content:"";position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--success),#22c55e)}
    .crsh-hero.pending::before{background:linear-gradient(90deg,var(--amber),#f59e0b)}
    .crsh-hero.rejected::before{background:linear-gradient(90deg,var(--danger),#ef4444)}
    .crsh-hero-pills{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
    .crsh-hero h1{font-family:'Inter',Georgia,serif;font-weight:600;font-size:30px;letter-spacing:-.018em;margin:0 0 10px;line-height:1.15;color:var(--ink)}
    .crsh-hero-meta{display:flex;flex-wrap:wrap;gap:10px 20px;color:var(--ink-soft);font-size:13px}
    .crsh-hero-meta span{display:inline-flex;align-items:center;gap:5px}
    .crsh-hero-meta i{font-size:13px;color:var(--ink-faint)}

    .crsh-info-row{display:flex;align-items:flex-start;padding:14px 0;border-bottom:1px solid var(--border-soft)}
    .crsh-info-row:last-child{border-bottom:none;padding-bottom:0}
    .crsh-info-row:first-child{padding-top:0}
    .crsh-info-icon{width:38px;height:38px;border-radius:10px;background:var(--primary-tint);color:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:15px;margin-right:14px}
    .crsh-info-icon.green{background:var(--success-tint);color:var(--success)}
    .crsh-info-icon.amber{background:var(--amber-tint);color:var(--amber)}
    .crsh-info-icon.purple{background:var(--purple-tint);color:var(--purple)}
    .crsh-info-content{flex:1;min-width:0}
    .crsh-info-label{font-size:11px;font-weight:700;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px}
    .crsh-info-value{font-size:14px;line-height:1.5;word-break:break-word;color:var(--ink)}
    .crsh-info-value.large{font-size:15px;font-weight:500}

    .crsh-desc-box{background:var(--bg);border:1px solid var(--border-soft);border-radius:10px;padding:14px 16px;font-size:14px;line-height:1.65;color:var(--ink);white-space:pre-wrap}

    /* Filed on behalf of - special card */
    .crsh-student-card{
        background:linear-gradient(135deg,#fff7ed,#ffedd5);
        border:1px solid #fed7aa;
        border-radius:12px;
        padding:16px 18px;
        margin-bottom:18px;
        display:flex;align-items:center;gap:14px;
    }
    .crsh-student-avatar{
        width:48px;height:48px;border-radius:50%;
        background:#c2410c;color:#fff;
        display:flex;align-items:center;justify-content:center;
        font-weight:700;font-size:18px;
        flex-shrink:0;
        font-family:'Inter',serif;
    }
    .crsh-student-info{flex:1;min-width:0}
    .crsh-student-label{font-size:11px;font-weight:700;color:#9a3412;text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px}
    .crsh-student-name{font-size:15px;font-weight:600;color:#1a2238;line-height:1.3}
    .crsh-student-meta{font-size:12px;color:#7c2d12;margin-top:2px}

    /* Notices */
    .crsh-notice{padding:16px 18px;border-radius:12px;display:flex;gap:14px;align-items:flex-start;margin-bottom:18px;border:1px solid}
    .crsh-notice.lock{background:var(--success-tint);border-color:#BBF7D0}
    .crsh-notice.lock i{color:var(--success);font-size:20px;flex-shrink:0;margin-top:1px}
    .crsh-notice.lock h4{color:var(--success);margin:0 0 5px;font-size:15px;font-weight:700}
    .crsh-notice.lock p{margin:0;font-size:13.5px;color:var(--ink-soft);line-height:1.55}
    .crsh-notice.lock a{color:var(--success);text-decoration:underline;font-weight:600}

    .crsh-notice.editable{background:var(--amber-tint);border-color:#FDE68A}
    .crsh-notice.editable i{color:var(--amber);font-size:20px;flex-shrink:0;margin-top:1px}
    .crsh-notice.editable h4{color:var(--amber);margin:0 0 5px;font-size:15px;font-weight:700}
    .crsh-notice.editable p{margin:0;font-size:13.5px;color:var(--ink-soft);line-height:1.55}

    .crsh-notice.reject{background:var(--danger-tint);border-color:#FECACA;padding:20px 22px}
    .crsh-notice.reject .reject-icon-wrap{width:42px;height:42px;border-radius:11px;background:var(--danger);color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:18px}
    .crsh-notice.reject h4{color:var(--danger);margin:0 0 6px;font-size:16px;font-weight:700}
    .crsh-notice.reject p{margin:0 0 10px;font-size:13.5px;color:var(--ink);line-height:1.55}
    .crsh-notice.reject .reason-box{background:#fff;border:1px solid #FECACA;border-radius:10px;padding:12px 14px;font-size:13.5px;color:#7f1d1d;margin:8px 0 0;line-height:1.55}

    /* Action buttons inside notices */
    .crsh-notice-actions{display:flex;gap:8px;margin-top:12px;flex-wrap:wrap}
    .crsh-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:8px 14px;border-radius:9px;font-weight:600;font-size:13px;cursor:pointer;text-decoration:none;border:1.5px solid transparent;font-family:inherit;transition:all .15s}
    .crsh-btn-primary{background:var(--primary);color:#fff}
    .crsh-btn-primary:hover{background:var(--primary-dark);color:#fff}
    .crsh-btn-ghost{background:#fff;color:var(--ink);border-color:var(--border)}
    .crsh-btn-ghost:hover{background:var(--bg);color:var(--ink)}
    .crsh-btn-danger-ghost{background:#fff;color:var(--danger);border-color:#fecaca}
    .crsh-btn-danger-ghost:hover{background:var(--danger-tint)}

    /* Timeline */
    .crsh-timeline{padding:4px 0}
    .crsh-timeline-item{display:flex;gap:14px;padding-bottom:18px;position:relative}
    .crsh-timeline-item:last-child{padding-bottom:0}
    .crsh-timeline-item:not(:last-child)::before{content:"";position:absolute;left:7px;top:24px;bottom:0;width:2px;background:var(--border)}
    .crsh-timeline-dot{width:16px;height:16px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:4px;box-shadow:0 0 0 4px var(--primary-tint)}
    .crsh-timeline-dot.green{background:var(--success);box-shadow:0 0 0 4px var(--success-tint)}
    .crsh-timeline-dot.amber{background:var(--amber);box-shadow:0 0 0 4px var(--amber-tint)}
    .crsh-timeline-dot.danger{background:var(--danger);box-shadow:0 0 0 4px var(--danger-tint)}
    .crsh-timeline-dot.muted{background:#cbd5e1;box-shadow:0 0 0 4px #f1f5f9}
    .crsh-timeline-text{flex:1;min-width:0}
    .crsh-timeline-title{font-weight:600;font-size:14px;color:var(--ink);margin:0 0 2px}
    .crsh-timeline-meta{font-size:12px;color:var(--ink-faint);line-height:1.4}

    /* Blockchain chip */
    .crsh-blockchain{background:linear-gradient(135deg,#f0e9fd,#e9d5ff);border:1px solid #d8b4fe;border-radius:12px;padding:12px 14px;display:flex;align-items:center;gap:10px}
    .crsh-blockchain-icon{width:36px;height:36px;border-radius:9px;background:var(--purple);color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:15px}
    .crsh-blockchain-info{flex:1;min-width:0}
    .crsh-blockchain-label{font-size:11px;font-weight:700;color:var(--purple);text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px}
    .crsh-blockchain-hash{font-family:ui-monospace,monospace;font-size:11px;color:#581c87;word-break:break-all}

    /* Help card */
    .crsh-help{background:linear-gradient(135deg,var(--primary),#3b82f6);color:#fff;border-radius:16px;overflow:hidden}
    .crsh-help-body{padding:18px 22px}
    .crsh-help h3{margin:0 0 6px;color:#fff;font-size:15px;font-weight:700;font-family:'Inter',sans-serif}
    .crsh-help p{margin:0;font-size:13px;color:rgba(255,255,255,.85);line-height:1.55}
    .crsh-help a{color:#fff;text-decoration:underline;font-weight:600}
</style>
@endpush

@section('content')
<div class="crsh-wrap">

    <a href="{{ route('nok.submissions.index') }}" class="crsh-back">
        <i class="bi bi-arrow-left"></i> Back to my submissions
    </a>

    @if (session('status'))
        <div class="crsh-notice lock" style="margin-bottom:16px">
            <i class="bi bi-check-circle-fill"></i>
            <div><h4>Success</h4><p>{{ session('status') }}</p></div>
        </div>
    @endif

    {{-- HERO --}}
    <section class="crsh-card crsh-hero {{ $statusClass }}">
        <div class="crsh-hero-pills">
            <span class="crsh-pill {{ $statusPill['class'] }}">
                <i class="bi {{ $statusPill['icon'] }}"></i> {{ $statusPill['label'] }}
            </span>
            <span class="crsh-pill {{ $impactClass }}">
                <i class="bi bi-info-circle-fill"></i> {{ $impactLabel }} priority
            </span>
            <span class="crsh-pill pill-nok">
                <i class="bi bi-person-badge-fill"></i> Submitted by you (NOK)
            </span>
        </div>
        <h1>{{ ucwords(str_replace('_', ' ', $crisis->crisis_type ?? 'Crisis Report')) }}</h1>
        <div class="crsh-hero-meta">
            @if ($crisis?->location)
                <span><i class="bi bi-geo-alt-fill"></i> {{ $crisis->location }}</span>
            @endif
            <span><i class="bi bi-calendar-event"></i> Reported {{ $report->date_reported?->format('d M Y') }}</span>
            @if ($status === 'verified')
                <span><i class="bi bi-shield-check"></i> Blockchain verified</span>
            @endif
        </div>
    </section>

    {{-- Filed on behalf of - prominent card --}}
    @if ($report->student)
        @php
            $studentInitials = strtoupper(substr($report->student->first_name ?? '', 0, 1) . substr($report->student->last_name ?? '', 0, 1));
            if (empty($studentInitials)) {
                $studentInitials = strtoupper(substr($report->student->full_name ?? 'S', 0, 1));
            }
        @endphp
        <div class="crsh-student-card">
            <div class="crsh-student-avatar">{{ $studentInitials }}</div>
            <div class="crsh-student-info">
                <div class="crsh-student-label">Filed on behalf of</div>
                <div class="crsh-student-name">{{ $report->student->full_name ?? 'Student' }}</div>
                @if ($report->student->student_id)
                    <div class="crsh-student-meta">Matric: {{ $report->student->student_id }}</div>
                @endif
            </div>
        </div>
    @endif

    {{-- STATE-AWARE NOTICE --}}
    @if ($status === 'verified')
        <div class="crsh-notice lock">
            <i class="bi bi-shield-fill-check"></i>
            <div>
                <h4>Report verified — and that's a good thing</h4>
                <p>
                    This report has been verified by IIUM admin and recorded on the blockchain. Donors can now contribute to this case.
                    To preserve trust, the report can't be edited after verification. If something has changed, contact
                    <a href="mailto:welfare@iium.edu.my">welfare@iium.edu.my</a>.
                </p>
            </div>
        </div>
    @elseif ($status === 'pending')
        <div class="crsh-notice editable">
            <i class="bi bi-hourglass-split"></i>
            <div style="flex:1">
                <h4>Awaiting admin review — you can still make changes</h4>
                <p>Your submission is in the admin queue. While it's pending, you can update the details or delete it. Verification usually takes 1–2 business days.</p>
                <div class="crsh-notice-actions">
                    <a href="{{ route('nok.crisis.edit', $report->report_id) }}" class="crsh-btn crsh-btn-primary">
                        <i class="bi bi-pencil-fill"></i> Edit Report
                    </a>
                    <form method="POST" action="{{ route('nok.crisis.destroy', $report->report_id) }}" style="display:inline" onsubmit="return confirm('Permanently delete this pending report? This cannot be undone.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="crsh-btn crsh-btn-danger-ghost">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @elseif ($status === 'rejected')
        <div class="crsh-notice reject">
            <div class="reject-icon-wrap"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div style="flex:1">
                <h4>This submission needs attention</h4>
                <p>The administrator reviewed your submission and asked for changes. Please update the report below and resubmit for re-review.</p>
                @if ($report->admin_remarks)
                    <div class="reason-box">
                        <strong>Reviewer's notes:</strong><br>
                        {{ $report->admin_remarks }}
                    </div>
                @endif
                <div class="crsh-notice-actions" style="margin-top:14px">
                    <a href="{{ route('nok.crisis.edit', $report->report_id) }}" class="crsh-btn crsh-btn-primary">
                        <i class="bi bi-arrow-clockwise"></i> Edit & Resubmit
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="crsh-grid">
        {{-- LEFT: Case info --}}
        <div>
            <section class="crsh-card">
                <div class="crsh-card-head">
                    <div class="crsh-card-head-icon"><i class="bi bi-info-circle-fill"></i></div>
                    <h3>Case Information</h3>
                </div>
                <div class="crsh-card-body">

                    <div class="crsh-info-row">
                        <div class="crsh-info-icon"><i class="bi bi-file-text-fill"></i></div>
                        <div class="crsh-info-content">
                            <div class="crsh-info-label">Description</div>
                            <div class="crsh-desc-box">{{ $report->report_description }}</div>
                        </div>
                    </div>

                    @if (!empty($details['sub_category']))
                        <div class="crsh-info-row">
                            <div class="crsh-info-icon purple"><i class="bi bi-tag-fill"></i></div>
                            <div class="crsh-info-content">
                                <div class="crsh-info-label">Sub-category</div>
                                <div class="crsh-info-value large">{{ $details['sub_category'] }}</div>
                            </div>
                        </div>
                    @endif

                    @if (!empty($details['immediate_actions']))
                        <div class="crsh-info-row">
                            <div class="crsh-info-icon amber"><i class="bi bi-lightning-charge-fill"></i></div>
                            <div class="crsh-info-content">
                                <div class="crsh-info-label">Immediate actions taken</div>
                                <div class="crsh-desc-box">{{ $details['immediate_actions'] }}</div>
                            </div>
                        </div>
                    @endif

                    @if (!empty($details['incident_at']))
                        <div class="crsh-info-row">
                            <div class="crsh-info-icon"><i class="bi bi-clock-fill"></i></div>
                            <div class="crsh-info-content">
                                <div class="crsh-info-label">Incident occurred</div>
                                <div class="crsh-info-value large">
                                    {{ \Carbon\Carbon::parse($details['incident_at'])->format('d M Y, h:i A') }}
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </section>

            {{-- Blockchain proof (verified only) --}}
            @if ($status === 'verified' && $report->blockchain_hash)
                <section class="crsh-card">
                    <div class="crsh-card-head">
                        <div class="crsh-card-head-icon purple"><i class="bi bi-link-45deg"></i></div>
                        <h3>Blockchain Proof</h3>
                    </div>
                    <div class="crsh-card-body">
                        <p style="margin:0 0 12px;font-size:13px;color:var(--ink-soft);line-height:1.55">
                            This report has been cryptographically anchored on the blockchain. Any attempt to alter it would break the hash chain, making tampering immediately detectable.
                        </p>
                        <div class="crsh-blockchain">
                            <div class="crsh-blockchain-icon"><i class="bi bi-shield-lock-fill"></i></div>
                            <div class="crsh-blockchain-info">
                                <div class="crsh-blockchain-label">SHA-256 Hash</div>
                                <div class="crsh-blockchain-hash">{{ $report->blockchain_hash }}</div>
                            </div>
                        </div>
                    </div>
                </section>
            @endif
        </div>

        {{-- RIGHT SIDEBAR --}}
        <div>
            {{-- Status Timeline --}}
            <section class="crsh-card">
                <div class="crsh-card-head">
                    <div class="crsh-card-head-icon"><i class="bi bi-list-check"></i></div>
                    <h3>Status Timeline</h3>
                </div>
                <div class="crsh-card-body">
                    <div class="crsh-timeline">
                        <div class="crsh-timeline-item">
                            <div class="crsh-timeline-dot"></div>
                            <div class="crsh-timeline-text">
                                <p class="crsh-timeline-title">Submitted by you</p>
                                <div class="crsh-timeline-meta">{{ $report->date_reported?->format('d M Y, h:i A') }}</div>
                            </div>
                        </div>

                        @if ($status === 'verified')
                            <div class="crsh-timeline-item">
                                <div class="crsh-timeline-dot green"></div>
                                <div class="crsh-timeline-text">
                                    <p class="crsh-timeline-title">Admin reviewed</p>
                                    <div class="crsh-timeline-meta">{{ $report->verified_at?->format('d M Y, h:i A') }}</div>
                                </div>
                            </div>
                            <div class="crsh-timeline-item">
                                <div class="crsh-timeline-dot green"></div>
                                <div class="crsh-timeline-text">
                                    <p class="crsh-timeline-title">Verified & published</p>
                                    <div class="crsh-timeline-meta">Approved by administrator</div>
                                </div>
                            </div>
                        @elseif ($status === 'rejected')
                            <div class="crsh-timeline-item">
                                <div class="crsh-timeline-dot danger"></div>
                                <div class="crsh-timeline-text">
                                    <p class="crsh-timeline-title">Admin reviewed</p>
                                    <div class="crsh-timeline-meta">{{ $report->verified_at?->format('d M Y, h:i A') }}</div>
                                </div>
                            </div>
                            <div class="crsh-timeline-item">
                                <div class="crsh-timeline-dot danger"></div>
                                <div class="crsh-timeline-text">
                                    <p class="crsh-timeline-title">Rejected</p>
                                    <div class="crsh-timeline-meta">See reviewer's notes above</div>
                                </div>
                            </div>
                        @else
                            <div class="crsh-timeline-item">
                                <div class="crsh-timeline-dot amber"></div>
                                <div class="crsh-timeline-text">
                                    <p class="crsh-timeline-title">Admin review</p>
                                    <div class="crsh-timeline-meta">In progress — usually 1–2 business days</div>
                                </div>
                            </div>
                            <div class="crsh-timeline-item">
                                <div class="crsh-timeline-dot muted"></div>
                                <div class="crsh-timeline-text">
                                    <p class="crsh-timeline-title">Verification</p>
                                    <div class="crsh-timeline-meta">Pending</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Help card --}}
            <section class="crsh-help">
                <div class="crsh-help-body">
                    <h3><i class="bi bi-life-preserver"></i> Need assistance?</h3>
                    <p>If you have questions about this submission or need to update information, contact the IIUM welfare office at <a href="mailto:welfare@iium.edu.my">welfare@iium.edu.my</a> or call +60 3 6196 4000.</p>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
