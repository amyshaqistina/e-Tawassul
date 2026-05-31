@extends('layouts.admin')
@section('title', 'Last Digital Messages')

@php
    $mediaLabels = [
        'text'  => 'Text',
        'audio' => 'Audio',
        'video' => 'Video',
        'image' => 'Image',
    ];
@endphp

@push('styles')
<style>
    .status-pill { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:700;
                   letter-spacing:0.3px; padding:4px 10px; border-radius:12px; text-transform:uppercase;
                   white-space:nowrap; }
    .status-pending  { background:#FEF3C7; color:#92400E; border:1px solid #FCD34D; }
    .status-released { background:#D1FAE5; color:#065F46; border:1px solid #6EE7B7; }

    .filter-bar { background:#fff; border:1px solid #E5E7EB; border-radius:12px;
                  padding:12px 14px; margin-bottom:14px; }
    .filter-grid { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
    .filter-grid > * { flex-shrink:0; }
    .filter-grid .form-control,
    .filter-grid .form-select { font-size:13px; height:36px; }
    .filter-grid .search-wrap { flex:1 1 240px; min-width:200px; max-width:340px; position:relative; }
    .filter-grid .search-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#9CA3AF; font-size:13px; }
    .filter-grid .search-wrap input { padding-left:34px; }
    .filter-grid .total-chip { background:#EFF6FF; color:#1E40AF; border:1px solid #BFDBFE;
                               padding:0 14px; height:36px; border-radius:8px;
                               display:flex; align-items:center; gap:6px; font-size:13px; font-weight:600;
                               white-space:nowrap; margin-left:auto; }

    .summary-chips { display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; padding-top:10px;
                     border-top:1px solid #F3F4F6; }
    .summary-chip { background:#F3F4F6; border:1px solid #E5E7EB; border-radius:20px;
                    padding:5px 14px; font-size:12px; color:#374151; }
    .summary-chip:hover { background:#E5E7EB; }
    .summary-chip strong { color:#111827; margin-left:6px; font-weight:700; }
    .summary-chip.active { background:#1E40AF; color:#fff; border-color:#1E40AF; }
    .summary-chip.active strong { color:#fff; }

    .content-card { background:#fff; border:1px solid #E5E7EB; border-radius:12px; padding:16px; }
    table.ldms-table { margin-bottom:0; }
    table.ldms-table thead th { font-size:12px; font-weight:700; color:#6B7280; text-transform:uppercase;
                                letter-spacing:0.3px; padding:10px 12px; border-bottom:2px solid #E5E7EB;
                                white-space:nowrap; }
    table.ldms-table tbody td { padding:12px; vertical-align:middle; }
    table.ldms-table tbody tr { border-left:3px solid transparent; transition:background 0.15s; }
    table.ldms-table tbody tr.row-pending  { border-left-color:#F59E0B; background:#FFFBEB; }
    table.ldms-table tbody tr.row-released { border-left-color:#10B981; }
    table.ldms-table tbody tr:hover { background:#F9FAFB; }
    table.ldms-table tbody tr.row-pending:hover { background:#FEF9E1; }

    .ref-code { font-family:'Courier New',monospace; font-size:11px; color:#1E40AF;
                background:#EFF6FF; padding:3px 8px; border-radius:4px; white-space:nowrap; }

    .media-badge { font-size:10px; font-weight:700; padding:3px 8px; border-radius:4px; text-transform:uppercase;
                   letter-spacing:0.4px; }
    .media-text  { background:#E5E7EB; color:#374151; }
    .media-audio { background:#DBEAFE; color:#1E40AF; }
    .media-video { background:#FECACA; color:#991B1B; }
    .media-image { background:#FED7AA; color:#9A3412; }

    .student-status-badge { font-size:10px; font-weight:600; padding:2px 8px; border-radius:10px;
                            text-transform:uppercase; letter-spacing:0.4px; }
    .student-active   { background:#D1FAE5; color:#065F46; }
    .student-deceased { background:#374151; color:#fff; }

    .nav-tabs .nav-link.active { font-weight:700; }
    .nav-tabs .nav-link .badge { margin-left:6px; }
    .btn-print { background:#374151; color:#fff; }
    .btn-print:hover { background:#1F2937; color:#fff; }
    .btn-review { background:#1E40AF; color:#fff; font-size:12px; font-weight:600;
                  padding:6px 12px; border-radius:6px; border:none;
                  display:inline-flex; align-items:center; gap:5px; white-space:nowrap;
                  text-decoration:none; }
    .btn-review:hover { background:#1E3A8A; color:#fff; }

    @media (max-width: 900px) { .filter-grid { flex-wrap:wrap; } }
    @media print {
        .no-print, .nav-tabs, .filter-bar, .pagination, .btn { display:none !important; }
        .content-card { box-shadow:none !important; border:none !important; }
        table.ldms-table tbody tr { background:#fff !important; border-left:none !important; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <h4 class="mb-0">Last Digital Messages</h4>
            <small class="text-muted">Pre-written final messages awaiting release after death verification</small>
        </div>
        <button type="button" class="btn btn-print btn-sm" onclick="window.print()">
            <i class="bi bi-printer"></i> Print / Export PDF
        </button>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('admin.ldms.index') }}" id="filterForm" class="no-print">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="filter-bar">
            <div class="filter-grid">
                <div class="search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search by student ID or LDMS #..."
                           value="{{ request('search') }}" oninput="debounceSubmit()">
                </div>

                <select name="media_type" class="form-select" style="width:160px;" onchange="this.form.submit()">
                    <option value="">All Media Types</option>
                    @foreach($mediaLabels as $val => $label)
                        <option value="{{ $val }}" {{ request('media_type')===$val?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="student_status" class="form-select" style="width:170px;" onchange="this.form.submit()">
                    <option value="">Any Student Status</option>
                    <option value="active"   {{ request('student_status')==='active'?'selected':'' }}>Active Students</option>
                    <option value="deceased" {{ request('student_status')==='deceased'?'selected':'' }}>Deceased (ready to release)</option>
                </select>

                <select name="date_range" class="form-select" style="width:150px;" onchange="toggleCustomDate()">
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

                <div class="total-chip">
                    Total: <strong>{{ $pending->total() + $released->total() }}</strong>
                </div>

                @if(request()->anyFilled(['search','media_type','student_status','date_range','date_from','date_to']))
                    <a href="{{ route('admin.ldms.index', ['tab'=>$tab]) }}" class="btn btn-sm btn-outline-secondary" style="height:36px;">
                        <i class="bi bi-x-lg"></i> Clear
                    </a>
                @endif
            </div>

            @if(count($mediaTotals))
            <div class="summary-chips">
                <a href="{{ route('admin.ldms.index', array_merge(request()->except('media_type','page'))) }}" class="text-decoration-none">
                    <span class="summary-chip {{ !request('media_type') ? 'active' : '' }}">
                        All Media <strong>{{ array_sum($mediaTotals) }}</strong>
                    </span>
                </a>
                @foreach($mediaTotals as $type => $count)
                    <a href="{{ route('admin.ldms.index', array_merge(request()->except('page'), ['media_type'=>$type])) }}" class="text-decoration-none">
                        <span class="summary-chip {{ request('media_type')===$type ? 'active' : '' }}">
                            {{ $mediaLabels[$type] ?? ucfirst($type) }} <strong>{{ $count }}</strong>
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
            <a class="nav-link {{ $tab==='pending'?'active':'' }}" href="{{ route('admin.ldms.index', array_merge(request()->except('tab','page'), ['tab'=>'pending'])) }}">
                <i class="bi bi-clock-history text-warning"></i>
                Pending Release <span class="badge bg-warning text-dark">{{ $pending->total() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab==='released'?'active':'' }}" href="{{ route('admin.ldms.index', array_merge(request()->except('tab','page'), ['tab'=>'released'])) }}">
                <i class="bi bi-check-circle text-success"></i>
                Released <span class="badge bg-success">{{ $released->total() }}</span>
            </a>
        </li>
    </ul>

    <div class="content-card">
        @php $collection = $$tab; @endphp

        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong class="text-muted small">
                {{ $collection->total() }} message{{ $collection->total()!==1?'s':'' }} found
            </strong>
            @if($collection->hasPages())
                <small class="text-muted">Page {{ $collection->currentPage() }} of {{ $collection->lastPage() }}</small>
            @endif
        </div>

        @if($collection->isEmpty())
            <div class="text-center my-5 py-4">
                <i class="bi bi-inbox" style="font-size:48px;color:#D1D5DB;"></i>
                <p class="text-muted mt-2 mb-0">No messages match your filter criteria.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle ldms-table">
                    <thead>
                        <tr>
                            <th style="width:100px;">LDMS ID</th>
                            <th>Student</th>
                            <th>Type</th>
                            <th>Student Status</th>
                            <th>Status</th>
                            <th>{{ $tab === 'released' ? 'Released' : 'Created' }}</th>
                            <th class="text-end no-print" style="width:100px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($collection as $ldms)
                            @php
                                $studentStatus = $ldms->student?->status ?? 'active';
                            @endphp
                            <tr class="row-{{ $tab }}">
                                <td><span class="ref-code">LDMS-{{ str_pad($ldms->ldms_id, 4, '0', STR_PAD_LEFT) }}</span></td>
                                <td>
                                    <div class="fw-semibold">{{ $ldms->student?->full_name ?? '—' }}</div>
                                    <small class="text-muted">{{ $ldms->student_id }}</small>
                                </td>
                                <td>
                                    <span class="media-badge media-{{ $ldms->media_type ?? 'text' }}">
                                        {{ strtoupper($ldms->media_type ?? 'text') }}
                                    </span>
                                    @if(is_array($ldms->media_file_path) && count($ldms->media_file_path))
                                        <small class="text-muted ms-1">+{{ count($ldms->media_file_path) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="student-status-badge student-{{ $studentStatus }}">
                                        {{ $studentStatus === 'deceased' ? 'Deceased' : 'Active' }}
                                    </span>
                                </td>
                                <td>
                                    @if($ldms->is_released)
                                        <span class="status-pill status-released"><i class="bi bi-check-circle-fill"></i> RELEASED</span>
                                    @else
                                        <span class="status-pill status-pending"><i class="bi bi-clock"></i> PENDING</span>
                                    @endif
                                </td>
                                <td>
                                    @if($tab === 'released')
                                        {{ $ldms->date_triggered?->format('d M Y') }}<br>
                                        <small class="text-muted">{{ $ldms->date_triggered?->diffForHumans() }}</small>
                                    @else
                                        {{ $ldms->created_at?->format('d M Y') }}<br>
                                        <small class="text-muted">{{ $ldms->created_at?->diffForHumans() }}</small>
                                    @endif
                                </td>
                                <td class="text-end no-print">
                                    <a href="{{ route('admin.ldms.show', $ldms->ldms_id) }}" class="btn-review">
                                        <i class="bi bi-eye"></i> Review
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3 no-print">
                {{ $collection->links() }}
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
