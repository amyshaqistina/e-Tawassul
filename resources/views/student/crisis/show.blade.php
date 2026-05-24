@extends('layouts.student')
@section('title', 'Report #' . $report->report_id)
@section('page-title', 'Crisis Report #' . $report->report_id)

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
        font-family:'Inter',-apple-system,sans-serif;
        color:var(--ink); line-height:1.55;
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
    .pill-pending {background:var(--amber-tint);color:var(--amber)}
    .pill-rejected{background:var(--danger-tint);color:var(--danger)}
    .pill-low     {background:#e0f2fe;color:#075985}
    .pill-medium  {background:var(--primary-tint);color:var(--primary)}
    .pill-high    {background:var(--amber-tint);color:var(--amber)}
    .pill-critical{background:var(--danger-tint);color:var(--danger)}

    .crsh-grid{display:grid;grid-template-columns:1fr 380px;gap:18px;align-items:start}
    @media (max-width:1000px){.crsh-grid{grid-template-columns:1fr}}

    .crsh-hero{padding:26px 30px;position:relative}
    .crsh-hero::before{content:"";position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--success),#22c55e)}
    .crsh-hero.pending::before{background:linear-gradient(90deg,var(--amber),#f59e0b)}
    .crsh-hero.rejected::before{background:linear-gradient(90deg,var(--danger),#ef4444)}
    .crsh-hero-pills{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
    .crsh-hero h1{font-family:'Fraunces',Georgia,serif;font-weight:600;font-size:30px;letter-spacing:-.018em;margin:0 0 10px;line-height:1.15;color:var(--ink)}
    .crsh-hero-meta{display:flex;flex-wrap:wrap;gap:10px 20px;color:var(--ink-soft);font-size:13px}
    .crsh-hero-meta span{display:inline-flex;align-items:center;gap:5px}
    .crsh-hero-meta i{font-size:13px;color:var(--ink-faint)}

    .crsh-info-row{display:flex;align-items:flex-start;padding:14px 0;border-bottom:1px solid var(--border-soft)}
    .crsh-info-row:last-child{border-bottom:none;padding-bottom:0}
    .crsh-info-row:first-child{padding-top:0}
    .crsh-info-icon{width:38px;height:38px;border-radius:10px;background:var(--primary-tint);color:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:15px;margin-right:14px}
    .crsh-info-icon.amber{background:var(--amber-tint);color:var(--amber)}
    .crsh-info-icon.purple{background:var(--purple-tint);color:var(--purple)}
    .crsh-info-icon.green{background:var(--success-tint);color:var(--success)}
    .crsh-info-icon.indigo{background:#e0e7ff;color:#3730A3}
    .crsh-info-content{flex:1;min-width:0}
    .crsh-info-label{font-size:11px;font-weight:700;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px}
    .crsh-info-value{font-size:14px;line-height:1.5;word-break:break-word;color:var(--ink)}
    .crsh-info-value.large{font-size:15px;font-weight:500}
    .crsh-info-value a{color:var(--primary);text-decoration:none}
    .crsh-info-value a:hover{text-decoration:underline}

    .crsh-desc{background:var(--bg);border:1px solid var(--border-soft);border-radius:10px;padding:14px 16px;font-size:14px;line-height:1.6;color:var(--ink)}

    .crsh-timeline{padding:4px 0}
    .crsh-timeline-item{display:flex;gap:12px;padding-bottom:18px;position:relative}
    .crsh-timeline-item:last-child{padding-bottom:0}
    .crsh-timeline-item:not(:last-child)::before{content:"";position:absolute;left:7px;top:24px;bottom:0;width:2px;background:var(--border)}
    .crsh-timeline-dot{width:16px;height:16px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:4px;box-shadow:0 0 0 4px var(--primary-tint)}
    .crsh-timeline-dot.green{background:var(--success);box-shadow:0 0 0 4px var(--success-tint)}
    .crsh-timeline-dot.amber{background:var(--amber);box-shadow:0 0 0 4px var(--amber-tint);animation:crshPulse 2s ease-in-out infinite}
    .crsh-timeline-dot.red{background:var(--danger);box-shadow:0 0 0 4px var(--danger-tint)}
    .crsh-timeline-dot.muted{background:#cbd5e1;box-shadow:0 0 0 4px #f1f5f9}
    @keyframes crshPulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.2);opacity:.7}}
    .crsh-timeline-text{flex:1;min-width:0}
    .crsh-timeline-title{font-weight:600;font-size:14px;color:var(--ink);margin:0 0 2px}
    .crsh-timeline-meta{font-size:12px;color:var(--ink-faint);line-height:1.4}

    .crsh-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:9px 16px;border-radius:10px;font-weight:600;font-size:13.5px;cursor:pointer;text-decoration:none;transition:all .15s ease;border:1.5px solid transparent;font-family:'Inter',sans-serif}
    .crsh-btn-primary{background:var(--primary);color:#fff}
    .crsh-btn-primary:hover{background:var(--primary-dark);color:#fff}
    .crsh-btn-success{background:var(--success);color:#fff}
    .crsh-btn-danger{background:var(--danger);color:#fff}
    .crsh-btn-danger:hover{background:#991b1b;color:#fff}
    .crsh-btn-ghost{background:#fff;color:var(--ink);border-color:var(--border)}
    .crsh-btn-ghost:hover{background:var(--bg);border-color:var(--ink-soft);color:var(--ink)}
    .crsh-btn-ghost.danger{color:var(--danger);border-color:#fecaca}
    .crsh-btn-ghost.danger:hover{background:var(--danger-tint);color:var(--danger);border-color:#fecaca}
    .crsh-btn-sm{padding:6px 12px;font-size:12.5px}
    .crsh-btn-block{width:100%}

    .crsh-funding-amount{font-family:'Fraunces',Georgia,serif;font-size:26px;font-weight:600;letter-spacing:-.02em;line-height:1;color:var(--ink)}
    .crsh-funding-target{font-size:13px;color:var(--ink-soft);margin:4px 0 12px}
    .crsh-funding-target strong{color:var(--ink);font-weight:600}
    .crsh-funding-bar{height:10px;background:var(--border);border-radius:999px;overflow:hidden}
    .crsh-funding-fill{height:100%;background:linear-gradient(90deg,var(--success),#22c55e);border-radius:999px;transition:width 1s cubic-bezier(.22,.61,.36,1)}
    .crsh-funding-stats{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px}
    .crsh-funding-stat{text-align:center;background:var(--bg);border:1px solid var(--border-soft);border-radius:12px;padding:12px}
    .crsh-funding-stat-num{font-family:'Fraunces',serif;font-size:20px;font-weight:600;color:var(--ink)}
    .crsh-funding-stat-label{font-size:11px;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.06em;margin-top:2px}

    .crsh-blockchain{display:flex;flex-wrap:wrap;gap:8px;align-items:center;padding:10px 14px;background:var(--success-tint);border:1px solid #BBF7D0;border-radius:10px}
    .crsh-blockchain-hash{font-family:ui-monospace,Menlo,monospace;font-size:12px;background:#fff;padding:3px 8px;border-radius:6px;color:var(--success);border:1px solid #BBF7D0}

    .crsh-notice{padding:14px 16px;border-radius:12px;display:flex;gap:12px;align-items:flex-start;margin-bottom:16px;border:1px solid}
    .crsh-notice.lock{background:var(--success-tint);border-color:#BBF7D0}
    .crsh-notice.lock i{color:var(--success)}
    .crsh-notice.lock h4{color:var(--success)}
    .crsh-notice.editable{background:var(--amber-tint);border-color:#FDE68A}
    .crsh-notice.editable i{color:var(--amber)}
    .crsh-notice.editable h4{color:var(--amber)}
    .crsh-notice i{font-size:18px;flex-shrink:0;margin-top:1px}
    .crsh-notice h4{margin:0 0 4px;font-size:14px;font-weight:700}
    .crsh-notice p{margin:0;font-size:13px;color:var(--ink-soft);line-height:1.5}
    .crsh-notice p a{color:var(--primary);text-decoration:none;font-weight:500}
    .crsh-notice p a:hover{text-decoration:underline}
    .crsh-notice-actions{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap}

    .crsh-reject{background:var(--danger-tint);border:1px solid #fecaca;border-radius:12px;padding:14px 16px;margin-bottom:16px}
    .crsh-reject h4{color:var(--danger);font-size:14px;font-weight:700;margin:0 0 6px;display:flex;align-items:center;gap:6px}
    .crsh-reject p.lead{margin:0 0 10px;font-size:13px;color:var(--ink-soft);line-height:1.5}
    .crsh-reject .reason{background:#fff;border-radius:8px;padding:12px 14px;font-size:13px;line-height:1.55;color:var(--ink);margin:0 0 10px;border-left:3px solid var(--danger)}
    .crsh-reject .reason strong{color:var(--danger)}
    .crsh-reject .meta{margin:0 0 10px;font-size:11.5px;color:var(--ink-faint)}
    .crsh-reject .actions{display:flex;gap:8px;flex-wrap:wrap}
</style>
@endpush

@section('content')
<div class="crsh-wrap">

    <a href="{{ route('student.reports.index') }}" class="crsh-back">
        <i class="bi bi-arrow-left"></i> Back to my reports
    </a>

    @if (session('error'))
        <div class="crsh-notice editable" style="margin-bottom:16px">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div><h4 style="color:var(--danger)">Action not allowed</h4><p>{{ session('error') }}</p></div>
        </div>
    @endif
    @if (session('status'))
        <div class="crsh-notice lock" style="margin-bottom:16px">
            <i class="bi bi-check-circle-fill"></i>
            <div><h4 style="color:var(--success)">Success</h4><p>{{ session('status') }}</p></div>
        </div>
    @endif

    {{-- HERO --}}
    <section class="crsh-card crsh-hero {{ $status }}">
        <div class="crsh-hero-pills">
            @if ($status === 'verified')
                <span class="crsh-pill pill-verified"><i class="bi bi-shield-check"></i> Verified</span>
            @elseif ($status === 'pending')
                <span class="crsh-pill pill-pending"><i class="bi bi-hourglass-split"></i> Pending Review</span>
            @else
                <span class="crsh-pill pill-rejected"><i class="bi bi-x-circle"></i> Rejected</span>
            @endif

            @if ($crisis && $crisis->impact_level)
                <span class="crsh-pill {{ $impactClass }}"><i class="bi bi-info-circle"></i> {{ $impactLabel }} Priority</span>
            @endif
        </div>
        <h1>{{ ucwords(str_replace('_', ' ', $crisis->crisis_type ?? 'Crisis Report')) }}</h1>
        <div class="crsh-hero-meta">
            @if ($crisis && $crisis->location)
                <span><i class="bi bi-geo-alt"></i> {{ $crisis->location }}</span>
            @endif
            <span><i class="bi bi-calendar-event"></i>
                @if ($status === 'pending')
                    Submitted {{ $report->date_reported?->diffForHumans() }}
                @else
                    Reported {{ $report->date_reported?->format('d M Y') }}
                @endif
            </span>
            @if ($status === 'verified' && $report->blockchain_hash)
                <span><i class="bi bi-shield-check"></i> Blockchain verified</span>
            @endif
        </div>
    </section>

    {{-- STATE-AWARE NOTICE --}}
    @if ($status === 'verified')
        <div class="crsh-notice lock">
            <i class="bi bi-shield-fill-check"></i>
            <div>
                <h4>This report is locked — and that's a good thing</h4>
                <p>Your report has been verified by IIUM admin and recorded on the blockchain. Donors can now contribute. To preserve trust, the report can't be edited after verification. If something has changed, contact <a href="mailto:welfare@iium.edu.my">welfare@iium.edu.my</a>.</p>
            </div>
        </div>

    @elseif ($status === 'pending')
        <div class="crsh-notice editable">
            <i class="bi bi-clock-history"></i>
            <div style="flex:1">
                <h4>Awaiting admin review</h4>
                <p>Your report is in the queue. You can still <strong>edit</strong> it while it's pending. Once an admin verifies it, the report will be locked and recorded on the blockchain.</p>
                <div class="crsh-notice-actions">
                    <a href="{{ route('student.crisis.edit', $report->report_id) }}" class="crsh-btn crsh-btn-sm crsh-btn-primary">
                        <i class="bi bi-pencil"></i> Edit Report
                    </a>
                    <form action="{{ route('student.crisis.destroy', $report->report_id) }}" method="POST" onsubmit="return confirm('Delete this pending report? This cannot be undone.');" style="display:inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="crsh-btn crsh-btn-sm crsh-btn-ghost danger">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

    @else {{-- rejected --}}
        <div class="crsh-reject">
            <h4><i class="bi bi-x-octagon-fill"></i> Report needs revision</h4>
            <p class="lead">Admin reviewed your report but couldn't verify it as-is. You can edit it based on the feedback below and resubmit — this will create a fresh review.</p>
            @if ($report->admin_remarks)
                <div class="reason">
                    <strong>Admin feedback:</strong> {{ $report->admin_remarks }}
                </div>
            @endif
            <p class="meta">
                @if ($report->verifier)
                    Reviewed by {{ $report->verifier->admin_name ?? 'Administrator' }} ·
                @endif
                {{ $report->verified_at?->format('d M Y, H:i') }}
            </p>
            <div class="actions">
                <a href="{{ route('student.crisis.edit', $report->report_id) }}" class="crsh-btn crsh-btn-primary">
                    <i class="bi bi-pencil-square"></i> Edit & Resubmit
                </a>
                <a href="mailto:welfare@iium.edu.my" class="crsh-btn crsh-btn-ghost">
                    <i class="bi bi-envelope"></i> Contact Admin
                </a>
            </div>
        </div>
    @endif

    <div class="crsh-grid">
        {{-- LEFT --}}
        <div>
            <section class="crsh-card">
                <div class="crsh-card-head">
                    <div class="crsh-card-head-icon"><i class="bi bi-info-circle-fill"></i></div>
                    <h3>Case Information</h3>
                </div>
                <div class="crsh-card-body">
                    <div class="crsh-info-row">
                        <div class="crsh-info-icon amber"><i class="bi bi-file-text-fill"></i></div>
                        <div class="crsh-info-content">
                            <div class="crsh-info-label">Description</div>
                            <div class="crsh-desc">{{ $crisis->crisis_description ?? $report->report_description }}</div>
                        </div>
                    </div>

                    @if ($crisis && $crisis->crisis_type)
                        <div class="crsh-info-row">
                            <div class="crsh-info-icon indigo"><i class="bi bi-tag-fill"></i></div>
                            <div class="crsh-info-content">
                                <div class="crsh-info-label">Crisis Type</div>
                                <div class="crsh-info-value large">{{ ucwords(str_replace('_', ' ', $crisis->crisis_type)) }}@if ($crisis->sub_category) · {{ $crisis->sub_category }}@endif</div>
                            </div>
                        </div>
                    @endif

                    @if ($crisis && $crisis->incident_at)
                        <div class="crsh-info-row">
                            <div class="crsh-info-icon"><i class="bi bi-calendar-event"></i></div>
                            <div class="crsh-info-content">
                                <div class="crsh-info-label">Incident Date</div>
                                <div class="crsh-info-value large">{{ \Carbon\Carbon::parse($crisis->incident_at)->format('d M Y, H:i') }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="crsh-info-row">
                        <div class="crsh-info-icon"><i class="bi bi-calendar-check"></i></div>
                        <div class="crsh-info-content">
                            <div class="crsh-info-label">Reported On</div>
                            <div class="crsh-info-value large">{{ $report->date_reported?->format('d M Y, H:i') }}</div>
                        </div>
                    </div>

                    @if (!empty($details['immediate_actions']))
                        <div class="crsh-info-row">
                            <div class="crsh-info-icon purple"><i class="bi bi-lightning-charge"></i></div>
                            <div class="crsh-info-content">
                                <div class="crsh-info-label">Immediate Actions Taken</div>
                                <div class="crsh-desc">{{ $details['immediate_actions'] }}</div>
                            </div>
                        </div>
                    @endif

                    @php $evidence = (array) ($report->supporting_evidence_path ?? []); @endphp
                    @if (!empty($evidence))
                        <div class="crsh-info-row">
                            <div class="crsh-info-icon green"><i class="bi bi-paperclip"></i></div>
                            <div class="crsh-info-content">
                                <div class="crsh-info-label">Supporting Documents ({{ count($evidence) }})</div>
                                <div class="crsh-info-value">
                                    @foreach ($evidence as $i => $path)
                                        <a href="{{ route('student.crisis.evidence.download', ['report' => $report->report_id, 'index' => $i]) }}">
                                            <i class="bi bi-file-earmark-arrow-down"></i> Document {{ $i + 1 }}
                                        </a>
                                        @if (!$loop->last) · @endif
                                    @endforeach
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
                        <div class="crsh-card-head-icon green"><i class="bi bi-shield-check"></i></div>
                        <h3>Verification</h3>
                    </div>
                    <div class="crsh-card-body">
                        <div class="crsh-info-row" style="border-bottom:none;padding:0">
                            <div class="crsh-info-icon green"><i class="bi bi-patch-check-fill"></i></div>
                            <div class="crsh-info-content">
                                <div class="crsh-info-label">Blockchain Proof</div>
                                <div class="crsh-info-value" style="margin-bottom:8px">
                                    Verified by IIUM administration on {{ $report->verified_at?->format('d M Y') }} and immutably recorded on the blockchain.
                                </div>
                                <div class="crsh-blockchain">
                                    <span style="color:var(--success);font-weight:600;font-size:12px">
                                        <i class="bi bi-shield-fill-check"></i> Blockchain Verified
                                    </span>
                                    <span class="crsh-blockchain-hash" id="crshHash">{{ \Illuminate\Support\Str::limit($report->blockchain_hash, 20, '...') }}</span>
                                    <button type="button" class="crsh-btn crsh-btn-sm crsh-btn-ghost" onclick="navigator.clipboard.writeText('{{ $report->blockchain_hash }}'); this.innerHTML='<i class=\'bi bi-check2\'></i> Copied'; setTimeout(()=>this.innerHTML='<i class=\'bi bi-copy\'></i> Copy',1500)">
                                        <i class="bi bi-copy"></i> Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @endif
        </div>

        {{-- RIGHT SIDEBAR --}}
        <div>
            <section class="crsh-card">
                <div class="crsh-card-head">
                    <div class="crsh-card-head-icon"><i class="bi bi-list-check"></i></div>
                    <h3>Status Timeline</h3>
                </div>
                <div class="crsh-card-body">
                    <div class="crsh-timeline">
                        <div class="crsh-timeline-item">
                            <div class="crsh-timeline-dot green"></div>
                            <div class="crsh-timeline-text">
                                <p class="crsh-timeline-title">Submitted</p>
                                <div class="crsh-timeline-meta">{{ $report->date_reported?->format('d M Y, H:i') }}</div>
                            </div>
                        </div>

                        @if ($status === 'pending')
                            <div class="crsh-timeline-item">
                                <div class="crsh-timeline-dot amber"></div>
                                <div class="crsh-timeline-text">
                                    <p class="crsh-timeline-title">Awaiting Admin Review</p>
                                    <div class="crsh-timeline-meta">Usually takes 1-4 hours</div>
                                </div>
                            </div>
                            <div class="crsh-timeline-item">
                                <div class="crsh-timeline-dot muted"></div>
                                <div class="crsh-timeline-text">
                                    <p class="crsh-timeline-title">Decision</p>
                                    <div class="crsh-timeline-meta">Pending</div>
                                </div>
                            </div>
                        @elseif ($status === 'verified')
                            <div class="crsh-timeline-item">
                                <div class="crsh-timeline-dot green"></div>
                                <div class="crsh-timeline-text">
                                    <p class="crsh-timeline-title">Admin Review</p>
                                    <div class="crsh-timeline-meta">
                                        {{ $report->verified_at?->format('d M Y, H:i') }}
                                        @if ($report->verifier)
                                            <br>by {{ $report->verifier->admin_name ?? 'Admin' }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="crsh-timeline-item">
                                <div class="crsh-timeline-dot green"></div>
                                <div class="crsh-timeline-text">
                                    <p class="crsh-timeline-title">Case Active</p>
                                    <div class="crsh-timeline-meta">Open for community support</div>
                                </div>
                            </div>
                        @else {{-- rejected --}}
                            <div class="crsh-timeline-item">
                                <div class="crsh-timeline-dot red"></div>
                                <div class="crsh-timeline-text">
                                    <p class="crsh-timeline-title">Rejected</p>
                                    <div class="crsh-timeline-meta">
                                        {{ $report->verified_at?->format('d M Y, H:i') }}
                                        @if ($report->verifier)
                                            <br>by {{ $report->verifier->admin_name ?? 'Admin' }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="crsh-timeline-item">
                                <div class="crsh-timeline-dot muted"></div>
                                <div class="crsh-timeline-text">
                                    <p class="crsh-timeline-title">Awaiting Resubmission</p>
                                    <div class="crsh-timeline-meta">Edit and resubmit to try again</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Funding (verified only) --}}
            @if ($status === 'verified' && $crisis)
                @php
                    $raised = (float) ($crisis->donation_raised ?? 0);
                    $target = (float) ($crisis->donation_target ?? 0);
                    $percent = $target > 0 ? min(100, ($raised / $target) * 100) : 0;
                @endphp
                <section class="crsh-card">
                    <div class="crsh-card-head">
                        <div class="crsh-card-head-icon green"><i class="bi bi-graph-up-arrow"></i></div>
                        <h3>Funding Progress</h3>
                    </div>
                    <div class="crsh-card-body">
                        <div class="crsh-funding-amount">RM {{ number_format($raised, 2) }}</div>
                        <div class="crsh-funding-target">of <strong>RM {{ number_format($target, 2) }}</strong> goal</div>
                        <div class="crsh-funding-bar"><div class="crsh-funding-fill" style="width:{{ $percent }}%"></div></div>
                        <div style="display:flex;justify-content:space-between;margin-top:8px;font-size:12px;color:var(--ink-faint)">
                            <span><strong style="color:var(--ink)">{{ number_format($percent, 0) }}%</strong> funded</span>
                            <span>updated</span>
                        </div>
                        <a href="{{ route('crisis.show', $crisis->crisis_id) }}" class="crsh-btn crsh-btn-ghost crsh-btn-block" style="margin-top:14px">
                            <i class="bi bi-eye"></i> View Public Page
                        </a>
                    </div>
                </section>
            @endif
        </div>
    </div>
</div>
@endsection
