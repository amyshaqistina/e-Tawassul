@extends('layouts.admin')
@section('title', 'Crisis Reports')
@section('page-title', 'Crisis Reports')

@push('styles')
<style>
    /* ===== Status colour coding ===== */
    .status-pill {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 11px; font-weight: 700; letter-spacing: 0.3px;
        padding: 4px 10px; border-radius: 12px; text-transform: uppercase;
    }
    .status-pending   { background: #FFF4E0; color: #B7791F; border: 1px solid #FBD38D; }
    .status-verified  { background: #E6FAEF; color: #1F7A47; border: 1px solid #9AE6B4; }
    .status-rejected  { background: #FEE2E2; color: #B91C1C; border: 1px solid #FCA5A5; }
    .status-resolved  { background: #DBEAFE; color: #1E40AF; border: 1px solid #93C5FD; }
    .status-active    { background: #EDE9FE; color: #6D28D9; border: 1px solid #C4B5FD; }

    /* ===== Filter bar ===== */
    .filter-bar {
        background: #fff; border: 1px solid #E5E7EB; border-radius: 12px;
        padding: 16px; margin-bottom: 16px;
    }
    .filter-row { display:flex; flex-wrap:wrap; gap:10px; align-items:center; }
    .filter-row .form-control, .filter-row .form-select { font-size: 13px; }
    .search-input-wrap { position: relative; flex: 1; min-width: 220px; }
    .search-input-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#9CA3AF; }
    .search-input-wrap input { padding-left: 34px; }

    /* ===== Summary chips (totals per category) ===== */
    .summary-chips { display:flex; flex-wrap:wrap; gap:8px; margin-top:12px; }
    .summary-chip {
        background:#F3F4F6; border:1px solid #E5E7EB; border-radius:20px;
        padding:4px 12px; font-size:12px; color:#374151;
    }
    .summary-chip strong { color:#111827; margin-left:4px; }
    .summary-chip.active { background:#1E40AF; color:#fff; border-color:#1E40AF; }
    .summary-chip.active strong { color:#fff; }

    /* ===== Table row styling: left border reflecting status ===== */
    table.crisis-table tbody tr { border-left: 4px solid transparent; }
    table.crisis-table tbody tr.row-pending  { border-left-color:#F59E0B; background:#FFFBEB; }
    table.crisis-table tbody tr.row-verified { border-left-color:#10B981; }
    table.crisis-table tbody tr.row-rejected { border-left-color:#EF4444; }

    .nav-tabs .nav-link.active { font-weight: 700; }
    .nav-tabs .nav-link .badge { margin-left: 6px; }

    .btn-print { background:#374151; color:#fff; }
    .btn-print:hover { background:#1F2937; color:#fff; }

    @media print {
        .no-print, .nav-tabs, .filter-bar, .pagination, .btn { display:none !important; }
        .content-card { box-shadow:none !important; border:none !important; }
        table.crisis-table tbody tr { background:#fff !important; border-left:none !important; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- Page header with print button --}}
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <h4 class="mb-0">Crisis Reports</h4>
            <small class="text-muted">Review and verify submitted student crisis reports</small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-print btn-sm" onclick="window.print()">
                <i class="bi bi-printer"></i> Print / Export PDF
            </button>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('admin.crisis.index') }}" id="filterForm" class="no-print">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="filter-bar">
            <div class="filter-row">
                <div class="search-input-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Search by student name, ID, or report #..."
                           value="{{ request('search') }}" oninput="debounceSubmit()">
                </div>

                <select name="crisis_type" class="form-select form-select-sm" style="max-width:170px;" onchange="this.form.submit()">
                    <option value="">All Crisis Types</option>
                    {{-- Matches the crisis_type enum in the crisis table --}}
                    @foreach(['death','accident','illness','medical','natural_disaster','family_emergency'] as $t)
                        <option value="{{ $t }}" {{ request('crisis_type')===$t?'selected':'' }}>
                            {{ ucwords(str_replace('_',' ', $t)) }}
                        </option>
                    @endforeach
                </select>

                {{-- Sub-category dynamically shown based on crisis_type
                     (uses Malaysia Bencana classifications stored in crisis.sub_category) --}}
                <select name="sub_category" class="form-select form-select-sm" style="max-width:170px;" onchange="this.form.submit()">
                    <option value="">All Sub-Categories</option>
                    @if(request('crisis_type')==='accident')
                        @foreach(['road_accident','workplace','sports','other'] as $sc)
                            <option value="{{ $sc }}" {{ request('sub_category')===$sc?'selected':'' }}>{{ ucwords(str_replace('_',' ',$sc)) }}</option>
                        @endforeach
                    @elseif(request('crisis_type')==='natural_disaster')
                        @foreach(['flood','fire','earthquake','landslide','storm'] as $sc)
                            <option value="{{ $sc }}" {{ request('sub_category')===$sc?'selected':'' }}>{{ ucfirst($sc) }}</option>
                        @endforeach
                    @elseif(request('crisis_type')==='medical' || request('crisis_type')==='illness')
                        @foreach(['coma','surgery','chronic','emergency'] as $sc)
                            <option value="{{ $sc }}" {{ request('sub_category')===$sc?'selected':'' }}>{{ ucfirst($sc) }}</option>
                        @endforeach
                    @elseif(request('crisis_type')==='death')
                        @foreach(['natural','accident','illness','other'] as $sc)
                            <option value="{{ $sc }}" {{ request('sub_category')===$sc?'selected':'' }}>{{ ucfirst($sc) }}</option>
                        @endforeach
                    @elseif(request('crisis_type')==='family_emergency')
                        @foreach(['bereavement','financial','displacement','other'] as $sc)
                            <option value="{{ $sc }}" {{ request('sub_category')===$sc?'selected':'' }}>{{ ucfirst($sc) }}</option>
                        @endforeach
                    @endif
                </select>

                <select name="date_range" class="form-select form-select-sm" style="max-width:150px;" onchange="toggleCustomDate()">
                    <option value="">All Time</option>
                    <option value="today"      {{ request('date_range')==='today'?'selected':'' }}>Today</option>
                    <option value="week"       {{ request('date_range')==='week'?'selected':'' }}>This Week</option>
                    <option value="month"      {{ request('date_range')==='month'?'selected':'' }}>This Month</option>
                    <option value="last_week"  {{ request('date_range')==='last_week'?'selected':'' }}>Last Week</option>
                    <option value="custom"     {{ request('date_range')==='custom'?'selected':'' }}>Custom range...</option>
                </select>

                <div id="customDateWrap" class="d-flex gap-2" style="{{ request('date_range')==='custom' ? '' : 'display:none !important;' }}">
                    <input type="date" name="date_from" class="form-control form-control-sm" style="max-width:140px;" value="{{ request('date_from') }}">
                    <input type="date" name="date_to"   class="form-control form-control-sm" style="max-width:140px;" value="{{ request('date_to') }}">
                    <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-funnel"></i></button>
                </div>

                @if(request()->anyFilled(['search','crisis_type','sub_category','date_range','date_from','date_to']))
                    <a href="{{ route('admin.crisis.index', ['tab'=>$tab]) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg"></i> Clear
                    </a>
                @endif
            </div>

            {{-- Quick summary chips showing totals per crisis type within current results --}}
            @if(isset($categoryTotals) && count($categoryTotals))
            <div class="summary-chips">
                <span class="summary-chip {{ !request('crisis_type') ? 'active' : '' }}">
                    All Categories <strong>{{ array_sum($categoryTotals) }}</strong>
                </span>
                @foreach($categoryTotals as $type => $count)
                    <a href="{{ route('admin.crisis.index', array_merge(request()->query(), ['crisis_type'=>$type])) }}"
                       class="text-decoration-none">
                        <span class="summary-chip {{ request('crisis_type')===$type ? 'active' : '' }}">
                            {{ ucwords(str_replace('_',' ',$type)) }} <strong>{{ $count }}</strong>
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
            <a class="nav-link {{ $tab==='pending'?'active':'' }}" href="?tab=pending">
                <i class="bi bi-clock-history text-warning"></i>
                Pending <span class="badge bg-warning text-dark">{{ $pending->total() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab==='verified'?'active':'' }}" href="?tab=verified">
                <i class="bi bi-check-circle text-success"></i>
                Verified <span class="badge bg-success">{{ $verified->total() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab==='rejected'?'active':'' }}" href="?tab=rejected">
                <i class="bi bi-x-circle text-danger"></i>
                Rejected <span class="badge bg-danger">{{ $rejected->total() }}</span>
            </a>
        </li>
    </ul>

    {{-- Results card --}}
    <div class="content-card">
        @php $collection = $$tab; @endphp

        <div class="d-flex justify-content-between align-items-center mb-2 px-2">
            <strong class="text-muted small">
                {{ $collection->total() }} report{{ $collection->total()!==1?'s':'' }} found
                @if(request('crisis_type'))
                    in <em>{{ ucwords(str_replace('_',' ',request('crisis_type'))) }}</em>
                @endif
                @if(request('sub_category'))
                    / <em>{{ ucwords(str_replace('_',' ',request('sub_category'))) }}</em>
                @endif
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
                            <th style="width:60px;">#</th>
                            <th>Student</th>
                            <th>Crisis Type</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th class="text-end no-print"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($collection as $r)
                            @php
                                $statusKey = $r->report_status ?? 'pending';
                                $rowClass  = 'row-' . $statusKey;
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td><span class="text-muted">{{ $r->report_id }}</span></td>
                                <td>
                                    <div class="fw-semibold">{{ $r->student?->full_name ?? '—' }}</div>
                                    <small class="text-muted">{{ $r->student_id }}</small>
                                </td>
                                <td>
                                    {{ ucwords(str_replace('_',' ', $r->crisis?->crisis_type ?? '')) }}
                                    @if($r->crisis?->sub_category)
                                        <br><small class="text-muted">{{ ucwords(str_replace('_',' ', $r->crisis->sub_category)) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-pill status-{{ $statusKey }}">
                                        {{ strtoupper(str_replace('_',' ', $statusKey)) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $r->date_reported?->format('d M Y') }}
                                    <br><small class="text-muted">{{ $r->date_reported?->diffForHumans() }}</small>
                                </td>
                                <td class="text-end no-print">
                                    <a href="{{ route('admin.crisis.show', $r->report_id) }}"
                                       class="btn btn-primary btn-sm">
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
