@extends('layouts.student')
@section('title', 'Student Dashboard')
@section('page-title', 'Welcome, ' . $student->first_name)
@section('page-subtitle', 'Your e-Tawassul home')

@section('content')
<div class="container-fluid py-3">

    @if($scrapeStale)
        <div class="alert alert-info d-flex align-items-center">
            <i class="bi bi-arrow-repeat me-2 fs-4"></i>
            <div class="flex-grow-1">
                <strong>iMaalum sync in progress</strong>
                <div class="small text-muted">Your profile data is being refreshed from iMaalum. This will run in the background.</div>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card stat-primary">
                <div class="stat-card-icon"><i class="bi bi-clipboard-pulse"></i></div>
                <div>
                    <div class="stat-card-value">{{ $reports->count() }}</div>
                    <div class="stat-card-label">Recent Reports</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-success">
                <div class="stat-card-icon"><i class="bi bi-envelope-paper"></i></div>
                <div>
                    <div class="stat-card-value">{{ $ldmsCount }}</div>
                    <div class="stat-card-label">Legacy Messages</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-warning">
                <div class="stat-card-icon"><i class="bi bi-bell-fill"></i></div>
                <div>
                    <div class="stat-card-value">{{ $unreadCount }}</div>
                    <div class="stat-card-label">Unread Notifications</div>
                </div>
            </div>
        </div>
    </div>

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
