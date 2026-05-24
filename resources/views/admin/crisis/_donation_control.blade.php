{{--
    Donation Control Panel
    ----------------------
    Drop-in partial for the admin "verify report" page. Only renders
    after the report is verified (status === 'verified').

    Required in scope:
        $report   App\Models\CrisisReport (with ->crisis loaded)

    Behaviour:
        - Live banner pulses green when open, dark when closed
        - Cap presets (RM 1k–100k) + custom value
        - Auto-close toggle: closes automatically once target is hit
        - Master open/close toggle (submits the parent form on change)
--}}

@php
    /** @var \App\Models\Crisis|null $crisis */
    $crisis = $crisis ?? $report->crisis;
@endphp

@if ($crisis && ($report->report_status ?? null) === 'verified')

    @php
        $raised      = (float) $crisis->donation_raised;
        $target      = (float) $crisis->donation_target;
        $percent     = $target > 0 ? min(100, ($raised / $target) * 100) : 0;
        $remaining   = max(0, $target - $raised);
        $donorsCount = $crisis->donations()->count();
        $avgDonation = $donorsCount > 0 ? $raised / $donorsCount : 0;
        $daysOpen    = $crisis->date_reported ? $crisis->date_reported->diffInDays(now()) : 0;
        $isOpen      = (bool) $crisis->donation_open;
    @endphp

    <div class="donation-control-card no-print">

        {{-- Status banner --}}
        <div class="dc-banner {{ $isOpen ? 'open' : 'closed' }}">
            <span class="dc-dot"></span>
            <span class="dc-banner-text">
                @if ($isOpen)
                    Donation page is <strong>LIVE</strong> — accepting contributions
                @else
                    Donation page is <strong>CLOSED</strong> — hidden from public
                @endif
            </span>
            <span class="dc-banner-sub">
                @if ($isOpen)
                    Public can donate now
                @elseif ($crisis->donation_closed_at)
                    Closed {{ $crisis->donation_closed_at->diffForHumans() }}
                @else
                    Not visible
                @endif
            </span>
        </div>

        {{-- Card header --}}
        <div class="dc-head">
            <div class="dc-head-icon"><i class="bi bi-heart-fill"></i></div>
            <div>
                <h5 class="dc-title">Donation Control</h5>
                <p class="dc-sub">Manage funding cap, auto-close, and public visibility</p>
            </div>
        </div>

        {{-- Progress block --}}
        <div class="dc-progress">
            <div class="dc-progress-top">
                <div>
                    <div class="dc-amount">RM {{ number_format($raised, 2) }}</div>
                    <div class="dc-target-line">of <strong>RM {{ number_format($target, 2) }}</strong> goal</div>
                </div>
                <div class="dc-percent-block">
                    <div class="dc-percent">{{ number_format($percent, 0) }}%</div>
                    <div class="dc-percent-label">funded</div>
                </div>
            </div>
            <div class="dc-bar"><div class="dc-bar-fill {{ $percent >= 100 ? 'hit' : '' }}" style="width: {{ $percent }}%"></div></div>
            <div class="dc-meta">
                <span><strong>RM {{ number_format($remaining, 2) }}</strong> remaining</span>
                <span>{{ $crisis->updated_at->diffForHumans() }}</span>
            </div>

            <div class="dc-quick-stats">
                <div class="dc-quick-stat">
                    <div class="dc-quick-num">{{ $donorsCount }}</div>
                    <div class="dc-quick-label">Donors</div>
                </div>
                <div class="dc-quick-stat">
                    <div class="dc-quick-num">RM {{ number_format($avgDonation, 0) }}</div>
                    <div class="dc-quick-label">Avg / donor</div>
                </div>
                <div class="dc-quick-stat">
                    <div class="dc-quick-num">{{ $daysOpen }}d</div>
                    <div class="dc-quick-label">Days open</div>
                </div>
            </div>
        </div>

        {{-- Cap form --}}
        <form method="POST" action="{{ route('admin.crisis.donation-cap', $crisis->crisis_id) }}" class="dc-section">
            @csrf
            <h6 class="dc-section-title"><i class="bi bi-bullseye"></i> Donation Cap (Goal Amount)</h6>

            <div class="dc-presets">
                @foreach ([1000, 5000, 10000, 20000, 50000, 100000] as $preset)
                    <button type="button" class="dc-preset {{ (int) $target === $preset ? 'active' : '' }}"
                            data-amount="{{ $preset }}">RM {{ number_format($preset) }}</button>
                @endforeach
            </div>

            <div class="dc-input-wrap">
                <input type="number" name="donation_target" id="donation_target"
                       class="dc-input" value="{{ (int) $target }}"
                       min="100" max="1000000" step="100" required>
            </div>

            <label class="dc-auto-row">
                <input type="checkbox" name="auto_close_on_target" value="1"
                       {{ $crisis->auto_close_on_target ? 'checked' : '' }}>
                <div>
                    <div class="dc-auto-text">Auto-close when goal is reached</div>
                    <div class="dc-auto-sub">Donation page automatically stops accepting funds once the cap is hit. You can re-open anytime.</div>
                </div>
            </label>

            <button type="submit" class="dc-btn dc-btn-primary dc-btn-block">
                <i class="bi bi-save"></i> Save Donation Cap
            </button>
        </form>

        {{-- Open / Close toggle --}}
        <div class="dc-section">
            <h6 class="dc-section-title"><i class="bi bi-eye"></i> Public Visibility</h6>

            <form method="POST" action="{{ route('admin.crisis.toggle-donation', $crisis->crisis_id) }}" class="dc-toggle-form">
                @csrf
                <div class="dc-toggle-row">
                    <div>
                        <div class="dc-toggle-label">Accept donations</div>
                        <div class="dc-toggle-desc">
                            @if ($isOpen)
                                Donation page is visible to the public.
                            @else
                                Donation page is hidden from public view.
                            @endif
                        </div>
                    </div>
                    <label class="dc-switch">
                        <input type="checkbox" {{ $isOpen ? 'checked' : '' }}
                               onchange="if(confirm('{{ $isOpen ? "Close donation page? Donors will see a closed message." : "Re-open donation page? Donors will be able to contribute again." }}')) this.form.submit(); else this.checked = {{ $isOpen ? 'true' : 'false' }};">
                        <span class="dc-slider"></span>
                    </label>
                </div>
            </form>

            <a href="{{ route('donate.create', $crisis->crisis_id) }}" target="_blank"
               class="dc-btn dc-btn-ghost dc-btn-block">
                <i class="bi bi-box-arrow-up-right"></i> Preview Public Page
            </a>
        </div>

    </div>

    @push('styles')
    <style>
        /* ============ Donation Control Panel ============ */
        .donation-control-card {
            background:#fff; border:1px solid #E5E7EB; border-radius:14px;
            overflow:hidden; margin-top:16px;
            box-shadow:0 1px 2px rgba(20,28,55,0.04), 0 4px 16px rgba(20,28,55,0.04);
            position:relative;
        }
        .donation-control-card::before {
            content:""; position:absolute; top:0; left:0; right:0; height:3px;
            background:linear-gradient(90deg,#15803d,#22c55e);
        }
        .donation-control-card:has(.dc-banner.closed)::before {
            background:linear-gradient(90deg,#374151,#1f2937);
        }

        .dc-banner {
            padding:11px 18px; display:flex; align-items:center; gap:10px;
            border-bottom:1px solid #F3F4F6; font-size:13px;
        }
        .dc-banner.open { background:#E8F6EE; color:#15803d; }
        .dc-banner.closed { background:#1f2937; color:#fff; }
        .dc-banner.closed .dc-banner-sub { color:rgba(255,255,255,0.7); }
        .dc-banner-text { flex:1; font-weight:500; }
        .dc-banner-sub { font-size:11.5px; opacity:0.85; }
        .dc-dot {
            width:9px; height:9px; border-radius:50%; flex-shrink:0;
            background:#15803d; box-shadow:0 0 0 4px rgba(21,128,61,0.2);
            animation:dc-pulse 2s ease-in-out infinite;
        }
        .dc-banner.closed .dc-dot {
            background:#f87171; box-shadow:0 0 0 4px rgba(248,113,113,0.2);
        }
        @keyframes dc-pulse { 0%,100%{transform:scale(1);opacity:1} 50%{transform:scale(1.15);opacity:0.7} }

        .dc-head { display:flex; align-items:center; gap:10px; padding:14px 18px;
                   border-bottom:1px solid #F3F4F6; }
        .dc-head-icon {
            width:32px; height:32px; border-radius:9px;
            background:#E8F6EE; color:#15803d;
            display:flex; align-items:center; justify-content:center; font-size:14px;
            flex-shrink:0;
        }
        .dc-title { margin:0; font-size:14px; font-weight:700; color:#111827; }
        .dc-sub   { margin:0; font-size:11.5px; color:#9CA3AF; }

        .dc-progress { padding:18px; }
        .dc-progress-top { display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:8px; }
        .dc-amount { font-size:22px; font-weight:700; color:#111827; line-height:1.1; letter-spacing:-0.01em; }
        .dc-target-line { font-size:12px; color:#6B7280; margin-top:2px; }
        .dc-target-line strong { color:#111827; }
        .dc-percent-block { text-align:right; }
        .dc-percent { font-size:18px; font-weight:700; color:#15803d; line-height:1; }
        .dc-percent-label { font-size:10.5px; color:#9CA3AF; text-transform:uppercase; letter-spacing:0.05em; }

        .dc-bar { height:9px; background:#F3F4F6; border-radius:999px; overflow:hidden; position:relative; }
        .dc-bar-fill {
            height:100%; border-radius:999px;
            background:linear-gradient(90deg,#15803d,#22c55e);
            transition:width 1s cubic-bezier(.22,.61,.36,1); position:relative;
        }
        .dc-bar-fill::after {
            content:""; position:absolute; inset:0;
            background:linear-gradient(90deg,transparent,rgba(255,255,255,0.35),transparent);
            animation:dc-shine 2.4s ease-in-out infinite;
        }
        @keyframes dc-shine { 0%{transform:translateX(-100%)} 100%{transform:translateX(100%)} }
        .dc-bar-fill.hit { background:linear-gradient(90deg,#a16207,#eab308); }

        .dc-meta { display:flex; justify-content:space-between; margin-top:8px; font-size:11.5px; color:#9CA3AF; }
        .dc-meta strong { color:#111827; font-weight:600; }

        .dc-quick-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-top:14px; }
        .dc-quick-stat { background:#F9FAFB; border:1px solid #F3F4F6;
                         border-radius:9px; padding:10px; text-align:center; }
        .dc-quick-num { font-size:15px; font-weight:700; color:#111827; }
        .dc-quick-label { font-size:10px; color:#9CA3AF; text-transform:uppercase; letter-spacing:0.05em; margin-top:2px; }

        .dc-section { padding:16px 18px; border-top:1px solid #F3F4F6; }
        .dc-section-title {
            font-size:11.5px; font-weight:700; color:#6B7280;
            text-transform:uppercase; letter-spacing:0.05em;
            margin:0 0 12px; display:flex; align-items:center; gap:6px;
        }
        .dc-section-title i { font-size:13px; }

        .dc-presets { display:flex; flex-wrap:wrap; gap:5px; margin-bottom:10px; }
        .dc-preset {
            background:#F9FAFB; border:1.5px solid #E5E7EB;
            color:#111827; font-weight:600; font-size:11.5px;
            padding:6px 10px; border-radius:7px; cursor:pointer;
            transition:all 0.15s ease;
        }
        .dc-preset:hover { background:#EFF6FF; border-color:#BFDBFE; }
        .dc-preset.active { background:#1E40AF; color:#fff; border-color:#1E40AF; }

        .dc-input-wrap { position:relative; margin-bottom:12px; }
        .dc-input-wrap::before {
            content:"RM"; position:absolute; left:12px; top:50%;
            transform:translateY(-50%); color:#9CA3AF;
            font-weight:600; font-size:13px; pointer-events:none;
        }
        .dc-input {
            width:100%; padding:9px 12px 9px 38px;
            border:1.5px solid #E5E7EB; border-radius:8px;
            font-size:14px; font-weight:600; color:#111827;
            transition:all 0.15s ease;
        }
        .dc-input:focus { outline:none; border-color:#1E40AF;
                          box-shadow:0 0 0 3px rgba(30,64,175,0.1); }

        .dc-auto-row {
            display:flex; align-items:flex-start; gap:9px;
            padding:11px 13px; background:#FEF3C7;
            border:1px solid #FCD34D; border-radius:8px;
            margin-bottom:12px; cursor:pointer;
        }
        .dc-auto-row input { margin:0; width:15px; height:15px;
                             accent-color:#B45309; flex-shrink:0; margin-top:1px; cursor:pointer; }
        .dc-auto-text { font-size:12.5px; font-weight:600; color:#111827; }
        .dc-auto-sub { font-size:11px; color:#92400E; margin-top:1px; line-height:1.4; }

        .dc-toggle-row {
            display:flex; align-items:center; justify-content:space-between;
            padding:12px; background:#F9FAFB;
            border-radius:9px; border:1px solid #F3F4F6;
            margin-bottom:10px;
        }
        .dc-toggle-label { font-size:13px; font-weight:600; color:#111827; }
        .dc-toggle-desc { font-size:11.5px; color:#6B7280; margin-top:1px; }

        .dc-switch { position:relative; width:42px; height:24px; flex-shrink:0; }
        .dc-switch input { opacity:0; width:0; height:0; }
        .dc-slider {
            position:absolute; inset:0; background:#cbd5e1;
            border-radius:24px; cursor:pointer; transition:0.3s;
        }
        .dc-slider:before {
            position:absolute; content:""; height:18px; width:18px;
            left:3px; bottom:3px; background:#fff; border-radius:50%;
            transition:0.3s; box-shadow:0 2px 4px rgba(0,0,0,0.2);
        }
        .dc-switch input:checked + .dc-slider { background:#15803d; }
        .dc-switch input:checked + .dc-slider:before { transform:translateX(18px); }

        .dc-btn {
            display:inline-flex; align-items:center; justify-content:center; gap:6px;
            padding:9px 14px; border-radius:8px; font-weight:600; font-size:12.5px;
            cursor:pointer; border:none; transition:all 0.15s ease;
            text-decoration:none; font-family:inherit;
        }
        .dc-btn-primary { background:#1E40AF; color:#fff;
                          box-shadow:0 2px 6px rgba(30,64,175,0.2); }
        .dc-btn-primary:hover { background:#1E3A8A; transform:translateY(-1px); color:#fff; }
        .dc-btn-ghost { background:#fff; color:#111827; border:1.5px solid #E5E7EB; }
        .dc-btn-ghost:hover { background:#F9FAFB; border-color:#9CA3AF; color:#111827; }
        .dc-btn-block { width:100%; }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.querySelectorAll('.dc-preset').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.dc-preset').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('donation_target').value = this.dataset.amount;
            });
        });
        document.getElementById('donation_target')?.addEventListener('input', function () {
            document.querySelectorAll('.dc-preset').forEach(b => b.classList.remove('active'));
        });
    </script>
    @endpush
@endif
