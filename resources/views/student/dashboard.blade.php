@extends('layouts.student')
@section('title', 'Student Dashboard')
@section('page-title', 'Welcome, ' . $student->first_name)
@section('page-subtitle', 'Your e-Tawassul home')

@section('content')
<style>
.dashboard-wrap { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; }

/* ===== Hero Row: Welcome Banner + Privacy Notice (compact) ===== */
.hero-row {
    display: grid !important;
    grid-template-columns: 2fr 1fr !important;
    gap: 16px !important;
    margin-bottom: 20px !important;
    align-items: stretch !important;
}

/* ===== Welcome Banner (SHRUNK) ===== */
.welcome-banner {
    background: linear-gradient(135deg, #1a56db, #06b6d4) !important;
    border-radius: 14px !important;
    padding: 18px 22px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    position: relative !important;
    overflow: hidden !important;
    color: #fff !important;
}
.welcome-banner::before {
    content:''; position:absolute; right:-40px; top:-40px;
    width:140px; height:140px; border-radius:50%;
    background: rgba(255,255,255,0.06);
}
.welcome-text { position: relative; z-index: 1; flex: 1; }
.welcome-text h2 {
    color:#fff !important;
    font-size:17px !important;
    font-weight:700 !important;
    margin: 0 0 4px 0 !important;
}
.welcome-text p {
    color:rgba(255,255,255,0.85) !important;
    font-size:11.5px !important;
    line-height:1.5 !important;
    margin: 0 !important;
}
.welcome-badges {
    display:flex !important;
    gap:6px !important;
    margin-top:10px !important;
    flex-wrap:wrap !important;
}
.welcome-badge {
    display:inline-flex !important;
    align-items:center !important;
    gap:5px !important;
    background:rgba(255,255,255,0.15) !important;
    border:1px solid rgba(255,255,255,0.25) !important;
    border-radius:14px !important;
    padding:3px 10px !important;
    font-size:10.5px !important;
    font-weight:600 !important;
    color:#fff !important;
}
.welcome-icon {
    font-size:40px !important;
    opacity:0.18 !important;
    position:relative !important;
    z-index:1 !important;
    line-height: 1 !important;
}

/* ===== Privacy Notice (SHRUNK) ===== */
.privacy-notice {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 14px !important;
    padding: 14px 16px !important;
    display: flex !important;
    gap: 10px !important;
    align-items: flex-start !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03) !important;
}
.privacy-notice-icon {
    width: 30px !important;
    height: 30px !important;
    border-radius: 8px !important;
    background: #eff6ff !important;
    color: #1a56db !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 13px !important;
    flex-shrink: 0 !important;
}
.privacy-notice-body { flex: 1; }
.privacy-notice-body h4 {
    font-size: 12px !important;
    font-weight: 700 !important;
    color: #0f172a !important;
    margin: 0 0 6px 0 !important;
}
.privacy-notice-body ul {
    list-style: none !important;
    padding: 0 !important;
    margin: 0 !important;
}
.privacy-notice-body li {
    font-size: 10.5px !important;
    color: #64748b !important;
    line-height: 1.45 !important;
    padding-left: 10px !important;
    position: relative !important;
    margin: 0 0 2px 0 !important;
}
.privacy-notice-body li:last-child { margin-bottom: 0 !important; }
.privacy-notice-body li::before {
    content: '•';
    position: absolute;
    left: 0;
    color: #94a3b8;
}

