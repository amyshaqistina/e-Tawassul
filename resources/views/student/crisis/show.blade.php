@extends('layouts.student')
@section('title', 'Report #' . $report->report_id)
@section('page-title', 'Crisis Report #' . $report->report_id)

@push('styles')
<style>
    /* Card header — fits status/priority/date on one line */
    .student-report-header {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }
    .student-report-header .badge {
        font-size: 12px !important;
        padding: 6px 12px !important;
        font-weight: 700 !important;
        letter-spacing: 0.3px !important;
    }
    .student-report-header .submitted-meta {
        margin-left: auto;
        font-size: 12.5px;
        color: #64748b;
        white-space: nowrap;
    }
    .student-report-header .submitted-meta i {
        margin-right: 4px;
    }

    /* Section labels */
    .student-section-label {
        text-transform: uppercase;
        font-size: 11px;
        font-weight: 700;
        color: #94a3b8;
        letter-spacing: 0.5px;
        margin: 18px 0 8px 0;
    }
    .student-section-label:first-of-type { margin-top: 0; }

    /* Supporting documents */
    .student-evidence-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 10px;
    }
    .student-evidence-file {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8faff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 12px;
        text-decoration: none;
        color: inherit;
        transition: all 0.15s;
    }
    .student-evidence-file:hover {
        background: #eff6ff;
        border-color: #93c5fd;
        color: inherit;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(26,86,219,0.08);
    }
    .student-evidence-file .file-icon {
        font-size: 22px;
        color: #1a56db;
        flex-shrink: 0;
    }
    .student-evidence-file .file-info {
        flex: 1;
        min-width: 0;
    }
    .student-evidence-file .file-name {
        font-size: 12.5px;
        font-weight: 600;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .student-evidence-file .file-action {
        font-size: 10.5px;
        color: #64748b;
        margin-top: 2px;
    }
    .student-evidence-empty {
        font-size: 13px;
        color: #94a3b8;
        font-style: italic;
        padding: 8px 0;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <a href="{{ route('student.dashboard') }}" class="btn btn-link p-0 mb-3">
        <i class="bi bi-arrow-left"></i> Back to dashboard
    </a>

    <div class="row g-3">
        {{-- Main card — widened to col-lg-9 --}}
        <div class="col-lg-7">
            <div class="content-card">

                {{-- Header row: status + priority + submitted-on (all on one line) --}}
                <div class="student-report-header">
                    @php $sc = ['pending'=>'warning','verified'=>'success','rejected'=>'danger'][$report->report_status] ?? 'secondary'; @endphp
                    <span class="badge bg-{{ $sc }}">{{ strtoupper($report->report_status) }}</span>
                    @if($report->crisis)
                        <x-priority-badge :level="$report->crisis->impact_level" />
                    @endif
                    {{-- <div class="submitted-meta">
                        <i class="bi bi-clock"></i>
                        Submitted {{ $report->date_reported?->format('d M Y, h:i A') }}
                    </div> --}}
                </div>

                <h5 class="mb-2">{{ ucwords(str_replace('_',' ', $report->crisis?->crisis_type ?? '')) }}</h5>
                @if($report->crisis?->location)
                    <p class="text-muted small mb-3"><i class="bi bi-geo-alt"></i> {{ $report->crisis->location }}</p>
                @endif

                <div class="student-section-label">Public description</div>
                <p>{{ $report->crisis?->crisis_description }}</p>

                <div class="student-section-label">Your personal statement</div>
                <p>{{ $report->report_description }}</p>

                {{-- ===== SUPPORTING DOCUMENTS ===== --}}
                <div class="student-section-label">Supporting documents</div>
                @php
                    $evidence = (array) ($report->supporting_evidence_path ?? []);
                @endphp
                @if(count($evidence) > 0)
                    <div class="student-evidence-grid">
                        @foreach($evidence as $i => $p)
                            @php
                                $name = basename($p);
                                $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                                $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                $isPdf = $ext === 'pdf';
                                $icon  = $isImg ? 'bi-file-earmark-image'
                                      : ($isPdf ? 'bi-file-earmark-pdf'
                                      : 'bi-file-earmark-text');
                            @endphp
                            <a href="{{ route('student.crisis.evidence.download', [$report->report_id, $i]) }}"
                               target="_blank" rel="noopener"
                               class="student-evidence-file">
                                <i class="bi {{ $icon }} file-icon"></i>
                                <div class="file-info">
                                    <div class="file-name">{{ $name }}</div>
                                    <div class="file-action">
                                        <i class="bi bi-box-arrow-up-right"></i> Click to open
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="student-evidence-empty">No supporting documents were uploaded with this report.</div>
                @endif

                @if($report->admin_remarks)
                    <div class="student-section-label">Administrator's notes</div>
                    <p class="alert alert-light border mb-0">{{ $report->admin_remarks }}</p>
                @endif

                @if($report->blockchain_hash)
                    <div class="student-section-label">Blockchain proof</div>
                    <x-blockchain-badge :hash="$report->blockchain_hash" />
                @endif
            </div>
        </div>

        {{-- Right sidebar — narrowed to col-lg-3 --}}
        <div class="col-lg-5">
            <div class="content-card">
                <div class="student-section-label" style="margin-top:0;">Status timeline</div>
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
                    <div class="student-section-label" style="margin-top:0;">Funding</div>
                    <x-donation-progress :crisis="$report->crisis" />
                    <a href="{{ route('crisis.show', $report->crisis_id) }}" class="btn btn-link btn-sm p-0 mt-2">
                        View public page
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
