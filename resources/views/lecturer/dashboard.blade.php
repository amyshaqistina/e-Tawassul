@extends('layouts.lecturer')
@section('title', 'Lecturer Dashboard')
@section('page-title', 'Welcome, ' . $lecturer->first_name)
@section('page-subtitle', $lecturer->department)

@section('content')
<div class="container-fluid py-3">
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="stat-card stat-warning">
                <div class="stat-card-icon"><i class="bi bi-bell"></i></div>
                <div>
                    <div class="stat-card-value">{{ $unreadCount }}</div>
                    <div class="stat-card-label">Unread notifications</div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-card">
        <h5 class="mb-3">All notifications</h5>
        @forelse($notifications as $n)
            <a href="{{ $n->link ?? '#' }}" class="notification-row text-decoration-none {{ $n->read_at ? '' : 'unread' }}">
                <div class="d-flex justify-content-between">
                    <div class="fw-semibold">{{ $n->subject }}</div>
                    <small class="text-muted">{{ $n->timestamp?->diffForHumans() }}</small>
                </div>
                <div class="small text-muted">{{ $n->notification_message }}</div>
            </a>
        @empty
            <div class="text-muted text-center py-5">
                <i class="bi bi-bell-slash fs-1 text-secondary"></i>
                <p class="mb-0 mt-2">No notifications yet.</p>
            </div>
        @endforelse
        {{ $notifications->links() }}
    </div>
</div>
@endsection
