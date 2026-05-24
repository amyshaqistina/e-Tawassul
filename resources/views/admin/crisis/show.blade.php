@extends('layouts.admin')
@section('title', 'Report #' . $report->report_id)
@section('page-title', 'Crisis Report Review')

@php
    $typeLabels = [
        'medical' => 'Medical Emergency',
        'illness' => 'Medical Emergency',
        'accident' => 'Accident',
        'natural_disaster' => 'Natural Disaster',
        'death' => 'Death / Bereavement',
        'family_emergency' => 'Family Emergency',
    ];

    $subCategoryMap = [
        'medical' => [
            'sudden_illness' => 'Sudden Serious Illness',
            'mental_health' => 'Mental Health Crisis',
            'hospitalization' => 'Hospitalization',
            'surgery_required' => 'Surgery Required',
            'chronic_flare' => 'Chronic Condition Flare-up',
            'family_critical' => 'Family Member Critical Illness',
        ],
        'accident' => [
            'road_accident' => 'Road Accident',
            'lab_workshop' => 'Lab / Workshop Accident',
            'sports_injury' => 'Sports Injury',
            'fall_fracture' => 'Fall / Fracture',
            'burn_electrical' => 'Burn / Electrical Injury',
            'house_fire' => 'House Fire',
            'drowning' => 'Drowning / Near-drowning',
        ],
        'natural_disaster' => [
            'flood' => 'Flood',
            'landslide' => 'Landslide',
            'fire' => 'Forest / Building Fire',
            'storm' => 'Storm / Heavy Rain',
            'haze' => 'Haze',
            'earthquake' => 'Earthquake',
            'strong_wind' => 'Strong Wind',
        ],
        'death' => [
            'parent' => 'Parent',
            'sibling' => 'Sibling',
            'grandparent' => 'Grandparent',
            'close_relative' => 'Close Relative',
            'guardian' => 'Guardian',
            'spouse' => 'Spouse',
            'close_friend' => 'Close Friend / Coursemate / Roommate',
        ],
    ];
    $subCategoryMap['illness'] = $subCategoryMap['medical'];

    $extra = [];
    if ($report->crisis?->crisis_details) {
        $decoded = json_decode($report->crisis->crisis_details, true);
        if (is_array($decoded)) {
            $extra = $decoded;
        }
    }

    $statusKey = $report->report_status ?? 'pending';
    $crisisType = $report->crisis?->crisis_type;
    $subCat = $report->crisis?->sub_category;
    $typeLabel = $typeLabels[$crisisType] ?? '—';
    $subCatLabel =
        $subCat && isset($subCategoryMap[$crisisType][$subCat])
            ? $subCategoryMap[$crisisType][$subCat]
            : ($subCat
                ? ucwords(str_replace('_', ' ', $subCat))
                : null);

    $incidentAt =
        $report->crisis?->incident_at ??
        (isset($extra['incident_at']) ? \Carbon\Carbon::parse($extra['incident_at']) : null);

    $currentAdmin = auth('admin')->user();

    $evidenceCount = $report->supporting_evidence_path ? count((array) $report->supporting_evidence_path) : 0;
@endphp

