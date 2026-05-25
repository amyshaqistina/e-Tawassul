@extends('layouts.nok')
@section('title', 'My Submissions')

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .nsub-wrap {
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
    .nsub-wrap *,.nsub-wrap *::before,.nsub-wrap *::after{box-sizing:border-box}

    .nsub-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;gap:14px;flex-wrap:wrap}
    .nsub-header h1{font-family:'Inter',sans-serif;font-weight:700;font-size:24px;margin:0;letter-spacing:-.01em;color:var(--ink)}
    .nsub-header p{color:var(--ink-soft);font-size:14px;margin:4px 0 0}

    .nsub-tabs{display:flex;gap:4px;margin-bottom:16px;background:#fff;padding:5px;border-radius:12px;border:1px solid var(--border-soft);box-shadow:var(--shadow);width:fit-content;max-width:100%;flex-wrap:nowrap;overflow-x:auto;scrollbar-width:thin}
    .nsub-tabs::-webkit-scrollbar{height:4px}
    .nsub-tabs::-webkit-scrollbar-thumb{background:var(--border);border-radius:99px}
    .nsub-tab{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:8px;font-weight:600;font-size:13px;color:var(--ink-soft);text-decoration:none;cursor:pointer;transition:all .15s;border:none;background:transparent;font-family:inherit;white-space:nowrap;flex-shrink:0}
    .nsub-tab:hover{background:var(--bg);color:var(--ink)}
    .nsub-tab.active{background:var(--primary);color:#fff}
    .nsub-tab-count{background:rgba(255,255,255,.25);padding:1px 7px;border-radius:99px;font-size:11px;font-weight:700}
    .nsub-tab:not(.active) .nsub-tab-count{background:var(--bg);color:var(--ink-faint)}

    .nsub-section-title{font-family:'Inter',sans-serif;font-size:16px;font-weight:700;margin:24px 0 12px;display:flex;align-items:center;gap:8px;color:var(--ink)}
    .nsub-section-title i{color:var(--primary)}
    .nsub-section-title .count{background:var(--primary-tint);color:var(--primary);font-size:12px;font-weight:600;padding:2px 8px;border-radius:99px;font-family:'Inter',sans-serif}

    .nsub-card{background:#fff;border-radius:12px;box-shadow:var(--shadow);padding:16px 18px;margin-bottom:10px;display:flex;align-items:center;gap:14px;border-left:4px solid var(--border);transition:all .15s}
    .nsub-card.verified{border-left-color:var(--success)}
    .nsub-card.pending{border-left-color:var(--amber)}
    .nsub-card.rejected{border-left-color:var(--danger)}
    .nsub-card:hover{box-shadow:0 4px 12px rgba(20,28,55,.08);transform:translateX(2px)}

    .nsub-card-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:18px;background:var(--primary-tint);color:var(--primary)}
    .nsub-card-icon.death{background:var(--purple-tint);color:var(--purple)}

    .nsub-card-main{flex:1;min-width:0}
    .nsub-card-title{font-weight:600;font-size:14.5px;color:var(--ink)}
    .nsub-card-meta{font-size:12.5px;color:var(--ink-faint);margin-top:3px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}

    .nsub-pill{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:4px 10px;border-radius:999px;text-transform:uppercase;letter-spacing:.04em}
    .pill-verified{background:var(--success-tint);color:var(--success)}
    .pill-pending {background:var(--amber-tint);color:var(--amber)}
    .pill-rejected{background:var(--danger-tint);color:var(--danger)}

    .nsub-btn{display:inline-flex;align-items:center;justify-content:center;gap:5px;padding:7px 12px;border-radius:8px;font-weight:600;font-size:12.5px;cursor:pointer;text-decoration:none;border:1.5px solid transparent;font-family:inherit;transition:all .15s;white-space:nowrap}
    .nsub-btn-primary{background:var(--primary);color:#fff}
    .nsub-btn-primary:hover{background:var(--primary-dark);color:#fff}
    .nsub-btn-ghost{background:#fff;color:var(--ink);border-color:var(--border)}
    .nsub-btn-ghost:hover{background:var(--bg);color:var(--ink)}
    .nsub-btn-disabled{background:#f1f5f9;color:var(--ink-faint);cursor:not-allowed;border-color:var(--border-soft)}

    /* Fixed widths so View button stays aligned across all rows */
    .nsub-btn-view{min-width:80px}
    .nsub-btn-action{min-width:110px}

    .nsub-actions{display:flex;gap:6px;flex-shrink:0;flex-wrap:nowrap;justify-content:flex-end}

    .nsub-empty{padding:60px 24px;text-align:center;color:var(--ink-soft);background:#fff;border-radius:14px}
    .nsub-empty i{font-size:48px;color:var(--ink-faint);margin-bottom:14px;display:block}
    .nsub-empty h3{font-family:'Inter',sans-serif;font-size:16px;margin:0 0 4px;font-weight:700;color:var(--ink)}
    .nsub-empty p{margin:0;font-size:14px}

    @media (max-width:640px){
        .nsub-card{flex-wrap:wrap}
        .nsub-actions{width:100%}
    }
</style>
@endpush

@section('content')
<div class="nsub-wrap">

    {{-- <div class="nsub-header">
        <div>
            <h1>My Submissions</h1>
            <p>All crisis reports and death confirmations you've submitted ({{ $counts['all'] }} total)</p>
        </div>
    </div> --}}

    @if (session('status'))
        <div style="background:var(--success-tint);border:1px solid #BBF7D0;border-radius:12px;padding:14px 16px;margin-bottom:16px;display:flex;gap:10px;align-items:flex-start">
            <i class="bi bi-check-circle-fill" style="color:var(--success);font-size:18px"></i>
            <p style="margin:0;font-size:13.5px">{{ session('status') }}</p>
        </div>
    @endif

    {{-- Filter tabs --}}
    <div class="nsub-tabs">
        <a href="{{ route('nok.submissions.index', ['status' => 'all']) }}"
           class="nsub-tab {{ $filter === 'all' ? 'active' : '' }}">
            All <span class="nsub-tab-count">{{ $counts['all'] }}</span>
        </a>
        <a href="{{ route('nok.submissions.index', ['status' => 'pending']) }}"
           class="nsub-tab {{ $filter === 'pending' ? 'active' : '' }}">
            <i class="bi bi-hourglass-split"></i> Pending <span class="nsub-tab-count">{{ $counts['pending'] }}</span>
        </a>
        <a href="{{ route('nok.submissions.index', ['status' => 'verified']) }}"
           class="nsub-tab {{ $filter === 'verified' ? 'active' : '' }}">
            <i class="bi bi-shield-check"></i> Verified <span class="nsub-tab-count">{{ $counts['verified'] }}</span>
        </a>
        <a href="{{ route('nok.submissions.index', ['status' => 'rejected']) }}"
           class="nsub-tab {{ $filter === 'rejected' ? 'active' : '' }}">
            <i class="bi bi-x-circle"></i> Rejected <span class="nsub-tab-count">{{ $counts['rejected'] }}</span>
        </a>
    </div>

    @if ($reports->count() === 0 && $deaths->count() === 0)
        <div class="nsub-empty">
            <i class="bi bi-inbox"></i>
            <h3>Nothing here yet</h3>
            <p>You haven't submitted any {{ $filter === 'all' ? '' : $filter }} reports or confirmations.</p>
        </div>
    @endif

    {{-- Crisis Reports section --}}
    @if ($reports->count() > 0)
        <h3 class="nsub-section-title">
            <i class="bi bi-exclamation-triangle"></i> Crisis Reports <span class="count">{{ $reports->count() }}</span>
        </h3>
        @foreach ($reports as $rep)
            @php
                $rcrisis = $rep->crisis;
                $type = $rcrisis ? ucwords(str_replace('_', ' ', $rcrisis->crisis_type)) : 'Crisis';
            @endphp
            <div class="nsub-card {{ $rep->report_status }}">
                <div class="nsub-card-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div class="nsub-card-main">
                    <div class="nsub-card-title">Report #{{ $rep->report_id }} — {{ $type }}</div>
                    <div class="nsub-card-meta">
                        @if ($rep->report_status === 'verified')
                            <span class="nsub-pill pill-verified"><i class="bi bi-shield-check"></i> Verified</span>
                        @elseif ($rep->report_status === 'pending')
                            <span class="nsub-pill pill-pending"><i class="bi bi-hourglass-split"></i> Pending</span>
                        @else
                            <span class="nsub-pill pill-rejected"><i class="bi bi-x-circle"></i> Rejected</span>
                        @endif
                        <span><i class="bi bi-clock"></i> {{ $rep->date_reported?->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="nsub-actions">
                    <a href="{{ route('nok.crisis.show', $rep->report_id) }}" class="nsub-btn nsub-btn-ghost nsub-btn-view">
                        <i class="bi bi-eye"></i> View
                    </a>
                    @if ($rep->report_status === 'verified')
                        <span class="nsub-btn nsub-btn-disabled nsub-btn-action" title="Verified reports are locked by blockchain">
                            <i class="bi bi-lock-fill"></i> Locked
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    @endif

    {{-- Death Confirmations section --}}
    @if ($deaths->count() > 0)
        <h3 class="nsub-section-title">
            <i class="bi bi-file-medical"></i> Death Confirmations <span class="count">{{ $deaths->count() }}</span>
        </h3>
        @foreach ($deaths as $conf)
            <div class="nsub-card {{ $conf->status }}">
                <div class="nsub-card-icon death"><i class="bi bi-file-medical-fill"></i></div>
                <div class="nsub-card-main">
                    <div class="nsub-card-title">Confirmation #{{ $conf->confirmation_id }}</div>
                    <div class="nsub-card-meta">
                        @if ($conf->status === 'verified')
                            <span class="nsub-pill pill-verified"><i class="bi bi-shield-check"></i> Verified</span>
                        @elseif ($conf->status === 'pending')
                            <span class="nsub-pill pill-pending"><i class="bi bi-hourglass-split"></i> Pending</span>
                        @else
                            <span class="nsub-pill pill-rejected"><i class="bi bi-x-circle"></i> Needs revision</span>
                        @endif
                        <span><i class="bi bi-clock"></i> {{ $conf->date_triggered?->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="nsub-actions">
                    <a href="{{ route('nok.death.show', $conf->confirmation_id) }}" class="nsub-btn nsub-btn-ghost nsub-btn-view">
                        <i class="bi bi-eye"></i> View
                    </a>
                    @if ($conf->status === 'pending')
                        <a href="{{ route('nok.death.edit', $conf->confirmation_id) }}" class="nsub-btn nsub-btn-primary nsub-btn-action">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                    @elseif ($conf->status === 'rejected')
                        <a href="{{ route('nok.death.edit', $conf->confirmation_id) }}" class="nsub-btn nsub-btn-primary nsub-btn-action">
                            <i class="bi bi-arrow-clockwise"></i> Update
                        </a>
                    @else
                        <span class="nsub-btn nsub-btn-disabled nsub-btn-action" title="Verified confirmations are locked">
                            <i class="bi bi-lock-fill"></i> Locked
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
