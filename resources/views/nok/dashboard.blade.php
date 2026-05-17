@extends('layouts.nok')
@section('title', 'NOK Dashboard')
@section('page-title', 'Welcome, ' . $nok->first_name)
@section('page-subtitle', $student ? 'Linked to student: ' . $student->full_name . ' (' . $student->student_id . ')' : '')

@push('styles')
<style>
    /* ===== Action cards (Death confirmation + Crisis reporting) ===== */
    .action-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 22px 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        position: relative;
        overflow: hidden;
        height: 100%;
    }
    .action-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
    }
    .action-card.death::before  { background: #1a56db; }
    .action-card.crisis::before { background: #ea580c; }

    .action-card-head {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        margin-bottom: 14px;
    }
    .action-card-icon {
        width: 44px; height: 44px;
        border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .action-card.death .action-card-icon   { background: #eff6ff; color: #1a56db; }
    .action-card.crisis .action-card-icon  { background: #fff7ed; color: #ea580c; }

    .action-card h5 {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 2px 0;
    }
    .action-card .action-card-sub {
        font-size: 13px;
        color: #64748b;
        margin: 0 0 14px 0;
        line-height: 1.55;
    }
    .action-card .btn-action {
        font-size: 13px;
        font-weight: 600;
        padding: 9px 16px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: background 0.15s;
    }
    .action-card.death .btn-action   { background: #1a56db; color: #fff; }
    .action-card.death .btn-action:hover  { background: #1245b8; }
    .action-card.crisis .btn-action  { background: #ea580c; color: #fff; }
    .action-card.crisis .btn-action:hover { background: #c2410c; }

    /* ===== Recent crisis reports list ===== */
    .crisis-report-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border-left: 3px solid transparent;
        border-radius: 8px;
        background: #f8faff;
        margin-bottom: 8px;
        text-decoration: none;
        color: inherit;
        transition: background 0.15s;
    }
    .crisis-report-row:hover { background: #eff6ff; }
    .crisis-report-row.pending  { border-left-color: #f59e0b; background: #fffbeb; }
    .crisis-report-row.verified { border-left-color: #10b981; background: #ecfdf5; }
    .crisis-report-row.rejected { border-left-color: #dc2626; background: #fef2f2; }

    .crisis-report-row .cr-title {
        font-weight: 600;
        font-size: 14px;
        color: #0f172a;
        margin: 0 0 2px 0;
    }
    .crisis-report-row .cr-meta {
        font-size: 12px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .crisis-status-badge {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        padding: 4px 9px;
        border-radius: 999px;
    }
    .crisis-status-badge.pending  { background: #fef3c7; color: #92400e; }
    .crisis-status-badge.verified { background: #d1fae5; color: #065f46; }
    .crisis-status-badge.rejected { background: #fee2e2; color: #991b1b; }

    /* ===== Status timeline ===== */
    .status-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 22px; height: 100%; }
    .status-card .status-card-head {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
        margin-bottom: 14px;
    }
    .status-card .latest-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 999px;
        margin-bottom: 14px;
    }
    .status-card .latest-badge.pending  { background: #fef3c7; color: #92400e; }
    .status-card .latest-badge.verified { background: #d1fae5; color: #065f46; }
    .status-card .latest-badge.rejected { background: #fee2e2; color: #991b1b; }

    .timeline-mini { position: relative; padding-left: 24px; }
    .timeline-mini::before {
        content: '';
        position: absolute;
        left: 6px; top: 6px; bottom: 6px;
        width: 2px;
        background: #e2e8f0;
    }
    .timeline-mini-item { position: relative; padding-bottom: 18px; }
    .timeline-mini-item:last-child { padding-bottom: 0; }
    .timeline-mini-item::before {
        content: '';
        position: absolute;
        left: -22px;
        top: 4px;
        width: 14px; height: 14px;
        border-radius: 50%;
        background: #cbd5e1;
        border: 3px solid #fff;
        box-shadow: 0 0 0 1px #cbd5e1;
    }
    .timeline-mini-item.done::before    { background: #1a56db; box-shadow: 0 0 0 1px #1a56db; }
    .timeline-mini-item.success::before { background: #10b981; box-shadow: 0 0 0 1px #10b981; }
    .timeline-mini-item.danger::before  { background: #dc2626; box-shadow: 0 0 0 1px #dc2626; }
    .timeline-mini-item h6 {
        font-size: 13.5px; font-weight: 700; color: #0f172a;
        margin: 0 0 2px 0;
    }
    .timeline-mini-item p {
        font-size: 12px; color: #64748b; margin: 0;
    }

    /* Stat squares (Recent reports / Legacy / Notifications) */
    .stat-tile {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .stat-tile-icon {
        width: 42px; height: 42px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .stat-tile-icon.crisis     { background: #fff7ed; color: #ea580c; }
    .stat-tile-icon.legacy     { background: #eff6ff; color: #1a56db; }
    .stat-tile-icon.notif      { background: #fef9c3; color: #ca8a04; }
    .stat-tile h3 {
        margin: 0; font-size: 22px; font-weight: 800; color: #0f172a; line-height: 1;
    }
    .stat-tile p {
        margin: 4px 0 0 0; font-size: 12px; color: #64748b;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- ===== Linked student card ===== --}}
    @if($student)
        <div class="content-card mb-3">
            <h6 class="text-uppercase text-muted small">Linked student</h6>
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0">{{ $student->full_name }}</h5>
                    <p class="text-muted small mb-0">{{ $student->student_id }} &middot; {{ $student->kulliyyah }}</p>
                </div>
                @if($student->status === 'deceased')
                    <span class="badge bg-dark">Deceased</span>
                @else
                    <span class="badge bg-success">{{ ucfirst($student->status ?? 'Active') }}</span>
                @endif
            </div>
        </div>
    @endif

    {{-- ===== Two big action cards: LDMS confirmation + Crisis reporting ===== --}}
    @if($student)
        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="action-card death">
                    <div class="action-card-head">
                        <div class="action-card-icon">
                            <i class="bi bi-file-earmark-medical-fill"></i>
                        </div>
                        <div>
                            <h5>Death Confirmation</h5>
                            <p class="action-card-sub mb-0">Submit official confirmation of a student's passing to trigger the release of their legacy messages.</p>
                        </div>
                    </div>
                    <a href="{{ route('nok.death.create') }}" class="btn-action">
                        <i class="bi bi-plus-circle"></i> Submit Confirmation
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="action-card crisis">
                    <div class="action-card-head">
                        <div class="action-card-icon">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <div>
                            <h5>Crisis Reporting &amp; Notifications</h5>
                            <p class="action-card-sub mb-0">Report accidents, illness, or emergencies on behalf of {{ $student->first_name ?? 'the student' }}. Your report will be securely verified and processed by university staff.</p>
                        </div>
                    </div>
                    <a href="{{ route('nok.crisis.create') }}" class="btn-action">
                        <i class="bi bi-flag"></i> Report Crisis
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== Stat tiles ===== --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="stat-tile">
                <div class="stat-tile-icon crisis"><i class="bi bi-clipboard-pulse"></i></div>
                <div>
                    <h3>{{ $myCrisisReports->count() }}</h3>
                    <p>Crisis Reports</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-tile">
                <div class="stat-tile-icon legacy"><i class="bi bi-envelope-open"></i></div>
                <div>
                    <h3>{{ $releasedLdms->count() }}</h3>
                    <p>Released Messages</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-tile">
                <div class="stat-tile-icon notif"><i class="bi bi-bell"></i></div>
                <div>
                    <h3>{{ $unreadCount }}</h3>
                    <p>Unread Notifications</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Recent crisis reports + latest status timeline ===== --}}
    @if($myCrisisReports->isNotEmpty())
        <div class="row g-3 mb-3">
            <div class="col-lg-7">
                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-baseline mb-3 flex-wrap gap-2">
                        <h5 class="mb-0">My recent crisis reports</h5>
                        <small class="text-muted">Click a report to see its full timeline</small>
                    </div>
                    @foreach($myCrisisReports as $r)
                        <a href="{{ route('nok.crisis.show', $r->report_id) }}" class="crisis-report-row {{ $r->report_status }}">
                            <div class="flex-grow-1 min-w-0">
                                <p class="cr-title">
                                    Report #{{ $r->report_id }}
                                    @if($r->crisis?->crisis_type)
                                        &mdash; {{ ucwords(str_replace('_',' ', $r->crisis->crisis_type)) }}
                                    @endif
                                </p>
                                <div class="cr-meta">
                                    <i class="bi bi-clock"></i>
                                    <span>{{ $r->date_reported?->diffForHumans() }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="crisis-status-badge {{ $r->report_status }}">{{ strtoupper($r->report_status) }}</span>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-5">
                @if($latestCrisisReport)
                    <div class="status-card">
                        <div class="d-flex align-items-baseline justify-content-between mb-2">
                            <span class="status-card-head">Latest report status</span>
                            <small class="text-muted">#{{ $latestCrisisReport->report_id }}</small>
                        </div>
                        @php
                            $latestStatus = $latestCrisisReport->report_status;
                            $statusIcon = ['pending'=>'hourglass-split','verified'=>'check-circle-fill','rejected'=>'x-circle-fill'][$latestStatus] ?? 'circle';
                        @endphp
                        <span class="latest-badge {{ $latestStatus }}">
                            <i class="bi bi-{{ $statusIcon }}"></i> {{ strtoupper($latestStatus) }}
                        </span>

                        <div class="timeline-mini mt-3">
                            <div class="timeline-mini-item done">
                                <h6>Submitted</h6>
                                <p>{{ $latestCrisisReport->date_reported?->format('d M Y, h:i A') }}</p>
                            </div>
                            <div class="timeline-mini-item {{ in_array($latestStatus,['verified','rejected']) ? ($latestStatus === 'rejected' ? 'danger' : 'done') : '' }}">
                                <h6>Admin Review</h6>
                                <p>{{ $latestCrisisReport->verified_at?->diffForHumans() ?? 'Pending review' }}</p>
                            </div>
                            @if($latestStatus === 'verified')
                                <div class="timeline-mini-item success">
                                    <h6>Verified</h6>
                                    <p>Approved by administrator</p>
                                </div>
                            @elseif($latestStatus === 'rejected')
                                <div class="timeline-mini-item danger">
                                    <h6>Rejected</h6>
                                    <p>Additional information needed</p>
                                </div>
                            @endif
                        </div>

                        <div class="text-end mt-3">
                            <a href="{{ route('nok.crisis.show', $latestCrisisReport->report_id) }}" class="btn btn-outline-primary btn-sm">
                                View full report <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ===== Existing dashboard: LDMS + Death confirmations + Notifications ===== --}}
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="content-card">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="mb-0">Released messages (LDMS)</h5>
                </div>
                @forelse($releasedLdms as $m)
                    <div class="list-row">
                        <div>
                            <div class="fw-semibold">Message #{{ $m->ldms_id }}</div>
                            <small class="text-muted">{{ strtoupper($m->media_type) }} &middot; Released {{ $m->date_triggered?->diffForHumans() }}</small>
                        </div>
                        <a href="{{ route('nok.ldms.show', $m->ldms_id) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-envelope-open"></i> Open
                        </a>
                    </div>
                @empty
                    <p class="text-muted small text-center my-4">No messages have been released yet.</p>
                @endforelse
            </div>

            <div class="content-card mt-3">
                <h5 class="mb-3">My death confirmations</h5>
                @forelse($myConfirmations as $c)
                    <div class="list-row">
                        <div>
                            <div class="fw-semibold">Confirmation #{{ $c->confirmation_id }}</div>
                            <small class="text-muted">Submitted {{ $c->date_triggered?->diffForHumans() }}</small>
                        </div>
                        @php $sc = ['pending'=>'warning','verified'=>'success','rejected'=>'danger'][$c->status] ?? 'secondary'; @endphp
                        <span class="badge bg-{{ $sc }}">{{ strtoupper($c->status) }}</span>
                    </div>
                @empty
                    <p class="text-muted small text-center my-4">You haven't submitted any confirmations yet.</p>
                @endforelse
                <a href="{{ route('nok.death.create') }}" class="btn btn-outline-primary btn-sm mt-2">
                    <i class="bi bi-plus-circle"></i> Submit a death confirmation
                </a>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="content-card">
                <h5 class="mb-3">Recent notifications</h5>
                @forelse($notifications as $n)
                    <a href="{{ $n->link ?? route('notifications.index') }}" class="notification-row text-decoration-none">
                        <div class="fw-semibold">{{ $n->subject }}</div>
                        <small class="text-muted d-block">{{ \Illuminate\Support\Str::limit($n->notification_message, 100) }}</small>
                        <small class="text-muted">{{ $n->timestamp?->diffForHumans() }}</small>
                    </a>
                @empty
                    <p class="text-muted small text-center my-4">No notifications yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
