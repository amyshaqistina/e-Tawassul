@extends('layouts.admin')
@section('title', 'Admin Dashboard')

@push('head')
<style>
    .fraud-alert-card {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border: 1px solid #fecaca;
        border-left: 4px solid #dc2626;
        border-radius: 14px;
        padding: 18px 22px;
        margin-bottom: 20px;
    }
    .fraud-alert-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }
    .fraud-alert-header i {
        color: #dc2626;
        font-size: 22px;
    }
    .fraud-alert-header h5 {
        margin: 0;
        color: #991b1b;
        font-weight: 700;
        font-size: 16px;
    }
    .fraud-alert-count {
        background: #dc2626;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 9px;
        border-radius: 99px;
    }
    .fraud-alert-sub {
        margin: 0 0 14px;
        color: #7f1d1d;
        font-size: 13px;
    }
    .fraud-row {
        background: #fff;
        border: 1px solid #fecaca;
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .fraud-row:last-child { margin-bottom: 0; }
    .fraud-row-avatar {
        width: 38px; height: 38px;
        border-radius: 50%;
        background: #dc2626;
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 14px;
        flex-shrink: 0;
    }
    .fraud-row-main { flex: 1; min-width: 0; }
    .fraud-row-name {
        font-weight: 600;
        color: #0f172a;
        font-size: 14px;
        margin-bottom: 2px;
    }
    .fraud-row-reasons {
        display: flex; gap: 6px; flex-wrap: wrap;
    }
    .fraud-reason-chip {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 6px;
        font-weight: 600;
    }
    .fraud-reason-chip.rejection { background: #fee2e2; color: #991b1b; }
    .fraud-reason-chip.rapid     { background: #fef3c7; color: #92400e; }
    .fraud-row-btn {
        background: #fff;
        color: #991b1b;
        border: 1.5px solid #fecaca;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 12px;
        text-decoration: none;
        white-space: nowrap;
    }
    .fraud-row-btn:hover {
        background: #fee2e2;
        color: #7f1d1d;
        border-color: #f87171;
    }

    /* Make recent-crises rows clickable with hover affordance */
    .list-row-link {
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-decoration: none !important;
        color: inherit !important;
        cursor: pointer;
        border-radius: 8px;
        padding: 10px 12px;
        margin: 0 -12px;
        transition: background .15s ease, transform .12s ease;
    }
    .list-row-link:hover {
        background: #f1f5f9;
        transform: translateX(2px);
    }
    .list-row-link:hover .fw-semibold {
        color: #1d4ed8;
    }
</style>
@endpush

@section('content')
<div class="container-fluid pb-3">

    @if (!empty($suspiciousStudents) && $suspiciousStudents->count() > 0)
        <div class="fraud-alert-card">
            <div class="fraud-alert-header">
                <i class="bi bi-shield-exclamation"></i>
                <h5>Needs your attention</h5>
                <span class="fraud-alert-count">{{ $suspiciousStudents->count() }}</span>
            </div>
            <p class="fraud-alert-sub">
                These students show patterns that warrant a closer look — multiple rejected reports, or rapid submissions. Review each case carefully before approving.
            </p>
            @foreach ($suspiciousStudents->take(5) as $entry)
                @php
                    $stud = $entry['student'];
                    $initials = strtoupper(substr($stud->first_name ?? $stud->student_id, 0, 1) . substr($stud->last_name ?? '', 0, 1));
                @endphp
                <div class="fraud-row">
                    <div class="fraud-row-avatar">{{ $initials ?: '?' }}</div>
                    <div class="fraud-row-main">
                        <div class="fraud-row-name">
                            {{ $stud->full_name ?? ($stud->first_name . ' ' . $stud->last_name) }}
                            <span style="color:#64748b; font-weight:400;">· {{ $stud->student_id }}</span>
                        </div>
                        <div class="fraud-row-reasons">
                            @foreach ($entry['reasons'] as $r)
                                <span class="fraud-reason-chip {{ $r['type'] }}">
                                    @if ($r['type'] === 'rejection')
                                        <i class="bi bi-x-circle"></i>
                                    @else
                                        <i class="bi bi-lightning-charge"></i>
                                    @endif
                                    {{ $r['text'] }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <a href="{{ route('admin.students.show', $stud->student_id) }}" class="fraud-row-btn">
                        <i class="bi bi-eye"></i> Review Student
                    </a>
                </div>
            @endforeach
            @if ($suspiciousStudents->count() > 5)
                <p style="margin:10px 0 0; text-align:right; font-size:12px; color:#7f1d1d;">
                    + {{ $suspiciousStudents->count() - 5 }} more students flagged
                </p>
            @endif
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-warning">
                <div class="stat-card-icon"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="stat-card-value">{{ $stats['pending_reports'] }}</div>
                    <div class="stat-card-label">Pending Reports</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-success">
                <div class="stat-card-icon"><i class="bi bi-shield-check"></i></div>
                <div>
                    <div class="stat-card-value">{{ $stats['active_crises'] }}</div>
                    <div class="stat-card-label">Active Crises</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-info">
                <div class="stat-card-icon"><i class="bi bi-people"></i></div>
                <div>
                    <div class="stat-card-value">{{ $stats['total_students'] }}</div>
                    <div class="stat-card-label">Students Tracked</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-primary">
                <div class="stat-card-icon"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="stat-card-value">RM {{ number_format($stats['total_donations'], 0) }}</div>
                    <div class="stat-card-label">Donations ({{ $stats['donations_count'] }})</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-secondary">
                <div class="stat-card-icon"><i class="bi bi-file-earmark-medical"></i></div>
                <div>
                    <div class="stat-card-value">{{ $stats['pending_deaths'] }} / {{ $stats['verified_deaths'] }}</div>
                    <div class="stat-card-label">Pending / Verified Deaths</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-teal">
                <div class="stat-card-icon"><i class="bi bi-envelope-paper"></i></div>
                <div>
                    <div class="stat-card-value">{{ $stats['released_ldms'] }}</div>
                    <div class="stat-card-label">Released LDMS</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-primary">
                <div class="stat-card-icon"><i class="bi bi-check2-circle"></i></div>
                <div>
                    <div class="stat-card-value">{{ $stats['verified_reports'] }}</div>
                    <div class="stat-card-label">Verified Reports</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-success">
                <div class="stat-card-icon"><i class="bi bi-person-check"></i></div>
                <div>
                    <div class="stat-card-value">{{ $stats['active_students'] }}</div>
                    <div class="stat-card-label">Active Students</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="content-card">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="mb-0">Pending crisis reports</h5>
                    <a href="{{ route('admin.crisis.index') }}" class="btn btn-link btn-sm">View all</a>
                </div>
                @forelse($pendingReports as $r)
                    <div class="list-row">
                        <div>
                            <div class="fw-semibold">{{ $r->student?->full_name ?? '—' }} ({{ $r->student_id }})</div>
                            <small class="text-muted">{{ ucwords(str_replace('_',' ', $r->crisis?->crisis_type ?? '')) }} &middot; {{ $r->date_reported?->diffForHumans() }}</small>
                        </div>
                        <a href="{{ route('admin.crisis.show', $r->report_id) }}" class="btn btn-sm btn-primary">Review</a>
                    </div>
                @empty
                    <p class="text-muted text-center my-4 small">No pending reports. All caught up.</p>
                @endforelse
            </div>

            <div class="content-card mt-3">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="mb-0">Recent verified crises</h5>
                </div>
                @forelse($recentCrises as $c)
                    @php $latestReport = $c->reports->first(); @endphp
                    @if ($latestReport)
                        <a href="{{ route('admin.crisis.show', $latestReport->report_id) }}"
                           class="list-row list-row-link"
                           title="View case details">
                            <div>
                                <div class="fw-semibold">{{ ucwords(str_replace('_',' ', $c->crisis_type)) }}</div>
                                <small class="text-muted">{{ $c->student?->full_name ?? '—' }} &middot; {{ $c->date_reported?->diffForHumans() }}</small>
                            </div>
                            <div class="text-end">
                                <x-priority-badge :level="$c->impact_level" />
                                <div><small class="text-muted">RM {{ number_format($c->donation_raised, 0) }} / {{ number_format($c->donation_target, 0) }}</small></div>
                            </div>
                        </a>
                    @else
                        <div class="list-row">
                            <div>
                                <div class="fw-semibold">{{ ucwords(str_replace('_',' ', $c->crisis_type)) }}</div>
                                <small class="text-muted">{{ $c->student?->full_name ?? '—' }} &middot; {{ $c->date_reported?->diffForHumans() }}</small>
                            </div>
                            <div class="text-end">
                                <x-priority-badge :level="$c->impact_level" />
                                <div><small class="text-muted">RM {{ number_format($c->donation_raised, 0) }} / {{ number_format($c->donation_target, 0) }}</small></div>
                            </div>
                        </div>
                    @endif
                @empty
                    <p class="text-muted text-center my-4 small">No crises yet.</p>
                @endforelse
            </div>
        </div>

        <div class="col-lg-5">
            <div class="content-card">
                <h5 class="mb-3">Recent activity</h5>
                <ul class="activity-feed">
                    @foreach($recentActivity as $a)
                        <li>
                            <div class="activity-dot"></div>
                            <div>
                                <strong>{{ ucfirst($a->user_type) }}</strong>
                                <small class="text-muted">{{ $a->action }}</small>
                                <div class="small text-muted">{{ $a->action_description }}</div>
                                <small class="text-muted">{{ $a->timestamp?->diffForHumans() }}</small>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
