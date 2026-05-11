@php
    // Choose the matching layout based on which guard is logged in
    $layout = match($type) {
        'student'  => 'layouts.student',
        'admin'    => 'layouts.admin',
        'nok'      => 'layouts.nok',
        'lecturer' => 'layouts.lecturer',
        default    => 'layouts.public',
    };
@endphp

@extends($layout)
@section('title', 'Notifications')
@section('page-title', 'All Notifications')

@section('content')
<div class="container-fluid py-3">
    <div class="content-card">
        @if($notifications->isEmpty())
            <div class="text-muted text-center py-5">
                <i class="bi bi-bell-slash fs-1 text-secondary"></i>
                <p class="mb-0 mt-2">No notifications yet.</p>
            </div>
        @else
            @foreach($notifications as $n)
                <a href="{{ $n->link ?? '#' }}"
                   class="notification-row text-decoration-none {{ $n->read_at ? '' : 'unread' }}"
                   data-notif-id="{{ $n->notification_id }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="fw-semibold">{{ $n->subject }}</div>
                        @if(!$n->read_at)
                            <span class="badge bg-primary ms-2">NEW</span>
                        @endif
                    </div>
                    <div class="small text-muted">{{ $n->notification_message }}</div>
                    <div class="small text-muted mt-1">
                        <i class="bi bi-clock"></i> {{ $n->timestamp?->format('d M Y, h:i A') }}
                        &middot; <span class="text-uppercase">{{ str_replace('_', ' ', $n->notification_type) }}</span>
                    </div>
                </a>
            @endforeach
            {{ $notifications->links() }}
        @endif
    </div>
</div>
@endsection
