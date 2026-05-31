{{--
    Crisis card — public dashboard grid item.
    Matches the student page aesthetic: clean white cards, soft pastel
    pills, structured rows, rounded corners.
    Expected vars: $crisis (App\Models\Crisis)
--}}
@php
    /** @var \App\Models\Crisis $crisis */
    $progress = $crisis->progress_percent;
    $impactKey = strtolower($crisis->impact_level ?? 'medium');

    $impactStyles = [
        'critical' => ['bg' => '#FEE2E2', 'fg' => '#991B1B', 'icon' => 'exclamation-circle-fill', 'label' => 'Critical'],
        'high'     => ['bg' => '#FEF3C7', 'fg' => '#92400E', 'icon' => 'exclamation-triangle-fill', 'label' => 'High'],
        'medium'   => ['bg' => '#DBEAFE', 'fg' => '#1E40AF', 'icon' => 'info-circle-fill', 'label' => 'Medium'],
        'low'      => ['bg' => '#E0E7FF', 'fg' => '#3730A3', 'icon' => 'circle-fill', 'label' => 'Low'],
    ];
    $impact = $impactStyles[$impactKey] ?? $impactStyles['medium'];

    $title = ucwords(str_replace('_', ' ', $crisis->crisis_type));

    // Defensive check — works whether or not the donation_open column exists
    $isActive = strtolower($crisis->status ?? 'active') === 'active';
    if (method_exists($crisis, 'isAcceptingDonations')) {
        $canDonate = $crisis->isAcceptingDonations();
    } else {
        $canDonate = $isActive;
    }

    $donorsCount = $crisis->donations()->count();
@endphp

@once
@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    /* Crisis card — scoped under .cc to avoid Bootstrap conflicts */
    .cc {
        background: #fff;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 1px 2px rgba(20,28,55,.04), 0 4px 16px rgba(20,28,55,.04);
        display: flex; flex-direction: column;
        height: 100%;
        transition: transform .2s ease, box-shadow .2s ease;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .cc:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(20,28,55,.06), 0 12px 32px rgba(20,28,55,.08);
    }
    .cc-head {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 10px;
    }
    .cc-pill {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 10.5px; font-weight: 700;
        padding: 4px 10px; border-radius: 999px;
        text-transform: uppercase; letter-spacing: .04em;
    }
    .cc-pill i { font-size: 10px; }
    .cc-time {
        font-size: 11.5px; color: #8a92a6;
        display: inline-flex; align-items: center; gap: 4px;
    }
    .cc-time i { font-size: 11px; }
    .cc-title {
        font-family: 'inter', Georgia, serif;
        font-size: 18px; font-weight: 600;
        color: #1a2238;
        margin: 0 0 5px;
        letter-spacing: -.01em;
        line-height: 1.25;
    }
    .cc-loc {
        font-size: 12px; color: #5b6479;
        margin: 0 0 8px;
        display: flex; align-items: flex-start; gap: 5px;
        line-height: 1.4;
    }
    .cc-loc i { color: #8a92a6; font-size: 11px; flex-shrink: 0; margin-top: 2px; }
    .cc-desc {
        font-size: 13px; color: #5b6479;
        line-height: 1.5;
        margin: 0 0 14px;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .cc-progress {
        background: #f5f6fa;
        border: 1px solid #f0f2f7;
        border-radius: 10px;
        padding: 11px 13px;
        margin-bottom: 12px;
    }
    .cc-progress-top {
        display: flex; justify-content: space-between; align-items: baseline;
        margin-bottom: 7px;
        flex-wrap: wrap;
        gap: 4px;
    }
    .cc-raised-label {
        font-size: 10.5px; font-weight: 700;
        color: #8a92a6;
        text-transform: uppercase; letter-spacing: .05em;
    }
    .cc-raised-amt {
        font-family: 'inter', Georgia, serif;
        font-size: 11.5px; color: #5b6479;
    }
    .cc-raised-amt strong {
        font-weight: 600; font-size: 15px;
        color: #1a2238; letter-spacing: -.01em;
    }
    .cc-bar {
        height: 6px; background: #e8eaf0;
        border-radius: 999px; overflow: hidden;
    }
    .cc-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #15803d, #22c55e);
        border-radius: 999px;
        transition: width 1s ease;
    }
    .cc-progress-bottom {
        display: flex; justify-content: space-between;
        margin-top: 7px;
        font-size: 11px; color: #8a92a6;
    }
    .cc-progress-bottom span {
        display: inline-flex; align-items: center; gap: 4px;
    }
    .cc-progress-bottom i { font-size: 11px; }
    .cc-actions {
        display: grid;
        grid-template-columns: 1fr 1.4fr;
        gap: 8px;
    }

    /* === Button base — important rules so it doesn't fight Bootstrap === */
    .cc-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        padding: 9px 14px;
        border-radius: 10px;
        text-decoration: none !important;
        transition: background .15s ease, border-color .15s ease, transform .15s ease, box-shadow .15s ease;
        font-family: inherit;
        border: 1.5px solid transparent;
        line-height: 1;
        cursor: pointer;
    }
    .cc-btn i { font-size: 13px; }

    /* Details — ghost button, stays dark on hover */
    .cc-btn-ghost,
    .cc-btn-ghost:link,
    .cc-btn-ghost:visited {
        background: #fff !important;
        color: #1a2238 !important;
        border-color: #e8eaf0 !important;
    }
    .cc-btn-ghost:hover,
    .cc-btn-ghost:focus {
        background: #eef3ff !important;
        color: #2563eb !important;
        border-color: #dbe6ff !important;
        text-decoration: none !important;
    }

    /* Donate — solid green, stays white text on hover */
    .cc-btn-donate,
    .cc-btn-donate:link,
    .cc-btn-donate:visited {
        background: #15803d !important;
        color: #fff !important;
        box-shadow: 0 2px 6px rgba(21,128,61,.25);
    }
    .cc-btn-donate:hover,
    .cc-btn-donate:focus {
        background: #166534 !important;
        color: #fff !important;
        text-decoration: none !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(21,128,61,.35);
    }

    /* Closed — disabled grey */
    .cc-btn-closed,
    .cc-btn-closed:link,
    .cc-btn-closed:visited {
        background: #f5f6fa !important;
        color: #8a92a6 !important;
        border-color: #e8eaf0 !important;
        cursor: not-allowed;
    }
    .cc-btn-closed:hover,
    .cc-btn-closed:focus {
        background: #f5f6fa !important;
        color: #8a92a6 !important;
        text-decoration: none !important;
    }
