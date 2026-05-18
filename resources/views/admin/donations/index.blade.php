@extends('layouts.admin')
@section('title', 'Donations')
@section('page-title', 'Donations')

@php
    $typeLabels = [
        'medical'          => 'Medical Emergency',
        'illness'          => 'Medical Emergency',
        'accident'         => 'Accident',
        'natural_disaster' => 'Natural Disaster',
        'death'            => 'Death / Bereavement',
        'family_emergency' => 'Family Emergency',
    ];

    $paymentMethods = [
        'fpx'         => 'FPX (Online Banking)',
        'card'        => 'Credit/Debit Card',
        'duitnow'     => 'DuitNow QR',
        'transfer'    => 'Bank Transfer',
        'cash'        => 'Cash',
    ];
@endphp

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
    .filter-grid .total-chip { background:#ECFDF5; color:#065F46; border:1px solid #6EE7B7;
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
    .summary-chip .amt { color:#6B7280; font-size:11px; margin-left:6px; }
    .summary-chip.active .amt { color:#DBEAFE; }

    .stat-cards { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:14px; }
    .stat-card { background:#fff; border:1px solid #E5E7EB; border-radius:12px; padding:18px 20px; }
    .stat-card .label { font-size:11px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:0.5px; }
    .stat-card .value { font-size:24px; font-weight:700; color:#111827; margin-top:4px; }
    .stat-card .value.green { color:#065F46; }
    .stat-card .value.blue  { color:#1E40AF; }

    .content-card { background:#fff; border:1px solid #E5E7EB; border-radius:12px; padding:16px; }
    table.don-table { margin-bottom:0; }
    table.don-table thead th { font-size:12px; font-weight:700; color:#6B7280; text-transform:uppercase;
                               letter-spacing:0.3px; padding:10px 12px; border-bottom:2px solid #E5E7EB;
                               white-space:nowrap; }
    table.don-table tbody td { padding:12px; vertical-align:middle; }
    table.don-table tbody tr { transition:background 0.15s; border-left:3px solid #10B981; }
    table.don-table tbody tr:hover { background:#F9FAFB; }

    .ref-code { font-family:'Courier New',monospace; font-size:11px; color:#1E40AF;
                background:#EFF6FF; padding:3px 8px; border-radius:4px; white-space:nowrap; }
    .method-badge { font-size:10px; font-weight:700; padding:3px 8px; border-radius:4px; text-transform:uppercase;
                    letter-spacing:0.4px; background:#E5E7EB; color:#374151; }
    .amount-cell { font-weight:700; color:#065F46; white-space:nowrap; }
    .blockchain-cell { font-family:monospace; font-size:11px; color:#6B7280; }

    .btn-print { background:#374151; color:#fff; }
    .btn-print:hover { background:#1F2937; color:#fff; }

    @media (max-width: 900px) { .filter-grid { flex-wrap:wrap; } .stat-cards { grid-template-columns:1fr; } }
    @media print {
        .no-print, .filter-bar, .pagination, .btn { display:none !important; }
        .content-card, .stat-card { box-shadow:none !important; border:1px solid #E5E7EB !important; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <h4 class="mb-0">Donations</h4>
            <small class="text-muted">All donations received across verified crisis cases</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.donations.create') }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-lg"></i> Add Donation Manually
            </a>
            <button type="button" class="btn btn-print btn-sm" onclick="window.print()">
                <i class="bi bi-printer"></i> Print / Export PDF
            </button>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="stat-cards">
        <div class="stat-card">
            <div class="label">Total Raised (All Time)</div>
            <div class="value green">RM {{ number_format($totalRaised, 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Filtered Total</div>
            <div class="value blue">RM {{ number_format($totalFiltered, 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Donations (Filtered)</div>
            <div class="value">{{ number_format($countFiltered) }}</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('admin.donations.index') }}" id="filterForm" class="no-print">
        <div class="filter-bar">
            <div class="filter-grid">
                <div class="search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search by donor name, email, donation # or crisis #..."
                           value="{{ request('search') }}" oninput="debounceSubmit()">
                </div>

                <select name="crisis_type" class="form-select" style="width:160px;" onchange="this.form.submit()">
                    <option value="">All Crisis Types</option>
                    @foreach($typeLabels as $value => $label)
                        @if($value !== 'illness')
                            <option value="{{ $value }}" {{ request('crisis_type')===$value?'selected':'' }}>{{ $label }}</option>
                        @endif
                    @endforeach
                </select>

                <select name="payment_method" class="form-select" style="width:150px;" onchange="this.form.submit()">
                    <option value="">All Methods</option>
                    @foreach($paymentMethods as $val => $label)
                        <option value="{{ $val }}" {{ request('payment_method')===$val?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="amount_band" class="form-select" style="width:140px;" onchange="this.form.submit()">
                    <option value="">Any Amount</option>
                    <option value="small"  {{ request('amount_band')==='small'?'selected':'' }}>Below RM 100</option>
                    <option value="medium" {{ request('amount_band')==='medium'?'selected':'' }}>RM 100 – 500</option>
                    <option value="large"  {{ request('amount_band')==='large'?'selected':'' }}>RM 500 – 5K</option>
                    <option value="major"  {{ request('amount_band')==='major'?'selected':'' }}>Over RM 5K</option>
                </select>

                <select name="date_range" class="form-select" style="width:130px;" onchange="toggleCustomDate()">
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
                    Showing: <strong>{{ $donations->total() }}</strong>
                </div>

                @if(request()->anyFilled(['search','crisis_type','payment_method','amount_band','date_range','date_from','date_to']))
                    <a href="{{ route('admin.donations.index') }}" class="btn btn-sm btn-outline-secondary" style="height:36px;">
                        <i class="bi bi-x-lg"></i> Clear
                    </a>
                @endif
            </div>

            @if(count($categoryTotals))
            <div class="summary-chips">
                <a href="{{ route('admin.donations.index', request()->except('crisis_type','page')) }}" class="text-decoration-none">
                    <span class="summary-chip {{ !request('crisis_type') ? 'active' : '' }}">
                        All Categories <strong>{{ $categoryTotals->sum('total') }}</strong>
                        <span class="amt">RM {{ number_format($categoryTotals->sum('amount'),0) }}</span>
                    </span>
                </a>
                @foreach($categoryTotals as $type => $row)
                    <a href="{{ route('admin.donations.index', array_merge(request()->except('page'), ['crisis_type'=>$type])) }}" class="text-decoration-none">
                        <span class="summary-chip {{ request('crisis_type')===$type ? 'active' : '' }}">
                            {{ $typeLabels[$type] ?? ucwords(str_replace('_',' ',$type)) }}
                            <strong>{{ $row->total }}</strong>
                            <span class="amt">RM {{ number_format($row->amount,0) }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
            @endif
        </div>
    </form>

    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong class="text-muted small">
                {{ $donations->total() }} donation{{ $donations->total()!==1?'s':'' }} found
            </strong>
            @if($donations->hasPages())
                <small class="text-muted">Page {{ $donations->currentPage() }} of {{ $donations->lastPage() }}</small>
            @endif
        </div>

        @if($donations->isEmpty())
            <div class="text-center my-5 py-4">
                <i class="bi bi-heart" style="font-size:48px;color:#D1D5DB;"></i>
                <p class="text-muted mt-2 mb-0">No donations match your filter criteria.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle don-table">
                    <thead>
                        <tr>
                            <th style="width:80px;">#</th>
                            <th>Date</th>
                            <th>Donor</th>
                            <th>Crisis</th>
                            <th>Method</th>
                            <th>Source</th>
                            <th class="text-end">Amount</th>
                            <th>Blockchain</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($donations as $d)
                            <tr>
                                <td><span class="ref-code">#{{ $d->donation_id }}</span></td>
                                <td>
                                    {{ $d->donation_date?->format('d M Y') }}<br>
                                    <small class="text-muted">{{ $d->donation_date?->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $d->donor_name ?? 'Anonymous' }}</div>
                                    <small class="text-muted">{{ $d->donor_email ?? '—' }}</small>
                                </td>
                                <td>
                                    @if($d->crisis_id)
                                        <a href="#" class="fw-semibold text-decoration-none">#{{ $d->crisis_id }}</a><br>
                                        <small class="text-muted">
                                            {{ $typeLabels[$d->crisis?->crisis_type] ?? ucwords(str_replace('_',' ',$d->crisis?->crisis_type ?? '—')) }}
                                        </small>
                                    @else
                                        <small class="text-muted">—</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="method-badge">{{ strtoupper($d->payment_method ?? '—') }}</span>
                                </td>
                                <td>
                                    @if(($d->recorded_by ?? 'donor') === 'admin')
                                        <span style="background:#FEF3C7; color:#92400E; font-size:10px; font-weight:700;
                                                     padding:3px 8px; border-radius:10px; text-transform:uppercase;
                                                     letter-spacing:0.4px;"
                                              title="{{ $d->admin_note }}">
                                            <i class="bi bi-shield-check"></i> Admin
                                        </span>
                                    @else
                                        <span style="background:#DBEAFE; color:#1E40AF; font-size:10px; font-weight:700;
                                                     padding:3px 8px; border-radius:10px; text-transform:uppercase;
                                                     letter-spacing:0.4px;">
                                            <i class="bi bi-globe"></i> Donor
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end amount-cell">
                                    RM {{ number_format($d->donation_amount, 2) }}
                                </td>
                                <td class="blockchain-cell">
                                    @if($d->blockchain_reference)
                                        <i class="bi bi-shield-check text-success"></i>
                                        {{ substr($d->blockchain_reference, 0, 12) }}…
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3 no-print">
                {{ $donations->links() }}
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
