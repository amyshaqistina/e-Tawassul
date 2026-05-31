@extends('layouts.admin')
@section('title', 'Crisis Reports')

@php
    $typeLabels = [
        'medical'          => 'Medical Emergency',
        'illness'          => 'Medical Emergency',
        'accident'         => 'Accident',
        'natural_disaster' => 'Natural Disaster',
        'death'            => 'Death / Bereavement',
        'family_emergency' => 'Family Emergency',
    ];

    $subCategoryMap = [
        'medical' => [
            'sudden_illness'   => 'Sudden Serious Illness',
            'mental_health'    => 'Mental Health Crisis',
            'hospitalization'  => 'Hospitalization',
            'surgery_required' => 'Surgery Required',
            'chronic_flare'    => 'Chronic Condition Flare-up',
            'family_critical'  => 'Family Member Critical Illness',
        ],
        'accident' => [
            'road_accident'   => 'Road Accident',
            'lab_workshop'    => 'Lab / Workshop Accident',
            'sports_injury'   => 'Sports Injury',
            'fall_fracture'   => 'Fall / Fracture',
            'burn_electrical' => 'Burn / Electrical Injury',
            'house_fire'      => 'House Fire',
            'drowning'        => 'Drowning / Near-drowning',
        ],
        'natural_disaster' => [
            'flood'       => 'Flood',
            'landslide'   => 'Landslide',
            'fire'        => 'Forest / Building Fire',
            'storm'       => 'Storm / Heavy Rain',
            'haze'        => 'Haze',
            'earthquake'  => 'Earthquake',
            'strong_wind' => 'Strong Wind',
        ],
        'death' => [
            'parent'         => 'Parent',
            'sibling'        => 'Sibling',
            'grandparent'    => 'Grandparent',
            'close_relative' => 'Close Relative',
            'guardian'       => 'Guardian',
            'spouse'         => 'Spouse',
            'close_friend'   => 'Close Friend / Coursemate / Roommate',
        ],
    ];
    $subCategoryMap['illness'] = $subCategoryMap['medical'];
@endphp