@push('styles')
    <style>
        /* ===== Status banner ===== */
        .status-banner {
            border-radius: 12px;
            padding: 18px 22px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-left: 6px solid;
            gap: 16px;
            flex-wrap: wrap;
        }

        .status-banner.s-pending {
            background: #FFFBEB;
            border-color: #F59E0B;
        }

        .status-banner.s-verified {
            background: #ECFDF5;
            border-color: #10B981;
        }

        .status-banner.s-rejected {
            background: #FEF2F2;
            border-color: #EF4444;
        }

        .status-banner h3 {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
            color: #111827;
        }

        .status-banner .ref-code {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            background: #fff;
            padding: 2px 8px;
            border-radius: 4px;
            color: #374151;
            border: 1px solid #E5E7EB;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-pill.pending {
            background: #FEF3C7;
            color: #92400E;
        }

        .status-pill.verified {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-pill.rejected {
            background: #FEE2E2;
            color: #991B1B;
        }

        /* ===== Single info card ===== */
        .info-card {
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            margin-bottom: 16px;
            overflow: hidden;
        }

        .info-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid #E5E7EB;
            background: #F9FAFB;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-card-header h5 {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-card-header i.title-icon {
            color: #1E40AF;
        }

        /* Row layout (label / value) — FIXED: minmax(0,1fr) + min-width:0
           prevents grid columns from refusing to shrink and breaking text
           character-by-character. */
        .info-row {
            display: grid;
            grid-template-columns: 200px minmax(0, 1fr);
            padding: 14px 20px;
            border-bottom: 1px solid #F3F4F6;
            align-items: start;
            gap: 16px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row .label {
            font-size: 11px;
            font-weight: 700;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .label-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #EFF6FF;
            color: #1E40AF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .info-row .value {
            font-size: 14px;
            color: #111827;
            line-height: 1.55;
            word-break: break-word;
            overflow-wrap: anywhere;
            min-width: 0;
        }

        .info-row .value .sub {
            font-size: 12px;
            color: #6B7280;
            display: block;
            margin-top: 2px;
        }

        /* Stack label above value on tablets and smaller screens to prevent
           the 200px fixed label column from eating too much space. */
        @media (max-width: 991px) {
            .info-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }
        }

        /* Description row gets a boxed value, like pic 3 / pic 6 */
        .desc-box {
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13.5px;
            color: #111827;
            line-height: 1.6;
            white-space: pre-wrap;
        }

        /* Evidence files inline — now clickable */
        .evidence-inline {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .evidence-inline .file {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 12px;
            text-decoration: none;
            color: inherit;
            transition: all 0.15s;
        }

        .evidence-inline .file:hover {
            background: #EFF6FF;
            border-color: #93C5FD;
            color: inherit;
        }

        .evidence-inline .file i {
            color: #6B7280;
            font-size: 16px;
            flex-shrink: 0;
        }

        .evidence-inline .file:hover i.file-type-icon {
            color: #1E40AF;
        }

        .evidence-inline .file .file-name {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            color: #1E40AF;
            word-break: break-all;
            flex: 1;
            min-width: 0;
        }

        .evidence-inline .file .file-meta {
            font-size: 11px;
            color: #6B7280;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .integration-mini {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .integration-mini .item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            padding: 5px 10px;
            border-radius: 6px;
            background: #FEF3C7;
            color: #92400E;
        }

        .integration-mini .item i {
            font-size: 13px;
        }

        .map-link {
            font-size: 12px;
            color: #1E40AF;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .map-link:hover {
            text-decoration: underline;
        }

        /* ===== Right sidebar ===== */
        .profile-card {
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            margin-bottom: 16px;
            overflow: hidden;
        }

        .profile-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid #E5E7EB;
            background: #F9FAFB;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .profile-card-header h5 {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: #111827;
        }

        .profile-card-header i {
            color: #1E40AF;
        }

        .profile-body {
            padding: 20px;
            text-align: center;
        }

        .profile-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3B82F6, #1E40AF);
            color: #fff;
            font-size: 28px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }

        .profile-body .name {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
        }

        .profile-body .matric {
            font-size: 12px;
            color: #6B7280;
            margin-bottom: 12px;
        }

        .profile-meta {
            text-align: left;
            padding-top: 10px;
            border-top: 1px solid #F3F4F6;
        }

        .profile-meta div {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 12.5px;
            color: #374151;
            padding: 5px 0;
            word-break: break-word;
        }

        .profile-meta i {
            color: #6B7280;
            width: 14px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        /* Lecturers panel (side card showing who will be notified on verify) */
        .lect-card {
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            margin-bottom: 16px;
            overflow: hidden;
        }

        .lect-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid #E5E7EB;
            background: #F9FAFB;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .lect-card-header h5 {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: #111827;
        }

        .lect-card-header i {
            color: #1E40AF;
        }

        .lect-card-body {
            padding: 16px 20px;
        }

        .lect-card-body .blurb {
            font-size: 11.5px;
            color: #6B7280;
            margin: 0 0 12px;
            line-height: 1.5;
        }

        .lect-row {
            padding: 10px 0;
            border-bottom: 1px solid #F3F4F6;
        }

        .lect-row:last-child {
            border-bottom: none;
        }

        .lect-row .course {
            font-size: 11px;
            font-weight: 700;
            color: #1E40AF;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .lect-row .lname {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
            margin: 2px 0;
        }

        .lect-row .lemail {
            font-size: 11.5px;
            color: #6B7280;
            word-break: break-all;
        }

        .lect-row .nomatch {
            font-size: 11.5px;
            color: #92400E;
            font-style: italic;
        }

        .lect-empty {
            font-size: 12px;
            color: #6B7280;
            padding: 8px 0;
        }

        .lect-test-banner {
            background: #FFFBEB;
            border: 1px solid #FCD34D;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 11.5px;
            color: #92400E;
            margin-top: 12px;
            line-height: 1.45;
        }

        .lect-test-banner i {
            margin-right: 5px;
        }

        /* Decision card — the ONLY action panel for pending reports */
        .decision-card {
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .decision-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid #E5E7EB;
            background: #F9FAFB;
        }

        .decision-card-header h5 {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .decision-card-header i {
            color: #1E40AF;
        }

        .decision-card-body {
            padding: 18px 20px;
        }

        .decision-card-body label {
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
            display: block;
        }

        .decision-card-body .form-control {
            font-size: 13px;
        }

        .outcome-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .outcome-option {
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            padding: 14px 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.15s;
            background: #fff;
        }

        .outcome-option:hover {
            border-color: #9CA3AF;
        }

        .outcome-option.active.verify {
            border-color: #10B981;
            background: #ECFDF5;
            color: #065F46;
        }

        .outcome-option.active.reject {
            border-color: #EF4444;
            background: #FEF2F2;
            color: #991B1B;
        }

        .outcome-option i {
            font-size: 20px;
            display: block;
            margin-bottom: 4px;
            color: #9CA3AF;
        }

        .outcome-option.active.verify i {
            color: #10B981;
        }

        .outcome-option.active.reject i {
            color: #EF4444;
        }

        .outcome-option .label-text {
            font-size: 12px;
            font-weight: 600;
        }

        .outcome-option input[type="radio"] {
            display: none;
        }

        .help-text {
            font-size: 11px;
            color: #6B7280;
            margin-top: 4px;
        }

        .btn-decision {
            font-size: 13px;
            font-weight: 600;
            padding: 9px 14px;
            border-radius: 8px;
            width: 100%;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-decision:disabled {
            background: #9CA3AF;
            color: #fff;
            cursor: not-allowed;
        }

        .btn-verify-action {
            background: #10B981;
            color: #fff;
        }

        .btn-verify-action:hover {
            background: #059669;
            color: #fff;
        }

        .btn-reject-action {
            background: #EF4444;
            color: #fff;
        }

        .btn-reject-action:hover {
            background: #DC2626;
            color: #fff;
        }

        /* Decision banner — verified/rejected reports */
        .verdict-card {
            border-radius: 12px;
            padding: 16px 18px;
            margin-bottom: 16px;
            border: 1px solid;
        }

        .verdict-card.verified {
            background: #ECFDF5;
            border-color: #10B981;
        }

        .verdict-card.rejected {
            background: #FEF2F2;
            border-color: #EF4444;
        }

        .verdict-card h5 {
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 6px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .verdict-card.verified h5 {
            color: #065F46;
        }

        .verdict-card.rejected h5 {
            color: #991B1B;
        }

        .verdict-card p {
            margin: 0;
            font-size: 13px;
            line-height: 1.5;
        }

        .verdict-card.verified p {
            color: #047857;
        }

        .verdict-card.rejected p {
            color: #B91C1C;
        }

        .verdict-card strong {
            font-weight: 700;
        }

        .back-link {
            color: #1E40AF;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .btn-print {
            background: #374151;
            color: #fff;
        }

        .btn-print:hover {
            background: #1F2937;
            color: #fff;
        }

        @media print {

            .no-print,
            .back-link,
            .decision-card {
                display: none !important;
            }

            .info-card,
            .profile-card {
                border: 1px solid #E5E7EB !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-3">

        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <a href="{{ route('admin.crisis.index') }}" class="back-link">
                <i class="bi bi-arrow-left"></i> Back to Reports List
            </a>
            <button type="button" class="btn btn-print btn-sm" onclick="window.print()">
                <i class="bi bi-printer"></i> Print / Export PDF
            </button>
        </div>

        {{-- Status banner --}}
        <div class="status-banner s-{{ $statusKey }}">
            <div>
                <h3 class="mb-2">
                    {{ $typeLabel }}
                    @if ($subCatLabel)
                        <span style="font-size:14px; color:#6B7280; font-weight:500;"> — {{ $subCatLabel }}</span>
                    @endif
                </h3>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="ref-code">CR-{{ str_pad($report->report_id, 6, '0', STR_PAD_LEFT) }}</span>
                    <small class="text-muted">Submitted {{ $report->date_reported?->diffForHumans() }}</small>
                </div>
            </div>
            <span class="status-pill {{ $statusKey }}">
                @if ($statusKey === 'pending')
                    <i class="bi bi-clock"></i>
                @endif
                @if ($statusKey === 'verified')
                    <i class="bi bi-check-circle-fill"></i>
                @endif
                @if ($statusKey === 'rejected')
                    <i class="bi bi-x-circle-fill"></i>
                @endif
                {{ strtoupper($statusKey) }}
            </span>
        </div>

        <div class="row g-3">
            {{-- =================== LEFT: ONE big card =================== --}}
            <div class="col-lg-8">
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="bi bi-info-circle-fill title-icon"></i> Crisis Information</h5>
                    </div>

                    <div class="info-row">
                        <div class="label">
                            <span class="label-icon"><i class="bi bi-person-fill"></i></span>
                            Student
                        </div>
                        <div class="value">
                            <strong>{{ $report->student?->full_name ?? '—' }}</strong>
                            <span class="text-muted">({{ $report->student_id }})</span>
                            @if ($report->student?->faculty)
                                <span class="sub">{{ $report->student->faculty }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="label">
                            <span class="label-icon"><i class="bi bi-pencil-square"></i></span>
                            Reported By
                        </div>
                        <div class="value">
                            {{ $report->student?->full_name ?? '—' }}
                            <span class="sub">Student self-report •
                                {{ $report->date_reported?->format('d M Y, H:i') }}</span>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="label">
                            <span class="label-icon"><i class="bi bi-tag-fill"></i></span>
                            Crisis Type
                        </div>
                        <div class="value">{{ $typeLabel }}</div>
                    </div>

                    @if ($subCatLabel)
                        <div class="info-row">
                            <div class="label">
                                <span class="label-icon"><i class="bi bi-bookmark-fill"></i></span>
                                Sub-Category
                            </div>
                            <div class="value">{{ $subCatLabel }}</div>
                        </div>
                    @endif

                    @if ($incidentAt)
                        <div class="info-row">
                            <div class="label">
                                <span class="label-icon"><i class="bi bi-calendar-event-fill"></i></span>
                                Date &amp; Time
                            </div>
                            <div class="value">{{ $incidentAt->format('d F Y, H:i') }}</div>
                        </div>
                    @endif

                    @if ($report->crisis?->location)
                        <div class="info-row">
                            <div class="label">
                                <span class="label-icon"><i class="bi bi-geo-alt-fill"></i></span>
                                Location
                            </div>
                            <div class="value">
                                {{ $report->crisis->location }}
                                @if (($extra['latitude'] ?? null) && ($extra['longitude'] ?? null))
                                    <div style="margin-top:4px;">
                                        <a href="https://maps.google.com/?q={{ $extra['latitude'] }},{{ $extra['longitude'] }}"
                                            target="_blank" rel="noopener" class="map-link">
                                            <i class="bi bi-pin-map"></i> View on Google Maps
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Description in a boxed value, inline as a row --}}
                    <div class="info-row">
                        <div class="label">
                            <span class="label-icon"><i class="bi bi-file-text-fill"></i></span>
                            Description
                        </div>
                        <div class="value">
                            <div class="desc-box">
                                {{ $report->crisis?->crisis_description ?? ($report->report_description ?? 'No description provided.') }}
                            </div>
                        </div>
                    </div>

                    {{-- Immediate Actions — inline row --}}
                    <div class="info-row">
                        <div class="label">
                            <span class="label-icon"><i class="bi bi-shield-check"></i></span>
                            Immediate Actions
                        </div>
                        <div class="value">
                            @if (!empty($extra['immediate_actions']))
                                {{ $extra['immediate_actions'] }}
                            @else
                                <em class="text-muted">None specified</em>
                            @endif
                        </div>
                    </div>

                    {{-- Supporting Documents — clickable files --}}
                    <div class="info-row">
                        <div class="label">
                            <span class="label-icon"><i class="bi bi-paperclip"></i></span>
                            Supporting Documents
                        </div>
                        <div class="value">
                            @if ($evidenceCount > 0)
                                <div class="evidence-inline">
                                    @foreach ((array) $report->supporting_evidence_path as $i => $p)
                                        @php
                                            $name = basename($p);
                                            $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                                            $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                            $isPdf = $ext === 'pdf';
                                            $icon  = $isImg ? 'bi-file-earmark-image'
                                                  : ($isPdf ? 'bi-file-earmark-pdf'
                                                  : 'bi-file-earmark-text');
                                        @endphp
                                        <a href="{{ route('admin.crisis.evidence.download', [$report->report_id, $i]) }}"
                                           target="_blank" rel="noopener" class="file">
                                            <i class="bi {{ $icon }} file-type-icon"></i>
                                            <span class="file-name">{{ $name }}</span>
                                            <span class="file-meta">
                                                <i class="bi bi-box-arrow-up-right"></i> Open
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <em class="text-muted">No files uploaded</em>
                            @endif
                        </div>
                    </div>

                    {{-- System Integration — inline row --}}
                    <div class="info-row">
                        <div class="label">
                            <span class="label-icon"><i class="bi bi-database-fill"></i></span>
                            System Integration
                        </div>
                        <div class="value">
                            <div class="integration-mini">
                                <span class="item"><i class="bi bi-clock"></i> iTa'leem: Fetching...</span>
                                <span class="item"><i class="bi bi-clock"></i> Sejahtera Clinic: Pending</span>
                            </div>
                            <div class="help-text" style="margin-top:6px;">
                                <i class="bi bi-info-circle"></i> Manual verification mode — API integration coming in
                                future phases.
                            </div>
                        </div>
                    </div>

                    @if ($report->admin_remarks && $statusKey !== 'pending')
                        <div class="info-row">
                            <div class="label">
                                <span class="label-icon"><i class="bi bi-chat-left-text-fill"></i></span>
                                Reviewer's Notes
                            </div>
                            <div class="value">
                                <div class="desc-box">{{ $report->admin_remarks }}</div>
                            </div>
                        </div>
                    @endif

                    @if ($report->blockchain_hash)
                        <div class="info-row">
                            <div class="label">
                                <span class="label-icon"><i class="bi bi-link-45deg"></i></span>
                                Blockchain
                            </div>
                            <div class="value"><x-blockchain-badge :hash="$report->blockchain_hash" /></div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- =================== RIGHT SIDEBAR =================== --}}
            <div class="col-lg-4">

                {{-- Decision banner (only when verified/rejected) --}}
                @if ($statusKey === 'verified')
                    <div class="verdict-card verified">
                        <h5><i class="bi bi-check-circle-fill"></i> Report Verified</h5>
                        <p>
                            Reviewed by <strong>{{ $report->verifier?->admin_name ?? 'System Administrator' }}</strong>
                            @if ($report->verifier?->admin_id)
                                <br><small>Staff ID:
                                    STAFF{{ str_pad($report->verifier->admin_id, 3, '0', STR_PAD_LEFT) }}</small>
                            @endif
                            @if ($report->verified_at)
                                <br><small>{{ $report->verified_at->format('d M Y, h:i A') }}</small>
                            @endif
                        </p>
                    </div>
                @elseif($statusKey === 'rejected')
                    <div class="verdict-card rejected">
                        <h5><i class="bi bi-x-circle-fill"></i> Report Rejected</h5>
                        <p>
                            Reviewed by <strong>{{ $report->verifier?->admin_name ?? 'System Administrator' }}</strong>
                            @if ($report->verifier?->admin_id)
                                <br><small>Staff ID:
                                    STAFF{{ str_pad($report->verifier->admin_id, 3, '0', STR_PAD_LEFT) }}</small>
                            @endif
                            @if ($report->verified_at)
                                <br><small>{{ $report->verified_at->format('d M Y, h:i A') }}</small>
                            @endif
                        </p>
                    </div>
                @endif

                {{-- Student profile --}}
                <div class="profile-card">
                    <div class="profile-card-header">
                        <i class="bi bi-person-circle"></i>
                        <h5>Student Profile</h5>
                    </div>
                    <div class="profile-body">
                        <div class="profile-avatar">
                            {{ strtoupper(substr($report->student?->full_name ?? '?', 0, 1)) }}
                        </div>
                        <div class="name">{{ $report->student?->full_name ?? '—' }}</div>
                        <div class="matric">{{ $report->student_id }}</div>

                        <div class="profile-meta">
                            @if ($report->student?->email)
                                <div><i class="bi bi-envelope"></i> <span>{{ $report->student->email }}</span></div>
                            @endif
                            @if ($report->student?->phone)
                                <div><i class="bi bi-telephone"></i> <span>{{ $report->student->phone }}</span></div>
                            @endif
                            @if ($report->student?->programme)
                                <div><i class="bi bi-mortarboard"></i> <span>{{ $report->student->programme }}</span>
                                </div>
                            @endif
                            @if ($report->student?->status)
                                <div><i class="bi bi-circle-fill"
                                        style="font-size:7px; color:#10B981; margin-top:5px;"></i>
                                    <span>{{ ucfirst($report->student->status) }} student</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Lecturers who will be notified on verify --}}
                <div class="lect-card">
                    <div class="lect-card-header">
                        <i class="bi bi-mortarboard-fill"></i>
                        <h5>Student's Lecturers</h5>
                    </div>
                    <div class="lect-card-body">
                        @if(($studentCourses ?? collect())->isEmpty())
                            <div class="lect-empty">
                                <i class="bi bi-info-circle"></i>
                                No courses on file for this student yet. The student must
                                log in to e-Tawassul at least once to auto-sync their timetable.
                            </div>
                        @else
                            <p class="blurb">
                                These {{ count($studentCourses) }} lecturer(s) will be notified by
                                email when this report is verified.
                            </p>

                            @foreach($studentCourses as $row)
                                <div class="lect-row">
                                    <div class="course">{{ $row->course_code }}{{ $row->course_name ? ' — ' . \Illuminate\Support\Str::limit($row->course_name, 40) : '' }}</div>
                                    @if($row->lecturer_id)
                                        <div class="lname">
                                            {{ trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '')) }}
                                        </div>
                                        <div class="lemail">
                                            <i class="bi bi-envelope"></i> {{ $row->email }}
                                        </div>
                                    @else
                                        <div class="lname">{{ $row->lecturer_name_raw ?: 'Unknown lecturer' }}</div>
                                        <div class="nomatch">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            Not in directory — will not be notified
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            @if(env('TESTING_MODE_REDIRECT_LECTURER_EMAILS', false))
                                <div class="lect-test-banner">
                                    <i class="bi bi-cone-striped"></i>
                                    <strong>Test mode:</strong> all lecturer emails are redirected to
                                    <code>{{ env('TESTING_MODE_LECTURER_REDIRECT_EMAIL') }}</code>
                                    until the safety flag is turned off.
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- DECISION (only when pending) — ONE compact card --}}
                @if ($statusKey === 'pending')
                    <div class="decision-card no-print">
                        <div class="decision-card-header">
                            <h5><i class="bi bi-shield-check"></i> Verify Report</h5>
                        </div>
                        <div class="decision-card-body">
                            <form id="verificationForm" method="POST" action="">
                                @csrf

                                <div class="mb-3">
                                    <label>Staff ID</label>
                                    <input type="text" name="staff_id" class="form-control"
                                        value="{{ $currentAdmin?->admin_id ? 'STAFF' . str_pad($currentAdmin->admin_id, 3, '0', STR_PAD_LEFT) : '' }}"
                                        placeholder="e.g. STAFF001" required>
                                    @if ($currentAdmin)
                                        <div class="help-text">Logged in as
                                            <strong>{{ $currentAdmin->admin_name }}</strong></div>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label>Decision <span style="color:#EF4444;">*</span></label>
                                    <div class="outcome-grid">
                                        <label class="outcome-option" id="opt-verify" onclick="setOutcome('verify')">
                                            <input type="radio" name="outcome" value="verify" required>
                                            <i class="bi bi-check-circle"></i>
                                            <div class="label-text">Verify</div>
                                        </label>
                                        <label class="outcome-option" id="opt-reject" onclick="setOutcome('reject')">
                                            <input type="radio" name="outcome" value="reject" required>
                                            <i class="bi bi-x-circle"></i>
                                            <div class="label-text">Reject</div>
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label id="notesLabel">Notes (Optional)</label>
                                    <textarea name="admin_remarks" id="admin_remarks" rows="3" class="form-control" maxlength="2000"
                                        placeholder="Add any notes..."></textarea>
                                    <div class="help-text" id="notesHelp">Optional notes for the audit trail.</div>
                                </div>

                                <button type="submit" id="submitBtn" class="btn-decision" disabled
                                    onclick="return confirmSubmit(event);">
                                    <i class="bi bi-arrow-right-circle"></i> Choose a decision first
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Donation Control Panel — appears only when report is verified --}}
                @include('admin.crisis._donation_control')
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const verifyUrl = "{{ route('admin.crisis.verify', $report->report_id) }}";
            const rejectUrl = "{{ route('admin.crisis.reject', $report->report_id) }}";

            function setOutcome(choice) {
                const form = document.getElementById('verificationForm');
                const optV = document.getElementById('opt-verify');
                const optR = document.getElementById('opt-reject');
                const btn = document.getElementById('submitBtn');
                const lbl = document.getElementById('notesLabel');
                const help = document.getElementById('notesHelp');
                const txt = document.getElementById('admin_remarks');

                optV.classList.remove('active', 'verify', 'reject');
                optR.classList.remove('active', 'verify', 'reject');

                if (choice === 'verify') {
                    optV.classList.add('active', 'verify');
                    form.action = verifyUrl;
                    btn.classList.remove('btn-reject-action');
                    btn.classList.add('btn-verify-action');
                    btn.innerHTML = '<i class="bi bi-check2-circle"></i> Verify &amp; Publish';
                    lbl.innerHTML = 'Notes (Optional)';
                    help.textContent = 'Optional notes for the audit trail.';
                    txt.required = false;
                    txt.removeAttribute('minlength');
                } else {
                    optR.classList.add('active', 'reject');
                    form.action = rejectUrl;
                    btn.classList.remove('btn-verify-action');
                    btn.classList.add('btn-reject-action');
                    btn.innerHTML = '<i class="bi bi-x-circle"></i> Reject Report';
                    lbl.innerHTML = 'Reason for Rejection <span style="color:#EF4444;">*</span>';
                    help.textContent = 'Required (min 10 characters). Sent to the student by email.';
                    txt.required = true;
                    txt.minLength = 10;
                }
                btn.disabled = false;
            }

            function confirmSubmit(e) {
                const outcome = document.querySelector('input[name="outcome"]:checked')?.value;
                if (!outcome) {
                    e.preventDefault();
                    return false;
                }
                const msg = outcome === 'verify' ?
                    'Verify this report? This will be recorded on the blockchain and cannot be undone.' :
                    'Reject this report? The student will be notified by email.';
                if (!confirm(msg)) {
                    e.preventDefault();
                    return false;
                }
                return true;
            }
        </script>
    @endpush
@endsection
