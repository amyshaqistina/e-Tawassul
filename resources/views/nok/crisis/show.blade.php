@extends('layouts.nok')
@section('title', 'Report #' . $report->report_id)
@section('page-title', 'Crisis Report #' . $report->report_id)
@section('page-subtitle', 'Filed on behalf of ' . ($report->student->full_name ?? 'student'))

@section('content')
<div class="container-fluid py-3">
    <a href="{{ route('nok.dashboard') }}" class="btn btn-link p-0 mb-3">
        <i class="bi bi-arrow-left"></i> Back to dashboard
    </a>

    @if(session('status'))
        <div class="alert alert-success border-0" style="background:#d1fae5; color:#065f46;">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('status') }}
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="content-card">
                <div class="d-flex align-items-center mb-3 flex-wrap gap-2">
                    @php $sc = ['pending'=>'warning','verified'=>'success','rejected'=>'danger'][$report->report_status] ?? 'secondary'; @endphp
                    <span class="badge bg-{{ $sc }} fs-6">{{ strtoupper($report->report_status) }}</span>
                    <span class="badge" style="background:#fff7ed; color:#c2410c; border:1px solid #fed7aa;">
                        <i class="bi bi-person-badge-fill me-1"></i>Submitted by NOK
                    </span>
                    <small class="text-muted ms-auto">Submitted {{ $report->date_reported?->format('d M Y, h:i A') }}</small>
                </div>

                <h5>{{ ucwords(str_replace('_',' ', $report->crisis?->crisis_type ?? '')) }}</h5>
                @if($report->crisis?->location)
                    <p class="text-muted small mb-3">
                        <i class="bi bi-geo-alt"></i> {{ $report->crisis->location }}
                    </p>
                @endif

                <h6 class="text-uppercase text-muted small mt-3">Filed on behalf of</h6>
                <p class="mb-3">
                    <strong>{{ $report->student?->full_name }}</strong>
                    @if($report->student?->student_id)
                        <span class="text-muted">&middot; {{ $report->student->student_id }}</span>
                    @endif
                </p>

                <h6 class="text-uppercase text-muted small">Description</h6>
                <p>{{ $report->report_description }}</p>

                @if($report->admin_remarks)
                    <h6 class="text-uppercase text-muted small">Administrator's notes</h6>
                    <p class="alert alert-light border">{{ $report->admin_remarks }}</p>
                @endif

                @if($report->blockchain_hash)
                    <h6 class="text-uppercase text-muted small">Blockchain proof</h6>
                    @if(class_exists(\App\View\Components\BlockchainBadge::class))
                        <x-blockchain-badge :hash="$report->blockchain_hash" />
                    @else
                        <code class="small text-muted">{{ $report->blockchain_hash }}</code>
                    @endif
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
                        <div class="timeline-dot bg-{{ in_array($report->report_status,['verified','rejected']) ? ($report->report_status === 'rejected' ? 'danger' : 'success') : 'secondary' }}"></div>
                        <div>
                            <strong>Admin review</strong>
                            <div class="small text-muted">{{ $report->verified_at?->format('d M Y, h:i A') ?? 'Pending' }}</div>
                        </div>
                    </div>
                    @if($report->report_status === 'verified')
                        <div class="timeline-item done">
                            <div class="timeline-dot bg-success"></div>
                            <div>
                                <strong>Verified</strong>
                                <div class="small text-muted">Approved by administrator</div>
                            </div>
                        </div>
                    @elseif($report->report_status === 'rejected')
                        <div class="timeline-item done">
                            <div class="timeline-dot bg-danger"></div>
                            <div>
                                <strong>Rejected</strong>
                                <div class="small text-muted">See administrator's notes</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($report->report_status === 'pending')
                <div class="content-card mt-3" style="background:#fffbeb; border:1px solid #fde68a;">
                    <h6 class="mb-2" style="color:#92400e;">
                        <i class="bi bi-info-circle-fill"></i> What happens next?
                    </h6>
                    <p class="small mb-0" style="color:#78350f;">
                        Our administrators will review your report. You will be notified by email once a decision has been made. Verification usually takes 1&ndash;2 business days.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
