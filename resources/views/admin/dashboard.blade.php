@extends('layouts.admin')
@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid py-3">
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
        <div class="col-lg-6">
            <div class="content-card">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="mb-0">Crisis reports</h5>
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
                @empty
                    <p class="text-muted text-center my-4 small">No crises yet.</p>
                @endforelse
            </div>
        </div>

        <div class="col-lg-6">
            <div class="content-card">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="mb-0">Death reports</h5>
                    <a href="{{ route('admin.death.index') }}" class="btn btn-link btn-sm">View all</a>
                </div>
                @forelse($pendingDeaths as $d)
                    <div class="list-row">
                        <div>
                            <div class="fw-semibold">{{ $d->student?->full_name ?? '—' }} ({{ $d->student_id }})</div>
                            <small class="text-muted">{{ $d->nextOfKin?->name ?? 'NOK' }} &middot; {{ $d->date_triggered?->diffForHumans() }}</small>
                        </div>
                        <a href="{{ route('admin.death.show', $d->confirmation_id) }}" class="btn btn-sm btn-primary">Review</a>
                    </div>
                @empty
                    <p class="text-muted text-center my-4 small">No pending death reports. All caught up.</p>
                @endforelse
            </div>

            <div class="content-card mt-3">
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
