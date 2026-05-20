@extends('layouts.student')
@section('title', 'Student Dashboard')
@section('page-title', 'Welcome, ' . $student->first_name)
@section('page-subtitle', 'Your e-Tawassul home')

@section('content')
    <style>
        .dashboard-wrap {
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            min-width: 0 !important;
            max-width: 100% !important;
            overflow-x: hidden !important;
        }

        .dashboard-wrap>* { min-width: 0 !important; }

        /* ===== Hero Row ===== */
        .hero-row {
            display: grid !important;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) !important;
            gap: 16px !important; margin-bottom: 20px !important; align-items: stretch !important;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #1a56db, #06b6d4) !important;
            border-radius: 14px !important; padding: 18px 22px !important;
            display: flex !important; align-items: center !important; justify-content: space-between !important;
            position: relative !important; overflow: hidden !important; color: #fff !important; min-width: 0 !important;
        }
        .welcome-banner::before {
            content: ''; position: absolute; right: -40px; top: -40px;
            width: 140px; height: 140px; border-radius: 50%; background: rgba(255,255,255,0.06);
        }
        .welcome-text { position: relative; z-index: 1; flex: 1; min-width: 0; }
        .welcome-text h2 { color: #fff !important; font-size: 17px !important; font-weight: 700 !important; margin: 0 0 4px 0 !important; }
        .welcome-text p { color: rgba(255,255,255,0.85) !important; font-size: 11.5px !important; line-height: 1.5 !important; margin: 0 !important; }
        .welcome-badges { display: flex !important; gap: 6px !important; margin-top: 10px !important; flex-wrap: wrap !important; }
        .welcome-badge {
            display: inline-flex !important; align-items: center !important; gap: 5px !important;
            background: rgba(255,255,255,0.15) !important; border: 1px solid rgba(255,255,255,0.25) !important;
            border-radius: 14px !important; padding: 3px 10px !important; font-size: 10.5px !important;
            font-weight: 600 !important; color: #fff !important; white-space: nowrap !important;
        }
        .welcome-icon { font-size: 40px !important; opacity: 0.18 !important; position: relative !important; z-index: 1 !important; line-height: 1 !important; flex-shrink: 0 !important; }

        .privacy-notice {
            background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-radius: 14px !important;
            padding: 14px 16px !important; display: flex !important; gap: 10px !important;
            align-items: flex-start !important; box-shadow: 0 1px 3px rgba(0,0,0,0.03) !important; min-width: 0 !important;
        }
        .privacy-notice-icon {
            width: 30px !important; height: 30px !important; border-radius: 8px !important;
            background: #eff6ff !important; color: #1a56db !important; display: flex !important;
            align-items: center !important; justify-content: center !important; font-size: 13px !important; flex-shrink: 0 !important;
        }
        .privacy-notice-body { flex: 1; min-width: 0; }
        .privacy-notice-body h4 { font-size: 12px !important; font-weight: 700 !important; color: #0f172a !important; margin: 0 0 6px 0 !important; }
        .privacy-notice-body ul { list-style: none !important; padding: 0 !important; margin: 0 !important; }
        .privacy-notice-body li {
            font-size: 10.5px !important; color: #64748b !important; line-height: 1.45 !important;
            padding-left: 10px !important; position: relative !important; margin: 0 0 2px 0 !important;
        }
        .privacy-notice-body li:last-child { margin-bottom: 0 !important; }
        .privacy-notice-body li::before { content: '•'; position: absolute; left: 0; color: #94a3b8; }

        /* ===== Action Cards ===== */
        .action-cards-row {
            display: grid !important; grid-template-columns: minmax(0,1fr) minmax(0,1fr) !important;
            gap: 20px !important; margin-bottom: 28px !important;
        }
        .action-card {
            background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-radius: 14px !important;
            padding: 22px 24px !important; transition: all 0.25s !important; display: flex !important;
            gap: 18px !important; align-items: flex-start !important; box-shadow: 0 2px 8px rgba(0,0,0,0.04) !important;
            position: relative !important; overflow: hidden !important; min-width: 0 !important;
        }
        .action-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; }
        .action-card.legacy::before { background: #1a56db; }
        .action-card.crisis::before { background: #ea580c; }
        .action-card:hover { transform: translateY(-3px) !important; box-shadow: 0 12px 28px rgba(0,0,0,0.08) !important; }
        .action-card-icon {
            width: 52px !important; height: 52px !important; border-radius: 12px !important;
            display: flex !important; align-items: center !important; justify-content: center !important;
            font-size: 22px !important; flex-shrink: 0 !important;
        }
        .action-card.legacy .action-card-icon { background: #eff6ff !important; color: #1a56db !important; }
        .action-card.crisis .action-card-icon { background: #fff7ed !important; color: #ea580c !important; }
        .action-card-body { flex: 1; min-width: 0; }
        .action-card-body h3 { font-size: 14.5px !important; font-weight: 700 !important; color: #0f172a !important; margin: 0 0 6px 0 !important; }
        .action-card-body p { font-size: 12px !important; color: #64748b !important; margin: 0 0 12px 0 !important; line-height: 1.5 !important; }
        .action-card-btn {
            display: inline-block !important; padding: 6px 16px !important; border-radius: 8px !important;
            font-size: 12.5px !important; font-weight: 600 !important; text-decoration: none !important; color: #fff !important;
        }
        .action-card.legacy .action-card-btn { background: #1a56db !important; }
        .action-card.crisis .action-card-btn { background: #ea580c !important; }

        /* ===== Stats Row ===== */
        .stats-row {
            display: grid !important; grid-template-columns: repeat(3, minmax(0,1fr)) !important;
            gap: 16px !important; margin-bottom: 24px !important;
        }
        .stat-tile {
            background: #ffffff !important; border: 1px solid #e2e8f0 !important;
            border-radius: 14px !important; padding: 18px !important; display: flex !important;
            align-items: center !important; gap: 14px !important; box-shadow: 0 1px 3px rgba(0,0,0,0.03) !important; min-width: 0 !important;
        }
        .stat-tile-icon {
            width: 44px !important; height: 44px !important; border-radius: 10px !important;
            display: flex !important; align-items: center !important; justify-content: center !important;
            font-size: 20px !important; flex-shrink: 0 !important;
        }
        .stat-tile.tile-primary .stat-tile-icon { background: #eff6ff !important; color: #1a56db !important; }
        .stat-tile.tile-success .stat-tile-icon { background: #ecfdf5 !important; color: #059669 !important; }
        .stat-tile.tile-warning .stat-tile-icon { background: #fef9c3 !important; color: #b45309 !important; }
        .stat-tile-value { font-size: 22px !important; font-weight: 800 !important; color: #0f172a !important; line-height: 1.1 !important; }
        .stat-tile-label { font-size: 12px !important; color: #64748b !important; margin-top: 2px !important; }

        /* ===== Bottom row — equal-height cards ===== */
        .bottom-row { align-items: stretch; }
        .bottom-row > [class*="col-"] { display: flex; }
        .bottom-row .content-card {
            display: flex; flex-direction: column; width: 100%; height: 100%;
        }

        /* ===== Color-coded report rows in the list ===== */
        .report-row {
            display: flex; justify-content: space-between; align-items: center;
            gap: 12px; padding: 12px 12px; border-radius: 10px;
            border-left: 4px solid transparent; margin-bottom: 8px;
            transition: background 0.15s;
        }
        .report-row:hover { background: #f8fafc; }
        .report-row.is-pending  { border-left-color: #f59e0b; background: #fffbeb; }
        .report-row.is-verified { border-left-color: #10b981; background: #f0fdf4; }
        .report-row.is-rejected { border-left-color: #ef4444; background: #fef2f2; }

        .report-row .report-meta { flex: 1; min-width: 0; }
        .report-row .report-title { font-weight: 600; color: #0f172a; font-size: 13.5px; }
        .report-row .report-time { font-size: 11px; color: #64748b; margin-top: 2px; }

        /* ===== Status Timeline (right side) ===== */
        .status-timeline-card {
            padding: 18px 18px 18px 24px !important;
        }
        .status-timeline-title {
            font-size: 11px; font-weight: 700; color: #64748b;
            text-transform: uppercase; letter-spacing: 0.6px;
            margin-bottom: 16px;
        }

        .timeline-item {
            display: flex; gap: 12px; align-items: flex-start;
            position: relative; padding-bottom: 22px;
        }
        .timeline-item:last-child { padding-bottom: 0; }

        .timeline-dot-col {
            flex-shrink: 0; width: 14px;
            position: relative;
            z-index: 2;
        }

        .timeline-dot {
            width: 14px; height: 14px; border-radius: 50%;
            background: #cbd5e1;
            display: block;
            margin-top: 3px;
        }
        .timeline-dot.done    { background: #1a56db; }
        .timeline-dot.current { background: #1a56db; box-shadow: 0 0 0 4px rgba(26,86,219,0.15); }
        .timeline-dot.success { background: #10b981; }
        .timeline-dot.danger  { background: #dc2626; }
        .timeline-dot.future  { background: #fff; border: 2px solid #cbd5e1; }

        /* Line is absolutely positioned — guaranteed centered under dot */
        .timeline-line {
            position: absolute;
            left: 6px;          /* (14px dot / 2) - (2px line / 2) = 6px */
            top: 20px;          /* 3px margin-top + 14px dot + 3px gap */
            bottom: 4px;
            width: 2px;
            background: #e2e8f0;
            z-index: 1;
        }
        .timeline-line.done { background: #1a56db; }

        .timeline-content { flex: 1; padding-top: 0; min-width: 0; }
        .timeline-label   { font-weight: 700; color: #0f172a; font-size: 14px; line-height: 1.2; }
        .timeline-label.future { color: #94a3b8; font-weight: 600; }
        .timeline-label.danger { color: #dc2626; }
        .timeline-sub     { font-size: 12px; color: #64748b; margin-top: 3px; }

        .timeline-status-banner {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 12px; border-radius: 20px; font-size: 11.5px;
            font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px;
            margin-bottom: 14px;
        }
        .timeline-status-banner.is-pending  { background: #fef3c7; color: #92400e; }
        .timeline-status-banner.is-verified { background: #d1fae5; color: #047857; }
        .timeline-status-banner.is-rejected { background: #fee2e2; color: #b91c1c; }

        @media (max-width: 992px) {
            .hero-row { grid-template-columns: minmax(0,1fr) !important; }
            .bottom-row > [class*="col-"] { display: block; }
            .bottom-row .content-card { height: auto; }
        }
        @media (max-width: 768px) {
            .action-cards-row { grid-template-columns: minmax(0,1fr) !important; }
            .stats-row { grid-template-columns: minmax(0,1fr) !important; }
            .welcome-icon { display: none !important; }
        }
    </style>

    <div class="dashboard-wrap container-fluid py-3">

        {{-- NoK nag banner — shown if student hasn't added any kin --}}
        @if($student->nextOfKin->isEmpty())
            <div style="background:#FFFBEB; border:1px solid #FCD34D; border-radius:12px;
                        padding:14px 18px; margin-bottom:16px;
                        display:flex; align-items:flex-start; gap:14px;">
                <div style="background:#F59E0B; color:#fff; width:38px; height:38px;
                            border-radius:8px; display:flex; align-items:center; justify-content:center;
                            font-size:18px; flex-shrink:0;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div style="flex:1;">
                    <strong style="color:#92400E; font-size:14px; display:block; margin-bottom:3px;">
                        Please add at least one next of kin
                    </strong>
                    <p style="color:#78350F; font-size:12.5px; margin:0; line-height:1.5;">
                        In an emergency, your family won't be able to access this platform on your behalf
                        unless you've registered them as your next of kin. It only takes a minute.
                    </p>
                </div>
                <a href="{{ route('student.profile') }}#kin"
                   style="background:#F59E0B; color:#fff; font-size:12.5px; font-weight:600;
                          padding:8px 14px; border-radius:8px; text-decoration:none;
                          white-space:nowrap; flex-shrink:0;">
                    <i class="bi bi-plus-lg"></i> Add Next of Kin
                </a>
            </div>
        @endif

        {{-- ===== Hero Row ===== --}}
        <div class="hero-row">
            <div class="welcome-banner">
                <div class="welcome-text">
                    <h2>Assalamu'alaikum, {{ $student->first_name }} 👋</h2>
                    <p>Your account is secure and protected. All data is end-to-end encrypted.</p>
                    <div class="welcome-badges">
                        <span class="welcome-badge"><i class="bi bi-person-vcard"></i> {{ $student->student_id ?? 'N/A' }}</span>
                        <span class="welcome-badge"><i class="bi bi-mortarboard-fill"></i> {{ $student->faculty ?? ($student->program ?? 'Faculty') }}</span>
                        <span class="welcome-badge"><i class="bi bi-calendar3"></i> Year {{ $student->year_of_study ?? '—' }}</span>
                        <span class="welcome-badge"><i class="bi bi-clock"></i> {{ now()->format('D, d M') }} &bull; {{ now()->format('h:i a') }}</span>
                    </div>
                </div>
                <div class="welcome-icon">🎓</div>
            </div>

            <div class="privacy-notice">
                <div class="privacy-notice-icon"><i class="bi bi-shield-lock-fill"></i></div>
                <div class="privacy-notice-body">
                    <h4>Privacy &amp; Security</h4>
                    <ul>
                        <li>Encrypted end-to-end</li>
                        <li>LDMS activated only on verified death</li>
                        <li>Strict confidentiality</li>
                        <li>PDPA &amp; GDPR compliant</li>
                        <li>Blockchain audit trail</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- ===== Action Cards ===== --}}
        <div class="action-cards-row">
            <div class="action-card legacy">
                <div class="action-card-icon"><i class="bi bi-envelope-fill"></i></div>
                <div class="action-card-body">
                    <h3>Last Digital Message System</h3>
                    <p>Create a secure farewell message (wasiat) to be delivered to designated recipients only upon verified confirmation of death.</p>
                    <a href="{{ route('student.ldms.create') }}" class="action-card-btn">Create Message</a>
                </div>
            </div>

            <div class="action-card crisis">
                <div class="action-card-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div class="action-card-body">
                    <h3>Crisis Reporting &amp; Notifications</h3>
                    <p>Report accidents, illness, or emergencies. Your report will be securely verified and processed by university staff.</p>
                    <a href="{{ route('student.crisis.create') }}" class="action-card-btn">Report Crisis</a>
                </div>
            </div>
        </div>

        {{-- ===== Stats Row ===== --}}
        <div class="stats-row">
            <div class="stat-tile tile-primary">
                <div class="stat-tile-icon"><i class="bi bi-clipboard-pulse"></i></div>
                <div>
                    <div class="stat-tile-value">{{ $reports->count() }}</div>
                    <div class="stat-tile-label">Recent Reports</div>
                </div>
            </div>

            <div class="stat-tile tile-success">
                <div class="stat-tile-icon"><i class="bi bi-envelope-paper"></i></div>
                <div>
                    <div class="stat-tile-value">{{ $ldmsCount }}</div>
                    <div class="stat-tile-label">Legacy Messages</div>
                </div>
            </div>

            <div class="stat-tile tile-warning">
                <div class="stat-tile-icon"><i class="bi bi-bell-fill"></i></div>
                <div>
                    <div class="stat-tile-value">{{ $unreadCount }}</div>
                    <div class="stat-tile-label">Unread Notifications</div>
                </div>
            </div>
        </div>

        {{-- ===== Reports list + Status timeline ===== --}}
        <div class="row g-3 bottom-row">

            {{-- LEFT: report list with color-coded rows --}}
            <div class="col-lg-7">
                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">My recent crisis reports</h5>
                        <small class="text-muted">Click a report to see its full timeline</small>
                    </div>

                    @forelse($reports as $r)
                        @php
                            $status = strtolower($r->report_status ?? 'pending');
                            $rowClass = match($status) {
                                'verified', 'approved' => 'is-verified',
                                'rejected', 'declined' => 'is-rejected',
                                default                => 'is-pending',
                            };
                            $badgeClass = match($status) {
                                'verified', 'approved' => 'success',
                                'rejected', 'declined' => 'danger',
                                default                => 'warning',
                            };
                        @endphp

                        <a href="{{ route('student.crisis.show', $r->report_id) }}"
                           class="report-row {{ $rowClass }} text-decoration-none">
                            <div class="report-meta">
                                <div class="report-title">
                                    Report #{{ $r->report_id }} —
                                    {{ ucwords(str_replace('_', ' ', $r->crisis?->crisis_type ?? '')) }}
                                </div>
                                <div class="report-time">
                                    <i class="bi bi-clock"></i> {{ $r->date_reported?->diffForHumans() }}
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $badgeClass }}">{{ strtoupper($status) }}</span>
                                <i class="bi bi-chevron-right text-muted ms-2"></i>
                            </div>
                        </a>
                    @empty
                        <div class="text-muted text-center py-4">No reports submitted yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- RIGHT: status timeline for the most recent report --}}
            <div class="col-lg-5">
                <div class="content-card status-timeline-card">
                    @if($reports->isNotEmpty())
                        @php
                            $latest = $reports->first();
                            $status = strtolower($latest->report_status ?? 'pending');
                            $isRejected = in_array($status, ['rejected', 'declined'], true);
                            $isVerified = in_array($status, ['verified', 'approved'], true);

                            // Stage: 0=Submitted, 1=Under Review, 2=Final
                            $stage = $isVerified || $isRejected ? 2 : 1;

                            $bannerClass = match(true) {
                                $isVerified => 'is-verified',
                                $isRejected => 'is-rejected',
                                default     => 'is-pending',
                            };
                            $bannerText = match(true) {
                                $isVerified => 'Verified',
                                $isRejected => 'Rejected',
                                default     => 'Pending Review',
                            };
                        @endphp

                        <div class="status-timeline-title">Latest Report Status</div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="timeline-status-banner {{ $bannerClass }}">
                                @if($isVerified) <i class="bi bi-check-circle-fill"></i>
                                @elseif($isRejected) <i class="bi bi-x-circle-fill"></i>
                                @else <i class="bi bi-hourglass-split"></i>
                                @endif
                                {{ $bannerText }}
                            </span>
                            <small class="text-muted">#{{ $latest->report_id }}</small>
                        </div>

                        {{-- Step 1: Submitted (always done) --}}
                        <div class="timeline-item">
                            <div class="timeline-dot-col">
                                <span class="timeline-dot done"></span>
                            </div>
                            <span class="timeline-line done"></span>
                            <div class="timeline-content">
                                <div class="timeline-label">Submitted</div>
                                <div class="timeline-sub">
                                    {{ $latest->date_reported?->format('d M Y, h:i A') }}
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: Admin Review --}}
                        <div class="timeline-item">
                            <div class="timeline-dot-col">
                                <span class="timeline-dot {{ $stage > 1 ? 'done' : 'current' }}"></span>
                            </div>
                            <span class="timeline-line {{ ($isVerified || $isRejected) ? 'done' : '' }}"></span>
                            <div class="timeline-content">
                                <div class="timeline-label">Admin Review</div>
                                <div class="timeline-sub">
                                    @if($stage > 1)
                                        Reviewed {{ $latest->updated_at?->diffForHumans() }}
                                    @else
                                        Pending — awaiting verification
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Step 3: Final outcome (NO line — last item) --}}
                        <div class="timeline-item">
                            <div class="timeline-dot-col">
                                <span class="timeline-dot
                                    {{ $isVerified ? 'success' : ($isRejected ? 'danger' : 'future') }}"></span>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-label {{ $isRejected ? 'danger' : ($stage < 2 ? 'future' : '') }}">
                                    @if($isVerified) Verified
                                    @elseif($isRejected) Rejected
                                    @else Final Decision
                                    @endif
                                </div>
                                <div class="timeline-sub">
                                    @if($isVerified) Approved by administrator
                                    @elseif($isRejected) See admin notes on the report page
                                    @else Awaiting outcome
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <a href="{{ route('student.crisis.show', $latest->report_id) }}"
                               class="btn btn-sm btn-outline-primary">
                                View full report <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-signpost fs-1 d-block mb-2"></i>
                            <p class="mb-0 small">No reports yet.</p>
                            <p class="text-muted small">Submit a report to see its status here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