</style>
@endpush
@endonce

<article class="cc">
    {{-- Header --}}
    <header class="cc-head">
        <span class="cc-pill" style="background: {{ $impact['bg'] }}; color: {{ $impact['fg'] }};">
            <i class="bi bi-{{ $impact['icon'] }}"></i>
            {{ $impact['label'] }}
        </span>
        <span class="cc-time">
            <i class="bi bi-clock"></i>
            {{ $crisis->date_reported?->diffForHumans() }}
        </span>
    </header>

    {{-- Title --}}
    <h3 class="cc-title">{{ $title }}</h3>

    @if($crisis->location)
        <p class="cc-loc">
            <i class="bi bi-geo-alt-fill"></i>
            <span>{{ \Illuminate\Support\Str::limit($crisis->location, 70) }}</span>
        </p>
    @endif

    {{-- Description --}}
    <p class="cc-desc">
        {{ $crisis->crisis_description ?: 'No description provided.' }}
    </p>

    {{-- Progress --}}
    <div class="cc-progress">
        <div class="cc-progress-top">
            <span class="cc-raised-label">Raised</span>
            <span class="cc-raised-amt">
                <strong>RM {{ number_format((float) $crisis->donation_raised, 0) }}</strong>
                of RM {{ number_format((float) $crisis->donation_target, 0) }}
            </span>
        </div>
        <div class="cc-bar">
            <div class="cc-bar-fill" style="width: {{ $progress }}%"></div>
        </div>
        <div class="cc-progress-bottom">
            <span>{{ $progress }}% funded</span>
            <span>
                <i class="bi bi-people-fill"></i>
                {{ $donorsCount }} {{ \Illuminate\Support\Str::plural('donor', $donorsCount) }}
            </span>
        </div>
    </div>

    {{-- Actions --}}
    <div class="cc-actions">
        <a href="{{ route('crisis.show', $crisis->crisis_id) }}" class="cc-btn cc-btn-ghost">
            <i class="bi bi-eye"></i> Details
        </a>
        @if($canDonate)
            <a href="{{ route('donate.create', $crisis->crisis_id) }}" class="cc-btn cc-btn-donate">
                <i class="bi bi-heart-fill"></i> Donate
            </a>
        @else
            <a href="{{ route('crisis.show', $crisis->crisis_id) }}" class="cc-btn cc-btn-closed">
                <i class="bi bi-lock-fill"></i> Closed
            </a>
        @endif
    </div>
</article>
