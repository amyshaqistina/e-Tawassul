@extends('layouts.nok')
@section('title', 'NOK Dashboard')
@section('page-title', 'Welcome, ' . $nok->first_name)
@section('page-subtitle', $student ? 'Linked to student: ' . $student->full_name . ' (' . $student->student_id . ')' : '')

@section('content')
<div class="container-fluid py-3">
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
                <h5 class="mb-3">My submissions</h5>
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
