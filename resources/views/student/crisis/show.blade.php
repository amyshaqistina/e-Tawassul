@extends('layouts.student')
@section('title', 'Report #' . $report->report_id)
@section('page-title', 'Crisis Report #' . $report->report_id)

@section('content')
<div class="container-fluid py-3">
    <a href="{{ route('student.dashboard') }}" class="btn btn-link p-0 mb-3"><i class="bi bi-arrow-left"></i> Back to dashboard</a>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="content-card">
                <div class="d-flex align-items-center mb-3">
                    @php $sc = ['pending'=>'warning','verified'=>'success','rejected'=>'danger'][$report->report_status] ?? 'secondary'; @endphp
                    <span class="badge bg-{{ $sc }} fs-6">{{ strtoupper($report->report_status) }}</span>
                    @if($report->crisis)
                        <span class="ms-2"><x-priority-badge :level="$report->crisis->impact_level" /></span>
                    @endif
                    <small class="text-muted ms-auto">Submitted {{ $report->date_reported?->format('d M Y, h:i A') }}</small>
                </div>

                <h5>{{ ucwords(str_replace('_',' ', $report->crisis?->crisis_type ?? '')) }}</h5>
                @if($report->crisis?->location)
                    <p class="text-muted small"><i class="bi bi-geo-alt"></i> {{ $report->crisis->location }}</p>
                @endif

                <h6 class="text-uppercase text-muted small mt-3">Public description</h6>
                <p>{{ $report->crisis?->crisis_description }}</p>

                <h6 class="text-uppercase text-muted small">Your personal statement</h6>
                <p>{{ $report->report_description }}</p>

                @if($report->admin_remarks)
                    <h6 class="text-uppercase text-muted small">Administrator's notes</h6>
                    <p class="alert alert-light border">{{ $report->admin_remarks }}</p>
                @endif

                @if($report->blockchain_hash)
                    <h6 class="text-uppercase text-muted small">Blockchain proof</h6>
                    <x-blockchain-badge :hash="$report->blockchain_hash" />
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="content-card">
                <h6 class="text-uppercase text-muted small mb-3">Status timeline</h6>
                <div class="timeline">
                    <div class="timeline-item done">
                        <div class="timeline-dot bg-primary"></div>
                        <div>
                            <strong>Submitted</strong>
                            <div class="small text-muted">{{ $report->date_reported?->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                    <div class="timeline-item {{ in_array($report->report_status,['verified','rejected'])?'done':'' }}">
                        <div class="timeline-dot bg-{{ in_array($report->report_status,['verified','rejected'])?'success':'secondary' }}"></div>
                        <div>
                            <strong>Admin review</strong>
                            <div class="small text-muted">{{ $report->verified_at?->format('d M Y, h:i A') ?? 'Pending' }}</div>
                        </div>
                    </div>
                    @if($report->report_status === 'verified')
                        <div class="timeline-item done">
                            <div class="timeline-dot bg-success"></div>
                            <div>
                                <strong>Case active</strong>
                                <div class="small text-muted">Open for community support</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($report->crisis && $report->report_status === 'verified')
                <div class="content-card mt-3">
                    <h6 class="text-uppercase text-muted small mb-3">Funding</h6>
                    <x-donation-progress :crisis="$report->crisis" />
                    <a href="{{ route('crisis.show', $report->crisis_id) }}" class="btn btn-link btn-sm p-0 mt-2">View public page</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
