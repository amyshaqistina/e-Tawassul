@extends('layouts.student')
@section('title', 'My Reports')

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .mrep-wrap {
        --bg:#f5f6fa; --card:#fff; --ink:#1a2238; --ink-soft:#5b6479; --ink-faint:#8a92a6;
        --border:#e8eaf0; --border-soft:#f0f2f7;
        --primary:#2563eb; --primary-tint:#eef3ff; --primary-dark:#1d4ed8;
        --success:#15803d; --success-tint:#e8f6ee;
        --amber:#b45309; --amber-tint:#fdf1de;
        --danger:#b91c1c; --danger-tint:#fdeaea;
        --shadow:0 1px 2px rgba(20,28,55,.04), 0 4px 16px rgba(20,28,55,.04);
        font-family:'Inter',-apple-system,sans-serif; color:var(--ink); line-height:1.55;
    }
    .mrep-wrap *,.mrep-wrap *::before,.mrep-wrap *::after{box-sizing:border-box}

    .mrep-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;gap:14px;flex-wrap:wrap}
    .mrep-header h1{font-family:'Fraunces',serif;font-weight:600;font-size:28px;margin:0;letter-spacing:-.015em;color:var(--ink)}
    .mrep-header p{color:var(--ink-soft);font-size:14px;margin:4px 0 0}

    .mrep-tabs{display:flex;gap:6px;margin-bottom:16px;background:#fff;padding:6px;border-radius:12px;border:1px solid var(--border-soft);box-shadow:var(--shadow);width:fit-content;max-width:100%;overflow-x:auto;flex-wrap:wrap}
    .mrep-tab{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;font-weight:600;font-size:13px;color:var(--ink-soft);text-decoration:none;cursor:pointer;transition:all .15s;white-space:nowrap;border:none;background:transparent;font-family:inherit}
    .mrep-tab:hover{background:var(--bg);color:var(--ink)}
    .mrep-tab.active{background:var(--primary);color:#fff}
    .mrep-tab-count{background:rgba(255,255,255,.25);padding:1px 7px;border-radius:99px;font-size:11px;font-weight:700}
    .mrep-tab:not(.active) .mrep-tab-count{background:var(--bg);color:var(--ink-faint)}

    .mrep-card{background:#fff;border-radius:16px;box-shadow:var(--shadow);overflow:hidden}

    .mrep-table{width:100%;border-collapse:separate;border-spacing:0}
    .mrep-table th{text-align:left;font-size:11px;font-weight:700;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.06em;padding:14px 18px;border-bottom:1px solid var(--border-soft);background:var(--bg)}
    .mrep-table td{padding:16px 18px;font-size:13.5px;border-bottom:1px solid var(--border-soft);vertical-align:middle;color:var(--ink)}
    .mrep-table tr:last-child td{border-bottom:none}
    .mrep-table tr:hover td{background:#fafbfd}

    .mrep-id{color:var(--ink-faint);font-family:ui-monospace,monospace;font-size:13px}

    .mrep-type-name{font-weight:600;font-size:14px;color:var(--ink)}
    .mrep-type-sub{font-size:12px;color:var(--ink-faint);margin-top:2px}

    .mrep-pill{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:4px 10px;border-radius:999px;text-transform:uppercase;letter-spacing:.04em}
    .mrep-pill-verified{background:var(--success-tint);color:var(--success)}
    .mrep-pill-pending {background:var(--amber-tint);color:var(--amber)}
    .mrep-pill-rejected{background:var(--danger-tint);color:var(--danger)}

    .mrep-actions{display:flex;gap:6px;justify-content:flex-end;flex-wrap:nowrap}

    .mrep-btn{display:inline-flex;align-items:center;justify-content:center;gap:5px;padding:7px 12px;border-radius:8px;font-weight:600;font-size:12.5px;cursor:pointer;text-decoration:none;border:1.5px solid transparent;font-family:'Inter',sans-serif;transition:all .15s;white-space:nowrap}
    .mrep-btn-primary{background:var(--primary);color:#fff}
    .mrep-btn-primary:hover{background:var(--primary-dark);color:#fff}
    .mrep-btn-ghost{background:#fff;color:var(--ink);border-color:var(--border)}
    .mrep-btn-ghost:hover{background:var(--bg);color:var(--ink)}
    .mrep-btn-disabled{background:#f1f5f9;color:var(--ink-faint);cursor:not-allowed;border-color:var(--border-soft)}

    /* Fixed widths for consistent right-alignment regardless of label */
    .mrep-btn-view{min-width:74px}
    .mrep-btn-action{min-width:138px}

    .mrep-btn-cta{background:var(--primary);color:#fff;padding:10px 18px;border-radius:10px;font-weight:600;font-size:14px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;border:none}
    .mrep-btn-cta:hover{background:var(--primary-dark);color:#fff}

    .mrep-empty{padding:60px 24px;text-align:center;color:var(--ink-soft)}
    .mrep-empty i{font-size:48px;color:var(--ink-faint);margin-bottom:14px;display:block}
    .mrep-empty h3{font-family:'Fraunces',serif;font-size:20px;color:var(--ink);margin:0 0 6px;font-weight:600}
    .mrep-empty p{margin:0 0 18px;font-size:14px}

    @media (max-width:768px){
        .mrep-table thead{display:none}
        .mrep-table tr{display:block;border-bottom:1px solid var(--border-soft);padding:14px 0}
        .mrep-table td{display:block;padding:6px 18px;border-bottom:none}
        .mrep-table td::before{content:attr(data-label);display:block;font-size:11px;font-weight:700;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px}
        .mrep-actions{justify-content:flex-start;margin-top:6px}
    }
</style>
@endpush

@section('content')
<div class="mrep-wrap">

    <div class="mrep-header">
        <div>
            <h1>My Reports</h1>
            <p>All crisis reports you've submitted ({{ $counts['all'] }} total)</p>
        </div>
        <a href="{{ route('student.crisis.create') }}" class="mrep-btn-cta">
            <i class="bi bi-plus-circle-fill"></i> Submit New Report
        </a>
    </div>

    @if (session('status'))
        <div style="background:var(--success-tint);border:1px solid #BBF7D0;border-radius:12px;padding:14px 16px;margin-bottom:16px;display:flex;gap:10px;align-items:flex-start">
            <i class="bi bi-check-circle-fill" style="color:var(--success);font-size:18px"></i>
            <p style="margin:0;font-size:13.5px;color:var(--ink)">{{ session('status') }}</p>
        </div>
    @endif

    {{-- Filter tabs --}}
    <div class="mrep-tabs">
        <a href="{{ route('student.reports.index', ['status' => 'all']) }}"
           class="mrep-tab {{ $filter === 'all' ? 'active' : '' }}">
            All <span class="mrep-tab-count">{{ $counts['all'] }}</span>
        </a>
        <a href="{{ route('student.reports.index', ['status' => 'pending']) }}"
           class="mrep-tab {{ $filter === 'pending' ? 'active' : '' }}">
            <i class="bi bi-hourglass-split"></i> Pending <span class="mrep-tab-count">{{ $counts['pending'] }}</span>
        </a>
        <a href="{{ route('student.reports.index', ['status' => 'verified']) }}"
           class="mrep-tab {{ $filter === 'verified' ? 'active' : '' }}">
            <i class="bi bi-shield-check"></i> Verified <span class="mrep-tab-count">{{ $counts['verified'] }}</span>
        </a>
        <a href="{{ route('student.reports.index', ['status' => 'rejected']) }}"
           class="mrep-tab {{ $filter === 'rejected' ? 'active' : '' }}">
            <i class="bi bi-x-circle"></i> Rejected <span class="mrep-tab-count">{{ $counts['rejected'] }}</span>
        </a>
    </div>

    <section class="mrep-card">
        @if ($reports->count() === 0)
            <div class="mrep-empty">
                <i class="bi bi-inbox"></i>
                <h3>No reports here yet</h3>
                <p>
                    @if ($filter === 'all')
                        You haven't submitted any crisis reports yet. Use the <strong>Submit New Report</strong> button above to get started.
                    @else
                        No {{ $filter }} reports to show.
                    @endif
                </p>
            </div>
        @else
            <table class="mrep-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Last Updated</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $rep)
                        @php
                            $rcrisis = $rep->crisis;
                            $type = $rcrisis ? ucwords(str_replace('_', ' ', $rcrisis->crisis_type)) : 'Crisis';
                            $sub = $rcrisis?->sub_category ?? '';
                        @endphp
                        <tr>
                            <td data-label="ID" class="mrep-id">#{{ str_pad($rep->report_id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td data-label="Type">
                                <div class="mrep-type-name">{{ $type }}</div>
                                @if ($sub)<div class="mrep-type-sub">{{ $sub }}</div>@endif
                            </td>
                            <td data-label="Last Updated">{{ ($rep->verified_at ?? $rep->date_reported)?->diffForHumans() }}</td>
                            <td data-label="Status">
                                @if ($rep->report_status === 'verified')
                                    <span class="mrep-pill mrep-pill-verified"><i class="bi bi-shield-check"></i> Verified</span>
                                @elseif ($rep->report_status === 'pending')
                                    <span class="mrep-pill mrep-pill-pending"><i class="bi bi-hourglass-split"></i> Pending</span>
                                @else
                                    <span class="mrep-pill mrep-pill-rejected"><i class="bi bi-x-circle"></i> Rejected</span>
                                @endif
                            </td>
                            <td data-label="Actions">
                                <div class="mrep-actions">
                                    <a href="{{ route('student.crisis.show', $rep->report_id) }}" class="mrep-btn mrep-btn-ghost mrep-btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    @if ($rep->report_status === 'pending')
                                        <a href="{{ route('student.crisis.edit', $rep->report_id) }}" class="mrep-btn mrep-btn-primary mrep-btn-action">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                    @elseif ($rep->report_status === 'rejected')
                                        <a href="{{ route('student.crisis.edit', $rep->report_id) }}" class="mrep-btn mrep-btn-primary mrep-btn-action">
                                            <i class="bi bi-arrow-clockwise"></i> Edit & Resubmit
                                        </a>
                                    @else
                                        <span class="mrep-btn mrep-btn-disabled mrep-btn-action" title="Verified reports are locked by blockchain">
                                            <i class="bi bi-lock-fill"></i> Locked
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    @if ($reports->hasPages())
        <div style="margin-top:16px;display:flex;justify-content:center">{{ $reports->links() }}</div>
    @endif
</div>
@endsection