/* ===== Action Cards (HERO — bigger & more prominent) ===== */
.action-cards-row {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 22px !important;
    margin-bottom: 28px !important;
}
.action-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px !important;
    padding: 32px 28px !important;
    transition: all 0.3s !important;
    display: flex !important;
    gap: 22px !important;
    align-items: flex-start !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.04) !important;
    position: relative !important;
    overflow: hidden !important;
}
.action-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
}
.action-card.legacy::before { background: #1a56db; }
.action-card.crisis::before { background: #ea580c; }
.action-card:hover {
    transform: translateY(-4px) !important;
    box-shadow: 0 16px 36px rgba(0,0,0,0.1) !important;
}
.action-card-icon {
    width: 64px !important;
    height: 64px !important;
    border-radius: 14px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-shrink: 0 !important;
    font-size: 30px !important;
}
.action-card.legacy .action-card-icon {
    background: #eff6ff !important;
    color: #1a56db !important;
}
.action-card.crisis .action-card-icon {
    background: #fff7ed !important;
    color: #ea580c !important;
}
.action-card-body { flex: 1 !important; }
.action-card-body h3 {
    font-size: 18px !important;
    font-weight: 800 !important;
    color: #0f172a !important;
    margin: 0 0 8px 0 !important;
}
.action-card-body p {
    font-size: 13.5px !important;
    color: #64748b !important;
    line-height: 1.6 !important;
    margin: 0 0 20px 0 !important;
}
.action-card-btn {
    display: block !important;
    width: 100% !important;
    padding: 13px 16px !important;
    border-radius: 10px !important;
    font-size: 14.5px !important;
    font-weight: 700 !important;
    text-align: center !important;
    color: #fff !important;
    text-decoration: none !important;
    transition: all 0.2s !important;
    border: none !important;
}
.action-card.legacy .action-card-btn { background: #1a56db !important; }
.action-card.legacy .action-card-btn:hover { background: #1245b8 !important; color: #fff !important; }
.action-card.crisis .action-card-btn { background: #ea580c !important; }
.action-card.crisis .action-card-btn:hover { background: #c2410c !important; color: #fff !important; }

/* ===== Stats Row ===== */
.stats-row {
    display: grid !important;
    grid-template-columns: 1fr 1fr 1fr !important;
    gap: 16px !important;
    margin-bottom: 24px !important;
}
.stat-tile {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 14px !important;
    padding: 18px !important;
    display: flex !important;
    align-items: center !important;
    gap: 14px !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03) !important;
}
.stat-tile-icon {
    width: 44px !important;
    height: 44px !important;
    border-radius: 10px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 20px !important;
    flex-shrink: 0 !important;
}
.stat-tile.tile-primary .stat-tile-icon { background: #eff6ff !important; color: #1a56db !important; }
.stat-tile.tile-success .stat-tile-icon { background: #ecfdf5 !important; color: #059669 !important; }
.stat-tile.tile-warning .stat-tile-icon { background: #fef9c3 !important; color: #b45309 !important; }
.stat-tile-value {
    font-size: 22px !important;
    font-weight: 800 !important;
    color: #0f172a !important;
    line-height: 1.1 !important;
}
.stat-tile-label {
    font-size: 12px !important;
    color: #64748b !important;
    margin-top: 2px !important;
}

@media (max-width: 992px) {
    .hero-row { grid-template-columns: 1fr !important; }
}
@media (max-width: 768px) {
    .action-cards-row { grid-template-columns: 1fr !important; }
    .stats-row { grid-template-columns: 1fr !important; }
    .welcome-icon { display: none !important; }
}
</style>

<div class="dashboard-wrap container-fluid py-3">

    {{-- ===== Hero Row: Welcome Banner + Privacy Notice (both compact) ===== --}}
    <div class="hero-row">
        <div class="welcome-banner">
            <div class="welcome-text">
                <h2>Assalamu'alaikum, {{ $student->first_name }} 👋</h2>
                <p>Your account is secure and protected. All data is end-to-end encrypted.</p>
                <div class="welcome-badges">
                    <span class="welcome-badge">
                        <i class="bi bi-person-vcard"></i> {{ $student->student_id ?? 'N/A' }}
                    </span>
                    <span class="welcome-badge">
                        <i class="bi bi-mortarboard-fill"></i> {{ $student->faculty ?? $student->program ?? 'Faculty' }}
                    </span>
                    <span class="welcome-badge">
                        <i class="bi bi-calendar3"></i> Year {{ $student->year_of_study ?? '—' }}
                    </span>
                </div>
            </div>
            <div class="welcome-icon">🎓</div>
        </div>

        <div class="privacy-notice">
            <div class="privacy-notice-icon">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
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

    {{-- ===== Action Cards (THE HERO) ===== --}}
    <div class="action-cards-row">
        <div class="action-card legacy">
            <div class="action-card-icon">
                <i class="bi bi-envelope-fill"></i>
            </div>
            <div class="action-card-body">
                <h3>Last Digital Message System</h3>
                <p>Create a secure farewell message (wasiat) to be delivered to designated recipients only upon verified confirmation of death.</p>
                <a href="{{ route('student.ldms.create') }}" class="action-card-btn">Create Message</a>
            </div>
        </div>

        <div class="action-card crisis">
            <div class="action-card-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
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

    {{-- ===== Recent Reports + Notifications (existing) ===== --}}
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="content-card">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="mb-0">My recent crisis reports</h5>
                    <a href="{{ route('student.crisis.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> New Report
                    </a>
                </div>
                @forelse($reports as $r)
                    <div class="list-row">
                        <div>
                            <div class="fw-semibold">Report #{{ $r->report_id }} — {{ ucwords(str_replace('_',' ', $r->crisis?->crisis_type ?? '')) }}</div>
                            <small class="text-muted">{{ $r->date_reported?->diffForHumans() }}</small>
                        </div>
                        <div class="text-end">
                            @php
                                $statusClass = ['pending'=>'warning','verified'=>'success','rejected'=>'danger'][$r->report_status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ strtoupper($r->report_status) }}</span>
                            <div><a href="{{ route('student.crisis.show', $r->report_id) }}" class="btn btn-link btn-sm">View</a></div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted text-center py-4">No reports submitted yet.</div>
                @endforelse
            </div>
        </div>

        <div class="col-lg-5">
            <div class="content-card">
                <h5 class="mb-3">Recent notifications</h5>
                @forelse($notifications as $n)
                    <a href="{{ $n->link ?? route('notifications.index') }}" class="notification-row text-decoration-none">
                        <div class="fw-semibold">{{ $n->subject }}</div>
                        <small class="text-muted d-block">{{ \Illuminate\Support\Str::limit($n->notification_message, 80) }}</small>
                        <small class="text-muted">{{ $n->timestamp?->diffForHumans() }}</small>
                    </a>
                @empty
                    <div class="text-muted text-center py-4">No notifications yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