@push('styles')
<style>
    /* ===== Status pills ===== */
    .status-pill {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 11px; font-weight: 700; letter-spacing: 0.3px;
        padding: 4px 10px; border-radius: 12px; text-transform: uppercase;
        white-space: nowrap;
    }
    .status-pending  { background:#FEF3C7; color:#92400E; border:1px solid #FCD34D; }
    .status-verified { background:#D1FAE5; color:#065F46; border:1px solid #6EE7B7; }
    .status-rejected { background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; }

    /* ===== Filter bar — single horizontal row ===== */
    .filter-bar {
        background:#fff; border:1px solid #E5E7EB; border-radius:12px;
        padding:12px 14px; margin-bottom:14px;
    }
    .filter-grid {
        display:flex; flex-wrap:nowrap; gap:8px; align-items:center;
    }
    .filter-grid > * { flex-shrink:0; }
    .filter-grid .form-control,
    .filter-grid .form-select { font-size:13px; height:36px; }
    .filter-grid .search-wrap { flex:1 1 220px; min-width:200px; position:relative; }
    .filter-grid .search-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#9CA3AF; font-size:13px; }
    .filter-grid .search-wrap input { padding-left:34px; }
    .filter-grid .total-chip {
        background:#EFF6FF; color:#1E40AF; border:1px solid #BFDBFE;
        padding:0 14px; height:36px; border-radius:8px;
        display:flex; align-items:center; gap:6px; font-size:13px; font-weight:600;
        white-space:nowrap;
    }
    .filter-grid select.with-icon {
        background-position: 8px center, right 0.75rem center;
        background-repeat: no-repeat, no-repeat;
        padding-left: 30px;
    }
    .filter-grid select.crisis-type-sel  { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%239ca3af' viewBox='0 0 16 16' width='14' height='14'%3E%3Cpath d='M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .39.812l-4.89 6.226V13.5a.5.5 0 0 1-.812.39L7.69 13a.5.5 0 0 1-.19-.39V8.038L2.61 1.812A.5.5 0 0 1 1.5 1.5z'/%3E%3C/svg%3E"), url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23343a40' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E"); }
    .filter-grid select.sub-cat-sel       { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%239ca3af' viewBox='0 0 16 16' width='14' height='14'%3E%3Cpath d='M2 2.5A2.5 2.5 0 0 1 4.5 0h6.879a2.5 2.5 0 0 1 1.767.732l3.122 3.121A2.5 2.5 0 0 1 17 5.621V13.5A2.5 2.5 0 0 1 14.5 16h-10A2.5 2.5 0 0 1 2 13.5v-11z'/%3E%3C/svg%3E"), url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23343a40' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E"); }
    .filter-grid select.date-range-sel    { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%239ca3af' viewBox='0 0 16 16' width='14' height='14'%3E%3Cpath d='M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5z'/%3E%3C/svg%3E"), url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23343a40' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E"); }

    /* ===== Summary chips ===== */
    .summary-chips { display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; padding-top:10px;
                     border-top:1px solid #F3F4F6; }
    .summary-chip {
        background:#F3F4F6; border:1px solid #E5E7EB; border-radius:20px;
        padding:5px 14px; font-size:12px; color:#374151;
    }
    .summary-chip:hover { background:#E5E7EB; }
    .summary-chip strong { color:#111827; margin-left:6px; font-weight:700; }
    .summary-chip.active { background:#1E40AF; color:#fff; border-color:#1E40AF; }
    .summary-chip.active strong { color:#fff; }

    /* ===== Table ===== */
    .content-card { background:#fff; border:1px solid #E5E7EB; border-radius:12px; padding:16px; }
    table.crisis-table { margin-bottom:0; }
    table.crisis-table thead th {
        font-size:12px; font-weight:700; color:#6B7280; text-transform:uppercase;
        letter-spacing:0.3px; padding:10px 12px; border-bottom:2px solid #E5E7EB;
        white-space:nowrap;
    }
    table.crisis-table tbody td { padding:12px; vertical-align:middle; }
    table.crisis-table tbody tr { border-left:3px solid transparent; transition:background 0.15s; }
    table.crisis-table tbody tr.row-pending  { border-left-color:#F59E0B; background:#FFFBEB; }
    table.crisis-table tbody tr.row-verified { border-left-color:#10B981; }
    table.crisis-table tbody tr.row-rejected { border-left-color:#EF4444; }
    table.crisis-table tbody tr:hover { background:#F9FAFB; }
    table.crisis-table tbody tr.row-pending:hover { background:#FEF9E1; }

    .report-code {
        font-family:'Courier New',monospace; font-size:11px; color:#1E40AF;
        background:#EFF6FF; padding:3px 8px; border-radius:4px; white-space:nowrap;
    }

    .nav-tabs .nav-link.active { font-weight:700; }
    .nav-tabs .nav-link .badge { margin-left:6px; }

    .btn-print { background:#374151; color:#fff; }
    .btn-print:hover { background:#1F2937; color:#fff; }

    .btn-review {
        background:#1E40AF; color:#fff; font-size:12px; font-weight:600;
        padding:6px 12px; border-radius:6px; border:none;
        display:inline-flex; align-items:center; gap:5px; white-space:nowrap;
        text-decoration:none;
    }
    .btn-review:hover { background:#1E3A8A; color:#fff; }

    @media (max-width: 900px) {
        .filter-grid { flex-wrap:wrap; }
    }

    @media print {
        .no-print, .nav-tabs, .filter-bar, .pagination, .btn { display:none !important; }
        .content-card { box-shadow:none !important; border:none !important; }
        table.crisis-table tbody tr { background:#fff !important; border-left:none !important; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- Page header --}}
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <h4 class="mb-0">Crisis Reports</h4>
            <small class="text-muted">Review and verify submitted student crisis reports</small>
        </div>
        <button type="button" class="btn btn-print btn-sm" onclick="window.print()">
            <i class="bi bi-printer"></i> Print / Export PDF
        </button>
    </div>

    {{-- Filter Bar — SINGLE horizontal row --}}
    <form method="GET" action="{{ route('admin.crisis.index') }}" id="filterForm" class="no-print">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="filter-bar">
            <div class="filter-grid">

                <div class="search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search by student ID or report #..."
                           value="{{ request('search') }}" oninput="debounceSubmit()">
                </div>

                <select name="crisis_type" class="form-select with-icon crisis-type-sel" style="width:175px;" onchange="this.form.submit()">
                    <option value="">All Crisis Types</option>
                    @foreach($typeLabels as $value => $label)
                        @if($value !== 'illness')
                            <option value="{{ $value }}" {{ request('crisis_type')===$value?'selected':'' }}>{{ $label }}</option>
                        @endif
                    @endforeach
                </select>

                <select name="sub_category" class="form-select with-icon sub-cat-sel" style="width:200px;" onchange="this.form.submit()">
                    <option value="">All Sub-Categories</option>
                    @php $currentType = request('crisis_type'); @endphp
                    @if($currentType && isset($subCategoryMap[$currentType]))
                        @foreach($subCategoryMap[$currentType] as $value => $label)
                            <option value="{{ $value }}" {{ request('sub_category')===$value?'selected':'' }}>{{ $label }}</option>
                        @endforeach
                    @endif
                </select>

                <select name="date_range" class="form-select with-icon date-range-sel" style="width:150px;" onchange="toggleCustomDate()">
                    <option value="">All Time</option>
                    <option value="today"     {{ request('date_range')==='today'?'selected':'' }}>Today</option>
                    <option value="week"      {{ request('date_range')==='week'?'selected':'' }}>This Week</option>
                    <option value="last_week" {{ request('date_range')==='last_week'?'selected':'' }}>Last Week</option>
                    <option value="month"     {{ request('date_range')==='month'?'selected':'' }}>This Month</option>
                    <option value="custom"    {{ request('date_range')==='custom'?'selected':'' }}>Custom...</option>
                </select>

                <div id="customDateWrap" class="d-flex gap-2" style="{{ request('date_range')==='custom' ? '' : 'display:none !important;' }}">
                    <input type="date" name="date_from" class="form-control" style="width:140px;" value="{{ request('date_from') }}">
                    <input type="date" name="date_to"   class="form-control" style="width:140px;" value="{{ request('date_to') }}">
                </div>

                <div class="total-chip ms-auto">
                    Total: <strong>{{ $pending->total() + $verified->total() + $rejected->total() }}</strong>
                </div>

                @if(request()->anyFilled(['search','crisis_type','sub_category','date_range','date_from','date_to']))
                    <a href="{{ route('admin.crisis.index', ['tab'=>$tab]) }}" class="btn btn-sm btn-outline-secondary" style="height:36px;">
                        <i class="bi bi-x-lg"></i> Clear
                    </a>
                @endif
            </div>

            @if(isset($categoryTotals) && count($categoryTotals))
            <div class="summary-chips">
                <a href="{{ route('admin.crisis.index', array_merge(request()->except('crisis_type','sub_category','page'))) }}" class="text-decoration-none">
                    <span class="summary-chip {{ !request('crisis_type') ? 'active' : '' }}">
                        All Categories <strong>{{ array_sum($categoryTotals) }}</strong>
                    </span>
                </a>
                @foreach($categoryTotals as $type => $count)
                    <a href="{{ route('admin.crisis.index', array_merge(request()->except('sub_category','page'), ['crisis_type'=>$type])) }}" class="text-decoration-none">
                        <span class="summary-chip {{ request('crisis_type')===$type ? 'active' : '' }}">
                            {{ $typeLabels[$type] ?? ucwords(str_replace('_',' ',$type)) }} <strong>{{ $count }}</strong>
                        </span>
                    </a>
                @endforeach
            </div>
            @endif
        </div>
    </form>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-3 no-print">
        <li class="nav-item">
            <a class="nav-link {{ $tab==='pending'?'active':'' }}" href="{{ route('admin.crisis.index', array_merge(request()->except('tab','page'), ['tab'=>'pending'])) }}">
                <i class="bi bi-clock-history text-warning"></i>
                Pending <span class="badge bg-warning text-dark">{{ $pending->total() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab==='verified'?'active':'' }}" href="{{ route('admin.crisis.index', array_merge(request()->except('tab','page'), ['tab'=>'verified'])) }}">
                <i class="bi bi-check-circle text-success"></i>
                Verified <span class="badge bg-success">{{ $verified->total() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab==='rejected'?'active':'' }}" href="{{ route('admin.crisis.index', array_merge(request()->except('tab','page'), ['tab'=>'rejected'])) }}">
                <i class="bi bi-x-circle text-danger"></i>
                Rejected <span class="badge bg-danger">{{ $rejected->total() }}</span>
            </a>
        </li>
    </ul>

    <div class="content-card">
        @php $collection = $$tab; @endphp

        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong class="text-muted small">
                {{ $collection->total() }} report{{ $collection->total()!==1?'s':'' }} found
            </strong>
            @if($collection->hasPages())
                <small class="text-muted">Page {{ $collection->currentPage() }} of {{ $collection->lastPage() }}</small>
            @endif
        </div>

        @if($collection->isEmpty())
            <div class="text-center my-5 py-4">
                <i class="bi bi-inbox" style="font-size:48px;color:#D1D5DB;"></i>
                <p class="text-muted mt-2 mb-0">No reports match your filter criteria.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle crisis-table">
                    <thead>
                        <tr>
                            <th style="width:120px;">Report ID</th>
                            <th>Student</th>
                            <th>Type</th>
                            <th>Sub-Category</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th class="text-end no-print" style="width:100px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($collection as $r)
                            @php
                                $statusKey  = $r->report_status ?? 'pending';
                                $crisisType = $r->crisis?->crisis_type;
                                $subCat     = $r->crisis?->sub_category;
                            @endphp
                            <tr class="row-{{ $statusKey }}">
                                <td><span class="report-code">CR-{{ str_pad($r->report_id, 6, '0', STR_PAD_LEFT) }}</span></td>
                                <td>
                                    <div class="fw-semibold">{{ $r->student?->full_name ?? '—' }}</div>
                                    <small class="text-muted">{{ $r->student_id }}</small>
                                </td>
                                <td>{{ $typeLabels[$crisisType] ?? '—' }}</td>
                                <td>
                                    @if($subCat && $crisisType && isset($subCategoryMap[$crisisType][$subCat]))
                                        <small>{{ $subCategoryMap[$crisisType][$subCat] }}</small>
                                    @elseif($subCat)
                                        <small class="text-muted">{{ ucwords(str_replace('_',' ',$subCat)) }}</small>
                                    @else
                                        <small class="text-muted">—</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-pill status-{{ $statusKey }}">
                                        @if($statusKey==='pending')<i class="bi bi-clock"></i>@endif
                                        @if($statusKey==='verified')<i class="bi bi-check-circle-fill"></i>@endif
                                        @if($statusKey==='rejected')<i class="bi bi-x-circle-fill"></i>@endif
                                        {{ strtoupper($statusKey) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $r->date_reported?->format('d M Y') }}
                                    <br><small class="text-muted">{{ $r->date_reported?->format('h:i A') }}</small>
                                </td>
                                <td class="text-end no-print">
                                    <a href="{{ route('admin.crisis.show', $r->report_id) }}" class="btn-review">
                                        <i class="bi bi-eye"></i> Review
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3 no-print">
                {{ $collection->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    let _t;
    function debounceSubmit() {
        clearTimeout(_t);
        _t = setTimeout(() => document.getElementById('filterForm').submit(), 450);
    }
    function toggleCustomDate() {
        const sel = document.querySelector('select[name="date_range"]');
        const wrap = document.getElementById('customDateWrap');
        if (sel.value === 'custom') {
            wrap.style.display = 'flex';
        } else {
            wrap.style.display = 'none';
            document.getElementById('filterForm').submit();
        }
    }
</script>
@endpush
@endsection
