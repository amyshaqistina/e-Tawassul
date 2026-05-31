@extends('layouts.admin')
@section('title', 'Death Confirmations')

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

    /* ===== Filter bar ===== */
    .filter-bar {
        background:#fff; border:1px solid #E5E7EB; border-radius:12px;
        padding:12px 14px; margin-bottom:14px;
    }
    .filter-grid { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
    .filter-grid > * { flex-shrink:0; }
    .filter-grid .form-control,
    .filter-grid .form-select { font-size:13px; height:36px; }
    .filter-grid .search-wrap { flex:1 1 240px; min-width:200px; max-width:340px; position:relative; }
    .filter-grid .search-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#9CA3AF; font-size:13px; }
    .filter-grid .search-wrap input { padding-left:34px; }
    .filter-grid .total-chip {
        background:#EFF6FF; color:#1E40AF; border:1px solid #BFDBFE;
        padding:0 14px; height:36px; border-radius:8px;
        display:flex; align-items:center; gap:6px; font-size:13px; font-weight:600;
        white-space:nowrap; margin-left:auto;
    }

    /* ===== Table ===== */
    .content-card { background:#fff; border:1px solid #E5E7EB; border-radius:12px; padding:16px; }
    table.death-table { margin-bottom:0; }
    table.death-table thead th {
        font-size:12px; font-weight:700; color:#6B7280; text-transform:uppercase;
        letter-spacing:0.3px; padding:10px 12px; border-bottom:2px solid #E5E7EB;
        white-space:nowrap;
    }
    table.death-table tbody td { padding:12px; vertical-align:middle; }
    table.death-table tbody tr { border-left:3px solid transparent; transition:background 0.15s; }
    table.death-table tbody tr.row-pending  { border-left-color:#F59E0B; background:#FFFBEB; }
    table.death-table tbody tr.row-verified { border-left-color:#10B981; }
    table.death-table tbody tr.row-rejected { border-left-color:#EF4444; }
    table.death-table tbody tr:hover { background:#F9FAFB; }
    table.death-table tbody tr.row-pending:hover { background:#FEF9E1; }

    .ref-code {
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

    @media (max-width: 900px) { .filter-grid { flex-wrap:wrap; } }
    @media print {
        .no-print, .nav-tabs, .filter-bar, .pagination, .btn { display:none !important; }
        .content-card { box-shadow:none !important; border:none !important; }
        table.death-table tbody tr { background:#fff !important; border-left:none !important; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid pb-3">

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <h4 class="mb-0">Death Confirmations</h4>
            <small class="text-muted">Review and verify submissions from next of kin</small>
        </div>
        <button type="button" class="btn btn-print btn-sm" onclick="window.print()">
            <i class="bi bi-printer"></i> Print / Export PDF
        </button>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('admin.death.index') }}" id="filterForm" class="no-print">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="filter-bar">
            <div class="filter-grid">
                <div class="search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search by student ID, NoK name, confirmation #..."
                           value="{{ request('search') }}" oninput="debounceSubmit()">
                </div>

                <select name="has_doc" class="form-select" style="width:180px;" onchange="this.form.submit()">
                    <option value="">Any Document</option>
                    <option value="yes" {{ request('has_doc')==='yes'?'selected':'' }}>Has Document</option>
                    <option value="no"  {{ request('has_doc')==='no'?'selected':'' }}>Missing Document</option>
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
                    Total: <strong>{{ $pending->total() + $verified->total() + $rejected->total() }}</strong>
                </div>

                @if(request()->anyFilled(['search','has_doc','date_range','date_from','date_to']))
                    <a href="{{ route('admin.death.index', ['tab'=>$tab]) }}" class="btn btn-sm btn-outline-secondary" style="height:36px;">
                        <i class="bi bi-x-lg"></i> Clear
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-3 no-print">
        <li class="nav-item">
            <a class="nav-link {{ $tab==='pending'?'active':'' }}" href="{{ route('admin.death.index', array_merge(request()->except('tab','page'), ['tab'=>'pending'])) }}">
                <i class="bi bi-clock-history text-warning"></i>
                Pending <span class="badge bg-warning text-dark">{{ $pending->total() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab==='verified'?'active':'' }}" href="{{ route('admin.death.index', array_merge(request()->except('tab','page'), ['tab'=>'verified'])) }}">
                <i class="bi bi-check-circle text-success"></i>
                Verified <span class="badge bg-success">{{ $verified->total() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab==='rejected'?'active':'' }}" href="{{ route('admin.death.index', array_merge(request()->except('tab','page'), ['tab'=>'rejected'])) }}">
                <i class="bi bi-x-circle text-danger"></i>
                Rejected <span class="badge bg-danger">{{ $rejected->total() }}</span>
            </a>
        </li>
    </ul>

    <div class="content-card">
        @php $collection = $$tab; @endphp

        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong class="text-muted small">
                {{ $collection->total() }} confirmation{{ $collection->total()!==1?'s':'' }} found
            </strong>
            @if($collection->hasPages())
                <small class="text-muted">Page {{ $collection->currentPage() }} of {{ $collection->lastPage() }}</small>
            @endif
        </div>

        @if($collection->isEmpty())
            <div class="text-center my-5 py-4">
                <i class="bi bi-inbox" style="font-size:48px;color:#D1D5DB;"></i>
                <p class="text-muted mt-2 mb-0">No confirmations match your filter criteria.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle death-table">
                    <thead>
                        <tr>
                            <th style="width:120px;">Confirmation</th>
                            <th>Student</th>
                            <th>Submitted by NoK</th>
                            <th>Document</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th class="text-end no-print" style="width:100px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($collection as $c)
                            <tr class="row-{{ $c->status }}">
                                <td><span class="ref-code">DC-{{ str_pad($c->confirmation_id, 6, '0', STR_PAD_LEFT) }}</span></td>
                                <td>
                                    <div class="fw-semibold">{{ $c->student?->full_name ?? '—' }}</div>
                                    <small class="text-muted">{{ $c->student_id }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $c->nextOfKin?->full_name ?? '—' }}</div>
                                    <small class="text-muted">{{ $c->nextOfKin?->relationship_to_student ?? 'Next of Kin' }}</small>
                                </td>
                                <td>
                                    @if($c->media_file_path)
                                        <span class="text-success small"><i class="bi bi-file-earmark-check"></i> Attached</span>
                                    @else
                                        <span class="text-muted small"><i class="bi bi-file-earmark-x"></i> None</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-pill status-{{ $c->status }}">
                                        @if($c->status==='pending')<i class="bi bi-clock"></i>@endif
                                        @if($c->status==='verified')<i class="bi bi-check-circle-fill"></i>@endif
                                        @if($c->status==='rejected')<i class="bi bi-x-circle-fill"></i>@endif
                                        {{ strtoupper($c->status) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $c->date_triggered?->format('d M Y') }}
                                    <br><small class="text-muted">{{ $c->date_triggered?->format('h:i A') }}</small>
                                </td>
                                <td class="text-end no-print">
                                    <a href="{{ route('admin.death.show', $c->confirmation_id) }}" class="btn-review">
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
