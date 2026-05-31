@extends('layouts.public')
@section('title', 'Case #' . $crisis->crisis_id . ' — e-Tawassul')

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --cs-bg: #f5f6fa;
        --cs-card: #ffffff;
        --cs-ink: #1a2238;
        --cs-ink-soft: #5b6479;
        --cs-ink-faint: #8a92a6;
        --cs-border: #e8eaf0;
        --cs-border-soft: #f0f2f7;
        --cs-primary: #2563eb;
        --cs-primary-tint: #eef3ff;
        --cs-success: #15803d;
        --cs-success-tint: #e8f6ee;
        --cs-amber: #b45309;
        --cs-amber-tint: #fdf1de;
        --cs-danger: #b91c1c;
        --cs-danger-tint: #fdeaea;
        --cs-shadow: 0 1px 2px rgba(20,28,55,.04), 0 4px 16px rgba(20,28,55,.04);
    }

    body.public-layout { background: var(--cs-bg) !important; }

    .cs-page {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        max-width: 1200px; margin: 0 auto;
        padding: 28px 24px 80px;
        color: var(--cs-ink);
    }

    .cs-back {
        display: inline-flex; align-items: center; gap: 6px;
        color: var(--cs-primary); text-decoration: none;
        font-size: 14px; font-weight: 500;
        padding: 8px 12px; margin: 0 0 16px -12px;
        border-radius: 10px;
        transition: background .15s ease;
    }
    .cs-back:hover { background: var(--cs-primary-tint); color: var(--cs-primary); text-decoration: none; }

    .cs-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 20px;
        align-items: start;
    }

    .cs-card {
        background: var(--cs-card);
        border-radius: 16px;
        box-shadow: var(--cs-shadow);
        margin-bottom: 18px;
        overflow: hidden;
    }
    .cs-card-head {
        display: flex; align-items: center; gap: 10px;
        padding: 18px 24px;
        border-bottom: 1px solid var(--cs-border-soft);
    }
    .cs-card-head-icon {
        width: 32px; height: 32px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        background: var(--cs-primary-tint); color: var(--cs-primary);
        font-size: 14px; flex-shrink: 0;
    }
    .cs-card-head h3 {
        margin: 0;
        font-size: 16px; font-weight: 700; color: var(--cs-ink);
    }
    .cs-card-body { padding: 22px 24px; }

    .cs-hero {
        position: relative; overflow: hidden;
        padding: 28px 32px;
    }
    .cs-hero::before {
        content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px;
        background: linear-gradient(90deg, var(--cs-success), #22c55e);
    }

    .cs-pills { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; }
    .cs-pill {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 11.5px; font-weight: 600;
        padding: 5px 11px; border-radius: 999px;
        text-transform: uppercase; letter-spacing: .04em;
    }
    .cs-pill i { font-size: 12px; }
    .cs-pill-verified { background: var(--cs-success-tint); color: var(--cs-success); }
    .cs-pill-active   { background: var(--cs-primary-tint); color: var(--cs-primary); }
    .cs-pill-closed   { background: #e5e7eb; color: #374151; }
    .cs-pill-critical { background: var(--cs-danger-tint); color: var(--cs-danger); }
    .cs-pill-high     { background: var(--cs-amber-tint); color: var(--cs-amber); }
    .cs-pill-medium   { background: var(--cs-primary-tint); color: var(--cs-primary); }
    .cs-pill-low      { background: #e0e7ff; color: #3730a3; }

    .cs-hero h1 {
        font-family:'Inter', Georgia, serif;
        font-weight: 600; font-size: 32px;
        letter-spacing: -.018em;
        margin: 0 0 12px;
        color: var(--cs-ink);
        line-height: 1.15;
    }
    .cs-hero-meta {
        display: flex; flex-wrap: wrap; gap: 12px 22px;
        color: var(--cs-ink-soft); font-size: 13.5px;
    }
    .cs-hero-meta span { display: inline-flex; align-items: center; gap: 6px; }
    .cs-hero-meta i { color: var(--cs-ink-faint); font-size: 14px; }

    .cs-info-row {
        display: flex; align-items: flex-start;
        padding: 16px 0;
        border-bottom: 1px solid var(--cs-border-soft);
    }
    .cs-info-row:last-child { border-bottom: none; padding-bottom: 0; }
    .cs-info-row:first-child { padding-top: 0; }

    .cs-info-icon {
        width: 38px; height: 38px; border-radius: 10px;
        background: var(--cs-primary-tint); color: var(--cs-primary);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; font-size: 15px;
        margin-right: 14px;
    }

    .cs-info-content { flex: 1; min-width: 0; }
    .cs-info-label {
        font-size: 11px; font-weight: 700;
        color: var(--cs-ink-faint);
        text-transform: uppercase; letter-spacing: .06em;
        margin-bottom: 4px;
    }
    .cs-info-value {
        font-size: 14.5px; color: var(--cs-ink);
        line-height: 1.5; word-break: break-word;
    }
    .cs-info-value.large { font-size: 15.5px; font-weight: 500; }

    .cs-desc-box {
        background: var(--cs-bg);
        border: 1px solid var(--cs-border-soft);
        border-radius: 10px;
        padding: 16px 18px;
        font-size: 14.5px;
        color: var(--cs-ink);
        line-height: 1.65;
    }

    .cs-blockchain {
        display: flex; flex-wrap: wrap; gap: 10px;
        align-items: center;
        padding: 14px 16px;
        background: var(--cs-success-tint);
        border: 1px solid #BBF7D0;
        border-radius: 12px;
        margin-top: 14px;
    }
    .cs-blockchain-badge {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12.5px; font-weight: 600;
        color: var(--cs-success);
    }
    .cs-blockchain-hash {
        font-family: ui-monospace, Menlo, monospace;
        font-size: 12px;
        background: #fff;
        padding: 4px 10px;
        border-radius: 6px;
        color: var(--cs-success);
        border: 1px solid #BBF7D0;
    }
    .cs-blockchain-copy {
        background: #fff;
        border: 1px solid #BBF7D0;
        color: var(--cs-success);
        font-size: 11.5px; font-weight: 600;
        padding: 4px 10px; border-radius: 6px;
        cursor: pointer;
        display: inline-flex; align-items: center; gap: 4px;
        transition: all .15s ease;
        font-family: inherit;
    }
    .cs-blockchain-copy:hover { background: var(--cs-success); color: #fff; }

    .cs-side-funding { position: top: 16px; }
    .cs-funding-amount {
        font-family: 'Inter', Georgia, serif;
        font-size: 30px; font-weight: 600;
        color: var(--cs-ink); line-height: 1;
        letter-spacing: -.02em;
        margin-bottom: 4px;
    }
    .cs-funding-target {
        font-size: 13.5px; color: var(--cs-ink-soft);
        margin-bottom: 14px;
    }
    .cs-funding-target strong { color: var(--cs-ink); font-weight: 600; }
    .cs-funding-bar {
        height: 10px; background: var(--cs-border-soft);
        border-radius: 999px; overflow: hidden; position: relative;
    }
    .cs-funding-fill {
        height: 100%; width: 0;
        background: linear-gradient(90deg, var(--cs-success), #22c55e);
        border-radius: 999px;
        transition: width 1.4s cubic-bezier(.22,.61,.36,1);
        position: relative;
    }
    .cs-funding-fill::after {
        content: ""; position: absolute; inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.35), transparent);
        animation: cs-shine 2.4s ease-in-out infinite;
    }
    @keyframes cs-shine { 0% {transform: translateX(-100%)} 100% {transform: translateX(100%)} }
    .cs-funding-percent {
        display: flex; justify-content: space-between;
        margin-top: 8px;
        font-size: 12.5px; color: var(--cs-ink-faint);
    }
    .cs-funding-percent strong { color: var(--cs-ink); font-weight: 600; }

    .cs-funding-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 18px;
    }
    .cs-funding-stat {
        text-align: center;
        background: var(--cs-bg);
        border: 1px solid var(--cs-border-soft);
        border-radius: 12px;
        padding: 14px;
    }
    .cs-funding-stat-num {
        font-family: 'Inter', Georgia, serif;
        font-size: 24px; font-weight: 600;
        color: var(--cs-ink); line-height: 1;
    }
    .cs-funding-stat-label {
        font-size: 11px; color: var(--cs-ink-faint);
        text-transform: uppercase; letter-spacing: .06em;
        margin-top: 4px;
    }

    .cs-donate-btn {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%;
        background: var(--cs-success); color: #fff;
        font-weight: 600; font-size: 15px;
        padding: 14px 20px;
        border: none; border-radius: 12px;
        cursor: pointer;
        margin-top: 18px;
        text-decoration: none;
        transition: all .2s ease;
        box-shadow: 0 4px 12px rgba(21,128,61,.25);
        font-family: inherit;
    }
    .cs-donate-btn:hover {
        background: #166534; color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(21,128,61,.35);
        text-decoration: none;
    }
    .cs-donate-btn i { font-size: 16px; }

    .cs-donor {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 0;
        border-bottom: 1px solid var(--cs-border-soft);
    }
    .cs-donor:last-child { border-bottom: none; }
    .cs-donor-avatar {
        width: 36px; height: 36px; border-radius: 50%;
        background: linear-gradient(135deg, var(--cs-primary), #3b82f6);
        color: #fff; font-weight: 600; font-size: 13px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; font-family: 'Inter', Georgia, serif;
    }
    .cs-donor-avatar.anon {
        background: var(--cs-bg); color: var(--cs-ink-faint);
        border: 1px solid var(--cs-border);
    }
    .cs-donor-info { flex: 1; min-width: 0; }
    .cs-donor-name {
        font-size: 13.5px; font-weight: 500; color: var(--cs-ink);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .cs-donor-time { font-size: 11.5px; color: var(--cs-ink-faint); }
    .cs-donor-amount {
        font-family: 'Inter', Georgia, serif;
        font-weight: 600; font-size: 15px;
        color: var(--cs-success);
        flex-shrink: 0;
    }
    .cs-donor-empty {
        text-align: center;
        padding: 22px;
        color: var(--cs-ink-faint);
        font-size: 13px;
    }
    .cs-donor-empty i {
        display: block;
        font-size: 28px;
        opacity: .4;
        margin-bottom: 6px;
    }

    .cs-reveal {
        opacity: 0; transform: translateY(12px);
        animation: cs-reveal .6s cubic-bezier(.22,.61,.36,1) forwards;
    }
    .cs-reveal:nth-child(1) { animation-delay: .05s; }
    .cs-reveal:nth-child(2) { animation-delay: .12s; }
    .cs-reveal:nth-child(3) { animation-delay: .19s; }
    @keyframes cs-reveal { to { opacity: 1; transform: translateY(0); } }

    .cs-toast {
        position: fixed; bottom: 24px; left: 50%;
        transform: translateX(-50%) translateY(80px);
        background: var(--cs-ink); color: #fff;
        padding: 12px 20px; border-radius: 12px;
        font-size: 14px; font-weight: 500;
        box-shadow: 0 8px 24px rgba(0,0,0,.2);
        transition: transform .3s cubic-bezier(.22,.61,.36,1);
        z-index: 1000;
        display: flex; align-items: center; gap: 8px;
    }
    .cs-toast.show { transform: translateX(-50%) translateY(0); }
    .cs-toast i { color: #22c55e; font-size: 16px; }

    .cs-alert {
        padding: 12px 18px; border-radius: 12px;
        margin-bottom: 16px; font-size: 14px;
        display: flex; align-items: flex-start; gap: 10px;
    }
    .cs-alert.success { background: var(--cs-success-tint); color: var(--cs-success); border: 1px solid #BBF7D0; }
    .cs-alert.warning { background: var(--cs-amber-tint); color: var(--cs-amber); border: 1px solid #FCD34D; }

    @media (max-width: 1000px) {
        .cs-grid { grid-template-columns: 1fr; }
        .cs-side-funding { position: static; }
    }
    @media (max-width: 640px) {
        .cs-page { padding: 18px 16px 60px; }
        .cs-hero { padding: 22px 20px; }
        .cs-card-head { padding: 16px 20px; }
        .cs-card-body { padding: 18px 20px; }
        .cs-hero h1 { font-size: 24px; }
    }
</style>
@endpush

@section('content')
<div class="cs-page">

    <a href="{{ route('home') }}" class="cs-back">
        <i class="bi bi-arrow-left"></i> Back to all cases
    </a>

    @if(session('status'))
        <div class="cs-alert success"><i class="bi bi-check-circle-fill"></i>{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="cs-alert warning"><i class="bi bi-exclamation-triangle-fill"></i>{{ session('error') }}</div>
    @endif

    @php
        $impactKey = strtolower($crisis->impact_level ?? 'medium');
        $statusKey = strtolower($crisis->status ?? 'active');
        $isActive = $statusKey === 'active';

        // Recent donations — use only columns that exist in current schema
        $recentDonations = $crisis->donations()
            ->orderByDesc('donation_date')
            ->limit(5)
            ->get();
        $donorsCount = $crisis->donations()->count();
    @endphp

    <div class="cs-grid">

        {{-- ============ MAIN COLUMN ============ --}}
        <div>

            {{-- HERO --}}
            <section class="cs-card cs-hero cs-reveal">
                <div class="cs-pills">
                    <span class="cs-pill cs-pill-{{ $impactKey }}">
                        <i class="bi bi-info-circle-fill"></i>
                        {{ ucfirst($impactKey) }} Priority
                    </span>
                    @if($isActive)
                        <span class="cs-pill cs-pill-active"><i class="bi bi-circle-fill"></i> Active</span>
                    @elseif($statusKey === 'resolved')
                        <span class="cs-pill cs-pill-closed"><i class="bi bi-check-circle-fill"></i> Resolved</span>
                    @else
                        <span class="cs-pill cs-pill-closed"><i class="bi bi-pause-circle-fill"></i> {{ ucfirst($statusKey) }}</span>
                    @endif
                </div>

                <h1>{{ ucwords(str_replace('_', ' ', $crisis->crisis_type)) }}</h1>

                <div class="cs-hero-meta">
                    @if($crisis->location)
                        <span><i class="bi bi-geo-alt-fill"></i> {{ \Illuminate\Support\Str::limit($crisis->location, 80) }}</span>
                    @endif
                    @if($crisis->date_reported)
                        <span><i class="bi bi-calendar-event"></i> {{ $crisis->date_reported->format('d M Y') }}</span>
                    @endif
                    <span><i class="bi bi-shield-check"></i> Blockchain verified</span>
                </div>
            </section>

            {{-- CASE INFORMATION --}}
            <section class="cs-card cs-reveal">
                <div class="cs-card-head">
                    <div class="cs-card-head-icon"><i class="bi bi-info-circle-fill"></i></div>
                    <h3>Case Information</h3>
                </div>
                <div class="cs-card-body">

                    {{-- Description --}}
                    <div class="cs-info-row">
                        <div class="cs-info-icon" style="background: var(--cs-amber-tint); color: var(--cs-amber);">
                            <i class="bi bi-file-text-fill"></i>
                        </div>
                        <div class="cs-info-content">
                            <div class="cs-info-label">Description</div>
                            <div class="cs-desc-box">{{ $crisis->crisis_description }}</div>
                        </div>
                    </div>

                    {{-- Additional details (try JSON decode for structured display) --}}
                    @if($crisis->crisis_details)
                        @php
                            $details = null;
                            if (is_string($crisis->crisis_details)) {
                                $decoded = json_decode($crisis->crisis_details, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    $details = $decoded;
                                }
                            }
                        @endphp

                        @if($details)
                            @if(!empty($details['sub_category']))
                                <div class="cs-info-row">
                                    <div class="cs-info-icon" style="background: #f0e9fd; color: #6d28d9;">
                                        <i class="bi bi-tag-fill"></i>
                                    </div>
                                    <div class="cs-info-content">
                                        <div class="cs-info-label">Sub-category</div>
                                        <div class="cs-info-value large">{{ ucwords(str_replace('_', ' ', $details['sub_category'])) }}</div>
                                    </div>
                                </div>
                            @endif

                            @if(!empty($details['incident_at']))
                                <div class="cs-info-row">
                                    <div class="cs-info-icon" style="background: #e0e7ff; color: #3730A3;">
                                        <i class="bi bi-clock-fill"></i>
                                    </div>
                                    <div class="cs-info-content">
                                        <div class="cs-info-label">Incident Date & Time</div>
                                        <div class="cs-info-value large">
                                            @php
                                                try {
                                                    $dt = \Carbon\Carbon::parse($details['incident_at']);
                                                    echo $dt->format('d M Y, h:i A');
                                                } catch (\Throwable $e) {
                                                    echo e($details['incident_at']);
                                                }
                                            @endphp
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(!empty($details['immediate_actions']))
                                <div class="cs-info-row">
                                    <div class="cs-info-icon" style="background: var(--cs-success-tint); color: var(--cs-success);">
                                        <i class="bi bi-check-square-fill"></i>
                                    </div>
                                    <div class="cs-info-content">
                                        <div class="cs-info-label">Immediate Actions Taken</div>
                                        <div class="cs-desc-box">{{ $details['immediate_actions'] }}</div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="cs-info-row">
                                <div class="cs-info-icon" style="background: var(--cs-primary-tint); color: var(--cs-primary);">
                                    <i class="bi bi-card-text"></i>
                                </div>
                                <div class="cs-info-content">
                                    <div class="cs-info-label">Additional Details</div>
                                    <div class="cs-desc-box">{{ $crisis->crisis_details }}</div>
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- Reported on --}}
                    <div class="cs-info-row">
                        <div class="cs-info-icon" style="background: var(--cs-primary-tint); color: var(--cs-primary);">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <div class="cs-info-content">
                            <div class="cs-info-label">Reported On</div>
                            <div class="cs-info-value large">
                                {{ $crisis->date_reported?->format('d F Y, h:i A') ?? '—' }}
                                @if($crisis->date_reported)
                                    <small style="color: var(--cs-ink-faint); font-weight: 400;">
                                        ({{ $crisis->date_reported->diffForHumans() }})
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            {{-- BLOCKCHAIN VERIFICATION --}}
            @php
                $latestReport = $crisis->reports()->where('report_status', 'verified')->latest('verified_at')->first();
                $blockchainHash = $latestReport->blockchain_hash ?? null;
            @endphp

            @if($blockchainHash)
                <section class="cs-card cs-reveal">
                    <div class="cs-card-head">
                        <div class="cs-card-head-icon" style="background: var(--cs-success-tint); color: var(--cs-success);">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3>Verification</h3>
                    </div>
                    <div class="cs-card-body">
                        <div class="cs-info-row" style="border-bottom: none; padding: 0;">
                            <div class="cs-info-icon" style="background: var(--cs-success-tint); color: var(--cs-success);">
                                <i class="bi bi-patch-check-fill"></i>
                            </div>
                            <div class="cs-info-content">
                                <div class="cs-info-label">Blockchain Verified</div>
                                <div class="cs-info-value" style="margin-bottom: 8px;">
                                    This case has been verified by IIUM administration and immutably recorded on the blockchain.
                                </div>
                                <div class="cs-blockchain">
                                    <span class="cs-blockchain-badge">
                                        <i class="bi bi-shield-fill-check"></i> Verified by Administrator
                                    </span>
                                    <span class="cs-blockchain-hash">
                                        {{ substr($blockchainHash, 0, 8) . '...' . substr($blockchainHash, -8) }}
                                    </span>
                                    <button type="button" class="cs-blockchain-copy"
                                            onclick="copyHash('{{ $blockchainHash }}', this)">
                                        <i class="bi bi-copy"></i> Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @endif

        </div>

        {{-- ============ SIDEBAR ============ --}}
        <div>

            {{-- FUNDING PROGRESS --}}
            <section class="cs-card cs-side-funding cs-reveal">
                <div class="cs-card-head">
                    <div class="cs-card-head-icon" style="background: var(--cs-success-tint); color: var(--cs-success);">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <h3>Funding Progress</h3>
                </div>
                <div class="cs-card-body">

                    <div class="cs-funding-amount">RM {{ number_format((float) $crisis->donation_raised, 2) }}</div>
                    <div class="cs-funding-target">of <strong>RM {{ number_format((float) $crisis->donation_target, 2) }}</strong> goal</div>

                    <div class="cs-funding-bar">
                        <div class="cs-funding-fill" id="fundingFill" data-percent="{{ $crisis->progress_percent }}"></div>
                    </div>
                    <div class="cs-funding-percent">
                        <span><strong>{{ $crisis->progress_percent }}%</strong> funded</span>
                        <span>{{ $crisis->updated_at?->diffForHumans() ?? '—' }}</span>
                    </div>

                    <div class="cs-funding-stats">
                        <div class="cs-funding-stat">
                            <div class="cs-funding-stat-num">{{ $donorsCount }}</div>
                            <div class="cs-funding-stat-label">{{ \Illuminate\Support\Str::plural('Donor', $donorsCount) }}</div>
                        </div>
                        <div class="cs-funding-stat">
                            <div class="cs-funding-stat-num">
                                @php
                                    $daysOpen = $crisis->date_reported ? max(1, $crisis->date_reported->diffInDays(now())) : 0;
                                @endphp
                                {{ $daysOpen }}d
                            </div>
                            <div class="cs-funding-stat-label">Active</div>
                        </div>
                    </div>

                    @if($isActive)
                        <a href="{{ route('donate.create', $crisis->crisis_id) }}" class="cs-donate-btn">
                            <i class="bi bi-heart-fill"></i> Donate to this case
                        </a>
                    @endif

                </div>
            </section>

            {{-- RECENT DONATIONS --}}
            <section class="cs-card cs-reveal">
                <div class="cs-card-head">
                    <div class="cs-card-head-icon" style="background: #f0e9fd; color: #6d28d9;">
                        <i class="bi bi-heart-fill"></i>
                    </div>
                    <h3>Recent Donations</h3>
                </div>
                <div class="cs-card-body" style="padding-top: 6px;">

                    @forelse($recentDonations as $don)
                        @php
                            $isAnon = stripos($don->donor_name ?? '', 'anonymous') !== false;
                            $initials = $isAnon ? '?' : strtoupper(substr($don->donor_name ?? '?', 0, 1));
                        @endphp
                        <div class="cs-donor">
                            <div class="cs-donor-avatar {{ $isAnon ? 'anon' : '' }}">
                                @if($isAnon)
                                    <i class="bi bi-person-fill"></i>
                                @else
                                    {{ $initials }}
                                @endif
                            </div>
                            <div class="cs-donor-info">
                                <div class="cs-donor-name">{{ $don->donor_name }}</div>
                                <div class="cs-donor-time">
                                    {{ $don->donation_date?->diffForHumans() }}
                                    @if($don->payment_method)
                                        · {{ str_replace('_', ' ', $don->payment_method) }}
                                    @endif
                                </div>
                            </div>
                            <div class="cs-donor-amount">RM {{ number_format((float) $don->donation_amount, 0) }}</div>
                        </div>
                    @empty
                        <div class="cs-donor-empty">
                            <i class="bi bi-heart"></i>
                            Be the first to donate.
                        </div>
                    @endforelse

                </div>
            </section>

        </div>
    </div>
</div>

<div class="cs-toast" id="csToast">
    <i class="bi bi-check-circle-fill"></i>
    <span id="csToastMsg">Copied</span>
</div>
@endsection

@push('scripts')
<script>
    window.addEventListener('load', () => {
        setTimeout(() => {
            const fill = document.getElementById('fundingFill');
            if (fill) fill.style.width = (fill.dataset.percent || 0) + '%';
        }, 250);
    });

    function copyHash(hash, btn) {
        const doCopy = () => {
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied';
            btn.style.background = 'var(--cs-success)';
            btn.style.color = '#fff';
            const t = document.getElementById('csToast');
            t.classList.add('show');
            setTimeout(() => {
                btn.innerHTML = original;
                btn.style.background = '';
                btn.style.color = '';
                t.classList.remove('show');
            }, 1800);
        };
        if (navigator.clipboard) {
            navigator.clipboard.writeText(hash).then(doCopy);
        } else {
            const ta = document.createElement('textarea');
            ta.value = hash; document.body.appendChild(ta); ta.select();
            try { document.execCommand('copy'); doCopy(); } catch(e) {}
            document.body.removeChild(ta);
        }
    }
</script>
@endpush
