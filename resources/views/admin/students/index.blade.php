@extends('layouts.admin')
@section('title', 'Student Records')

@push('styles')
<style>
    .status-pill {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 11px; font-weight: 700; letter-spacing: 0.3px;
        padding: 4px 10px; border-radius: 12px; text-transform: uppercase;
        white-space: nowrap;
    }
    .status-active   { background:#D1FAE5; color:#065F46; border:1px solid #6EE7B7; }
    .status-deceased { background:#374151; color:#fff;    border:1px solid #1F2937; }
    .status-inactive { background:#F3F4F6; color:#6B7280; border:1px solid #E5E7EB; }
    .status-suspended{ background:#FEF3C7; color:#92400E; border:1px solid #FCD34D; }

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
    table.std-table { margin-bottom:0; }
    table.std-table thead th { font-size:12px; font-weight:700; color:#6B7280; text-transform:uppercase;
                               letter-spacing:0.3px; padding:10px 12px; border-bottom:2px solid #E5E7EB;
                               white-space:nowrap; }
    table.std-table tbody td { padding:12px; vertical-align:middle; }
    table.std-table tbody tr { border-left:3px solid transparent; transition:background 0.15s; }
    table.std-table tbody tr.row-active   { border-left-color:#10B981; }
    table.std-table tbody tr.row-deceased { border-left-color:#374151; background:#F9FAFB; }
    table.std-table tbody tr:hover { background:#F9FAFB; }

    .ref-code { font-family:'Courier New',monospace; font-size:11px; color:#1E40AF;
                background:#EFF6FF; padding:3px 8px; border-radius:4px; white-space:nowrap; }

    .btn-print { background:#374151; color:#fff; }
    .btn-print:hover { background:#1F2937; color:#fff; }

    @media (max-width: 900px) { .filter-grid { flex-wrap:wrap; } }
    @media print {
        .no-print, .filter-bar, .pagination, .btn { display:none !important; }
        .content-card { box-shadow:none !important; border:none !important; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <h4 class="mb-0">Student Records</h4>
            <small class="text-muted">All students synced from iMaalum and their current status</small>
        </div>
        <button type="button" class="btn btn-print btn-sm" onclick="window.print()">
            <i class="bi bi-printer"></i> Print / Export PDF
        </button>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('admin.students.index') }}" id="filterForm" class="no-print">
        <div class="filter-bar">
            <div class="filter-grid">
                <div class="search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search by ID, name, email, kulliyyah..."
                           value="{{ request('search') }}" oninput="debounceSubmit()">
                </div>

                <select name="kulliyyah" class="form-select" style="width:200px;" onchange="this.form.submit()">
                    <option value="">All Kulliyyahs</option>
                    @foreach($kulliyyahOptions as $k)
                        <option value="{{ $k }}" {{ request('kulliyyah')===$k?'selected':'' }}>{{ \Illuminate\Support\Str::limit($k, 30) }}</option>
                    @endforeach
                </select>

                <select name="year" class="form-select" style="width:120px;" onchange="this.form.submit()">
                    <option value="">All Years</option>
                    @foreach(['1','2','3','4','5','6'] as $y)
                        <option value="{{ $y }}" {{ request('year')===$y?'selected':'' }}>Year {{ $y }}</option>
                    @endforeach
                </select>

                <select name="status" class="form-select" style="width:140px;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="active"    {{ request('status')==='active'?'selected':'' }}>Active</option>
                    <option value="deceased"  {{ request('status')==='deceased'?'selected':'' }}>Deceased</option>
                    <option value="inactive"  {{ request('status')==='inactive'?'selected':'' }}>Inactive</option>
                    <option value="suspended" {{ request('status')==='suspended'?'selected':'' }}>Suspended</option>
                </select>

                <select name="sync" class="form-select" style="width:160px;" onchange="this.form.submit()">
                    <option value="">Any Sync Status</option>
                    <option value="synced" {{ request('sync')==='synced'?'selected':'' }}>Ever synced</option>
                    <option value="stale"  {{ request('sync')==='stale'?'selected':'' }}>Stale (&gt; 7 days)</option>
                    <option value="never"  {{ request('sync')==='never'?'selected':'' }}>Never synced</option>
                </select>

                <div class="total-chip">
                    Total: <strong>{{ $students->total() }}</strong>
                </div>

                @if(request()->anyFilled(['search','status','kulliyyah','year','sync']))
                    <a href="{{ route('admin.students.index') }}" class="btn btn-sm btn-outline-secondary" style="height:36px;">
                        <i class="bi bi-x-lg"></i> Clear
                    </a>
                @endif
            </div>

            {{-- Status breakdown chips --}}
            @if(count($statusTotals))
            <div class="summary-chips">
                <a href="{{ route('admin.students.index', request()->except('status','page')) }}" class="text-decoration-none">
                    <span class="summary-chip {{ !request('status') ? 'active' : '' }}">
                        All <strong>{{ array_sum($statusTotals) }}</strong>
                    </span>
                </a>
                @foreach(['active' => 'Active', 'deceased' => 'Deceased', 'inactive' => 'Inactive', 'suspended' => 'Suspended'] as $k => $label)
                    @if(isset($statusTotals[$k]) && $statusTotals[$k] > 0)
                        <a href="{{ route('admin.students.index', array_merge(request()->except('page'), ['status'=>$k])) }}" class="text-decoration-none">
                            <span class="summary-chip {{ request('status')===$k ? 'active' : '' }}">
                                {{ $label }} <strong>{{ $statusTotals[$k] }}</strong>
                            </span>
                        </a>
                    @endif
                @endforeach
            </div>
            @endif
        </div>
    </form>

    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong class="text-muted small">
                {{ $students->total() }} student{{ $students->total()!==1?'s':'' }} found
            </strong>
            @if($students->hasPages())
                <small class="text-muted">Page {{ $students->currentPage() }} of {{ $students->lastPage() }}</small>
            @endif
        </div>

        @if($students->isEmpty())
            <div class="text-center my-5 py-4">
                <i class="bi bi-person-lines-fill" style="font-size:48px;color:#D1D5DB;"></i>
                <p class="text-muted mt-2 mb-0">No students match your filter criteria.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle std-table">
                    <thead>
                        <tr>
                            <th style="width:110px;">Student ID</th>
                            <th>Name</th>
                            <th>Kulliyyah / Programme</th>
                            <th>Year</th>
                            <th>Status</th>
                            <th>Last iMaalum sync</th>
                            <th class="text-end no-print" style="width:80px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $s)
                            @php
                                $statusKey = strtolower($s->status ?? 'active');
                                if (!in_array($statusKey, ['active','deceased','inactive','suspended'], true)) {
                                    $statusKey = 'active';
                                }
                            @endphp
                            <tr class="row-{{ $statusKey }}">
                                <td><span class="ref-code">{{ $s->student_id }}</span></td>
                                <td>
                                    <div class="fw-semibold">{{ $s->full_name }}</div>
                                    <small class="text-muted">{{ $s->email }}</small>
                                </td>
                                <td>
                                    <div>{{ \Illuminate\Support\Str::limit($s->kulliyyah ?? '—', 36) }}</div>
                                    @if($s->programme)
                                        <small class="text-muted">{{ $s->programme }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($s->year_of_study)
                                        <small class="text-muted">Year {{ $s->year_of_study }}</small>
                                    @else
                                        <small class="text-muted">—</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-pill status-{{ $statusKey }}">
                                        @if($statusKey === 'deceased')<i class="bi bi-circle-fill" style="font-size:6px;"></i>@endif
                                        @if($statusKey === 'active')<i class="bi bi-check-circle-fill"></i>@endif
                                        {{ strtoupper($statusKey) }}
                                    </span>
                                </td>
                                <td>
                                    @if($s->imaalum_synced_at)
                                        <small>{{ $s->imaalum_synced_at->diffForHumans() }}</small>
                                    @else
                                        <small class="text-muted">Never</small>
                                    @endif
                                </td>
                                <td class="text-end no-print">
                                    <a href="{{ route('admin.students.show', $s->student_id) }}"
                                       style="background:#1E40AF; color:#fff; font-size:12px; font-weight:600;
                                              padding:6px 12px; border-radius:6px;
                                              display:inline-flex; align-items:center; gap:5px;
                                              text-decoration:none;">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3 no-print">
                {{ $students->links() }}
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
</script>
@endpush
@endsection
