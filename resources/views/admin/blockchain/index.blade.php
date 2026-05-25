@extends('layouts.admin')
@section('title', 'Blockchain Audit')

@push('styles')
<style>
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
    table.bc-table { margin-bottom:0; }
    table.bc-table thead th { font-size:12px; font-weight:700; color:#6B7280; text-transform:uppercase;
                              letter-spacing:0.3px; padding:10px 12px; border-bottom:2px solid #E5E7EB;
                              white-space:nowrap; }
    table.bc-table tbody td { padding:12px; vertical-align:middle; }
    table.bc-table tbody tr { transition:background 0.15s; border-left:3px solid #1E40AF; }
    table.bc-table tbody tr.row-simulation { border-left-color:#F59E0B; }
    table.bc-table tbody tr.row-quorum     { border-left-color:#10B981; }
    table.bc-table tbody tr:hover { background:#F9FAFB; }

    .event-badge { font-size:10px; font-weight:700; padding:3px 10px; border-radius:4px;
                   text-transform:uppercase; letter-spacing:0.4px;
                   background:#EFF6FF; color:#1E40AF; white-space:nowrap; }
    .mode-pill { font-size:10px; font-weight:700; padding:3px 10px; border-radius:10px;
                 text-transform:uppercase; letter-spacing:0.4px; white-space:nowrap; }
    .mode-quorum     { background:#D1FAE5; color:#065F46; }
    .mode-simulation { background:#FEF3C7; color:#92400E; }

    .hash-cell { font-family:'Courier New',monospace; font-size:11px; color:#EC4899; }
    .ref-cell  { font-family:'Courier New',monospace; font-size:11px; color:#6B7280; }

    .stat-cards { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:14px; }
    .stat-card-mini { background:#fff; border:1px solid #E5E7EB; border-radius:12px;
                      padding:18px 20px; display:flex; align-items:center; gap:14px; }
    .stat-card-mini .ic { width:48px; height:48px; border-radius:10px;
                          display:flex; align-items:center; justify-content:center; font-size:22px; }
    .stat-card-mini .ic.primary { background:#EFF6FF; color:#1E40AF; }
    .stat-card-mini .ic.success { background:#D1FAE5; color:#065F46; }
    .stat-card-mini .ic.warning { background:#FEF3C7; color:#92400E; }
    .stat-card-mini .ic.pdf     { background:#FEE2E2; color:#991B1B; }
    .stat-card-mini .val { font-size:22px; font-weight:700; color:#111827; line-height:1; }
    .stat-card-mini .lab { font-size:11px; color:#6B7280; text-transform:uppercase; letter-spacing:0.4px;
                           font-weight:600; margin-top:3px; }
    .stat-card-mini.pdf-link { cursor:pointer; text-decoration:none; }
    .stat-card-mini.pdf-link:hover { background:#F9FAFB; }

    .btn-print { background:#374151; color:#fff; }
    .btn-print:hover { background:#1F2937; color:#fff; }

    @media (max-width: 1100px) { .stat-cards { grid-template-columns:repeat(2,1fr); } }
    @media (max-width: 900px)  { .filter-grid { flex-wrap:wrap; } }
    @media print {
        .no-print, .filter-bar, .pagination, .btn { display:none !important; }
        .content-card, .stat-card-mini { box-shadow:none !important; border:1px solid #E5E7EB !important; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <h4 class="mb-0">Blockchain Audit Log</h4>
            <small class="text-muted">Tamper-proof record of every verification, release, and donation event</small>
        </div>
        <button type="button" class="btn btn-print btn-sm" onclick="window.print()">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>

    {{-- Stat cards --}}
    <div class="stat-cards">
        <div class="stat-card-mini">
            <div class="ic primary"><i class="bi bi-link-45deg"></i></div>
            <div>
                <div class="val">{{ $stats['total'] }}</div>
                <div class="lab">Total records</div>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="ic success"><i class="bi bi-cloud-check"></i></div>
            <div>
                <div class="val">{{ $stats['quorum'] }}</div>
                <div class="lab">Quorum-anchored</div>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="ic warning"><i class="bi bi-cpu"></i></div>
            <div>
                <div class="val">{{ $stats['simulation'] }}</div>
                <div class="lab">Simulation mode</div>
            </div>
        </div>
        <a href="{{ route('admin.pdf.audit') }}" class="stat-card-mini pdf-link text-decoration-none">
            <div class="ic pdf"><i class="bi bi-file-earmark-pdf"></i></div>
            <div>
                <div class="val" style="font-size:14px; padding-top:3px;">Export Audit Log</div>
                <div class="lab" style="color:#EC4899;">as PDF</div>
            </div>
        </a>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('admin.blockchain.index') }}" id="filterForm" class="no-print">
        <div class="filter-bar">
            <div class="filter-grid">
                <div class="search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search by hash, reference, event type..."
                           value="{{ request('search') }}" oninput="debounceSubmit()">
                </div>

                <select name="event_type" class="form-select" style="width:180px;" onchange="this.form.submit()">
                    <option value="">All Events</option>
                    @foreach($eventTypeOptions as $t)
                        <option value="{{ $t }}" {{ request('event_type')===$t?'selected':'' }}>{{ $t }}</option>
                    @endforeach
                </select>

                <select name="mode" class="form-select" style="width:140px;" onchange="this.form.submit()">
                    <option value="">All Modes</option>
                    <option value="simulation" {{ request('mode')==='simulation'?'selected':'' }}>Simulation</option>
                    <option value="quorum"     {{ request('mode')==='quorum'?'selected':'' }}>Quorum</option>
                </select>

                <select name="ref_table" class="form-select" style="width:170px;" onchange="this.form.submit()">
                    <option value="">All References</option>
                    @foreach($refTableOptions as $t)
                        <option value="{{ $t }}" {{ request('ref_table')===$t?'selected':'' }}>{{ $t }}</option>
                    @endforeach
                </select>

                <select name="date_range" class="form-select" style="width:140px;" onchange="toggleCustomDate()">
                    <option value="">All Time</option>
                    <option value="today"     {{ request('date_range')==='today'?'selected':'' }}>Today</option>
                    <option value="week"      {{ request('date_range')==='week'?'selected':'' }}>This Week</option>
                    <option value="last_week" {{ request('date_range')==='last_week'?'selected':'' }}>Last Week</option>
                    <option value="month"     {{ request('date_range')==='month'?'selected':'' }}>This Month</option>
                    <option value="custom"    {{ request('date_range')==='custom'?'selected':'' }}>Custom...</option>
                </select>

                <div id="customDateWrap" class="d-flex gap-2" style="{{ request('date_range')==='custom' ? '' : 'display:none !important;' }}">
                    <input type="date" name="date_from" class="form-control" style="width:135px;" value="{{ request('date_from') }}">
                    <input type="date" name="date_to"   class="form-control" style="width:135px;" value="{{ request('date_to') }}">
                </div>

                <div class="total-chip">
                    Total: <strong>{{ $records->total() }}</strong>
                </div>

                @if(request()->anyFilled(['search','event_type','mode','ref_table','date_range','date_from','date_to']))
                    <a href="{{ route('admin.blockchain.index') }}" class="btn btn-sm btn-outline-secondary" style="height:36px;">
                        <i class="bi bi-x-lg"></i> Clear
                    </a>
                @endif
            </div>

            {{-- Event-type chips --}}
            @if(count($stats['by_type']))
            <div class="summary-chips">
                <a href="{{ route('admin.blockchain.index', request()->except('event_type','page')) }}" class="text-decoration-none">
                    <span class="summary-chip {{ !request('event_type') ? 'active' : '' }}">
                        All Events <strong>{{ array_sum($stats['by_type']) }}</strong>
                    </span>
                </a>
                @foreach($stats['by_type'] as $type => $n)
                    <a href="{{ route('admin.blockchain.index', array_merge(request()->except('page'), ['event_type'=>$type])) }}" class="text-decoration-none">
                        <span class="summary-chip {{ request('event_type')===$type ? 'active' : '' }}">
                            {{ $type }} <strong>{{ $n }}</strong>
                        </span>
                    </a>
                @endforeach
            </div>
            @endif
        </div>
    </form>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="text-muted small">
                        {{ $records->total() }} record{{ $records->total()!==1?'s':'' }} found
                    </strong>
                    @if($records->hasPages())
                        <small class="text-muted">Page {{ $records->currentPage() }} of {{ $records->lastPage() }}</small>
                    @endif
                </div>

                @if($records->isEmpty())
                    <div class="text-center my-5 py-4">
                        <i class="bi bi-link" style="font-size:48px;color:#D1D5DB;"></i>
                        <p class="text-muted mt-2 mb-0">No records match your filter criteria.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle bc-table">
                            <thead>
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th style="width:155px;">Time</th>
                                    <th>Event</th>
                                    <th>Reference</th>
                                    <th>Mode</th>
                                    <th>Hash</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $r)
                                    <tr class="row-{{ $r->mode === 'quorum' ? 'quorum' : 'simulation' }}">
                                        <td>{{ $r->blockchain_id }}</td>
                                        <td>
                                            <small>{{ $r->timestamp?->format('d M Y') }}</small><br>
                                            <small class="text-muted">{{ $r->timestamp?->format('H:i:s') }}</small>
                                        </td>
                                        <td><span class="event-badge">{{ $r->data_from }}</span></td>
                                        <td>
                                            @if($r->reference_table)
                                                <span class="ref-cell">{{ $r->reference_table }}#{{ $r->reference_id }}</span>
                                            @else
                                                <small class="text-muted">—</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="mode-pill mode-{{ $r->mode }}">
                                                @if($r->mode === 'quorum')
                                                    <i class="bi bi-cloud-check"></i>
                                                @else
                                                    <i class="bi bi-cpu"></i>
                                                @endif
                                                {{ ucfirst($r->mode) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="hash-cell" title="{{ $r->stored_data }}">
                                                {{ substr($r->stored_data, 0, 10) }}…{{ substr($r->stored_data, -6) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3 no-print">
                        {{ $records->links() }}
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="content-card">
                <h5 class="mb-3"><i class="bi bi-search"></i> Verify a hash</h5>
                <p class="small text-muted">Paste a SHA-256 hash to check whether it exists in the audit chain.</p>
                <form method="POST" action="{{ route('admin.blockchain.verify') }}">
                    @csrf
                    <input type="text" name="hash" class="form-control mb-2"
                           placeholder="64-character SHA-256 hash"
                           value="{{ old('hash') }}" required>
                    @error('hash')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                    <button class="btn btn-primary w-100">Verify</button>
                </form>

                @php $vr = session('verify_result'); @endphp
                @if($vr)
                    <div class="alert {{ $vr['ok'] ? 'alert-success' : 'alert-danger' }} mt-3 small">
                        @if($vr['ok'])
                            <strong><i class="bi bi-shield-check"></i> Match found</strong>
                            <div>Event: <strong>{{ $vr['record']->data_from }}</strong></div>
                            <div>Recorded: {{ $vr['record']->timestamp?->format('d M Y, H:i:s') }}</div>
                            <div>Mode: {{ $vr['record']->mode }}</div>
                        @else
                            <strong><i class="bi bi-x-circle"></i> No match</strong>
                            <div>This hash was not found in the audit chain.</div>
                        @endif
                    </div>
                @endif

                <h6 class="mt-4 mb-2 text-uppercase small text-muted">By event type</h6>
                <ul class="list-unstyled small mb-0">
                    @foreach($stats['by_type'] as $type => $n)
                        <li class="d-flex justify-content-between border-bottom py-1">
                            <span>{{ $type }}</span>
                            <strong>{{ $n }}</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
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
