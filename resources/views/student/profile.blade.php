@extends('layouts.student')
@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="container-fluid py-3">
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="content-card text-center">
                <div class="profile-avatar">{{ strtoupper(substr($student->first_name,0,1) . substr($student->last_name,0,1)) }}</div>
                <h4 class="mt-3 mb-0">{{ $student->full_name }}</h4>
                <p class="text-muted small">{{ $student->student_id }}</p>
                <span class="badge bg-success">{{ ucfirst($student->status) }}</span>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="content-card">
                <h5 class="mb-3">Academic information</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Kulliyyah</dt><dd class="col-sm-8">{{ $student->kulliyyah ?? '—' }}</dd>
                    <dt class="col-sm-4">Programme</dt><dd class="col-sm-8">{{ $student->programme ?? '—' }}</dd>
                    <dt class="col-sm-4">Year of study</dt><dd class="col-sm-8">{{ $student->year_of_study ?? '—' }}</dd>
                    <dt class="col-sm-4">Mahallah</dt><dd class="col-sm-8">{{ $student->mahallah ?? '—' }}</dd>
                    <dt class="col-sm-4">Enrollment</dt><dd class="col-sm-8">{{ $student->enrollment_status ?? '—' }}</dd>
                </dl>
                <hr>
                <h5 class="mb-3">Contact</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $student->email }}</dd>
                    <dt class="col-sm-4">Phone</dt><dd class="col-sm-8">{{ $student->phone ?? '—' }}</dd>
                    <dt class="col-sm-4">Emergency</dt><dd class="col-sm-8">{{ $student->emergency_contact ?? '—' }}</dd>
                </dl>
                @if($student->imaalum_synced_at)
                    <p class="text-muted small mt-3 mb-0"><i class="bi bi-arrow-clockwise"></i> Last synced from iMaalum {{ $student->imaalum_synced_at->diffForHumans() }}</p>
                @endif
            </div>

            <div class="content-card mt-3">
                <h5 class="mb-3">Registered Next of Kin</h5>
                @forelse($student->nextOfKin as $nok)
                    <div class="list-row">
                        <div>
                            <div class="fw-semibold">{{ $nok->full_name }} <small class="text-muted">({{ $nok->relationship_to_student }})</small></div>
                            <small class="text-muted">{{ $nok->email }} &middot; {{ $nok->phone }}</small>
                        </div>
                        <div>
                            @if($nok->emergency_contact_verified)
                                <span class="badge bg-success">Verified</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No next of kin registered.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
