@extends('layouts.admin')
@section('title', 'Report #' . $report->report_id)
@section('page-title', 'Crisis Report #' . $report->report_id)

@section('content')
<div class="container-fluid py-3">
    <a href="{{ route('admin.crisis.index') }}" class="btn btn-link p-0 mb-3"><i class="bi bi-arrow-left"></i> Back to list</a>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="content-card">
                <div class="d-flex align-items-center mb-3">
                    @php $sc = ['pending'=>'warning','verified'=>'success','rejected'=>'danger'][$report->report_status] ?? 'secondary'; @endphp
                    <span class="badge bg-{{ $sc }} fs-6">{{ strtoupper($report->report_status) }}</span>
                    @if($report->crisis)<span class="ms-2"><x-priority-badge :level="$report->crisis->impact_level" /></span>@endif
                    <small class="text-muted ms-auto">Submitted {{ $report->date_reported?->format('d M Y, h:i A') }}</small>
                </div>

                <h4>{{ ucwords(str_replace('_',' ', $report->crisis?->crisis_type ?? '')) }}</h4>
                @if($report->crisis?->location)
                    <p class="text-muted small"><i class="bi bi-geo-alt"></i> {{ $report->crisis->location }}</p>
                @endif

                <h6 class="text-uppercase text-muted small">Public description</h6>
                <p>{{ $report->crisis?->crisis_description }}</p>

                @if($report->crisis?->crisis_details)
                    <h6 class="text-uppercase text-muted small">Additional details</h6>
                    <p class="alert alert-light border">{{ $report->crisis->crisis_details }}</p>
                @endif

                <h6 class="text-uppercase text-muted small">Student's personal statement</h6>
                <p>{{ $report->report_description }}</p>

                @if($report->supporting_evidence_path && count((array)$report->supporting_evidence_path) > 0)
                    <h6 class="text-uppercase text-muted small">Supporting evidence</h6>
                    <ul class="small">
                        @foreach((array)$report->supporting_evidence_path as $p)
                            <li><code>{{ basename($p) }}</code> <small class="text-muted">(stored encrypted at rest)</small></li>
                        @endforeach
                    </ul>
                @endif

                @if($report->admin_remarks)
                    <h6 class="text-uppercase text-muted small">Administrator's notes</h6>
                    <p class="alert alert-light border">{{ $report->admin_remarks }}</p>
                @endif

                @if($report->blockchain_hash)
                    <h6 class="text-uppercase text-muted small">Blockchain record</h6>
                    <x-blockchain-badge :hash="$report->blockchain_hash" />
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            @if($report->report_status === 'pending')
                <div class="content-card border-success">
                    <h5 class="text-success"><i class="bi bi-shield-check"></i> Verify Report</h5>
                    <form method="POST" action="{{ route('admin.crisis.verify', $report->report_id) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small">Confirm impact level</label>
                            <select name="impact_level" class="form-select form-select-sm">
                                @foreach(['low','medium','high','critical'] as $l)
                                    <option value="{{ $l }}" {{ ($report->crisis?->impact_level)===$l?'selected':'' }}>{{ ucfirst($l) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Donation target (RM)</label>
                            <input type="number" name="donation_target" min="0" max="1000000" step="100" value="{{ $report->crisis?->donation_target }}" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Internal remarks (optional)</label>
                            <textarea name="admin_remarks" rows="2" maxlength="2000" class="form-control form-control-sm"></textarea>
                        </div>
                        <button class="btn btn-success w-100"><i class="bi bi-check-circle"></i> Verify & Publish</button>
                    </form>
                </div>

                <div class="content-card border-danger mt-3">
                    <h5 class="text-danger"><i class="bi bi-x-circle"></i> Reject Report</h5>
                    <form method="POST" action="{{ route('admin.crisis.reject', $report->report_id) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small">Reason (required, min 10 chars)</label>
                            <textarea name="admin_remarks" rows="3" minlength="10" maxlength="2000" class="form-control form-control-sm" required></textarea>
                        </div>
                        <button class="btn btn-outline-danger w-100"><i class="bi bi-x-circle"></i> Reject</button>
                    </form>
                </div>
            @else
                <div class="content-card">
                    <h6 class="text-uppercase text-muted small">Reviewer</h6>
                    <p class="mb-0">{{ $report->verifier?->admin_name ?? 'System' }}</p>
                    @if($report->verified_at)
                        <small class="text-muted">{{ $report->verified_at->format('d M Y, h:i A') }}</small>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
