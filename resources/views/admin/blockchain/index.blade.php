@extends('layouts.admin')
@section('title', 'Blockchain Audit')
@section('page-title', 'Blockchain Audit Log')

@section('content')
<div class="container-fluid py-3">
    <div class="row g-3 mb-3">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-primary">
                <div class="stat-card-icon"><i class="bi bi-link-45deg"></i></div>
                <div>
                    <div class="stat-card-value">{{ $stats['total'] }}</div>
                    <div class="stat-card-label">Total records</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-success">
                <div class="stat-card-icon"><i class="bi bi-cloud-check"></i></div>
                <div>
                    <div class="stat-card-value">{{ $stats['quorum'] }}</div>
                    <div class="stat-card-label">Quorum-anchored</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-warning">
                <div class="stat-card-icon"><i class="bi bi-cpu"></i></div>
                <div>
                    <div class="stat-card-value">{{ $stats['simulation'] }}</div>
                    <div class="stat-card-label">Simulation mode</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 d-flex align-items-stretch">
            <a href="{{ route('admin.pdf.audit') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center">
                <i class="bi bi-file-earmark-pdf me-2 fs-3"></i>
                <span>Export Audit Log<br><small>as PDF</small></span>
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="content-card">
                <h5 class="mb-3">Audit log (most recent)</h5>
                <div class="table-responsive">
                    <table class="table align-middle table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Time</th>
                                <th>Event</th>
                                <th>Reference</th>
                                <th>Mode</th>
                                <th>Hash</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $r)
                                <tr>
                                    <td>{{ $r->blockchain_id }}</td>
                                    <td><small>{{ $r->timestamp?->format('d M Y, H:i:s') }}</small></td>
                                    <td><span class="badge bg-primary">{{ $r->data_from }}</span></td>
                                    <td>
                                        @if($r->reference_table)
                                            <small class="text-muted">{{ $r->reference_table }}#{{ $r->reference_id }}</small>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if($r->mode === 'quorum')
                                            <span class="badge bg-success">Quorum</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Simulation</span>
                                        @endif
                                    </td>
                                    <td><code class="small" title="{{ $r->stored_data }}">{{ substr($r->stored_data, 0, 10) }}…{{ substr($r->stored_data, -6) }}</code></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $records->links() }}
            </div>
        </div>

        <div class="col-lg-4">
            <div class="content-card">
                <h5 class="mb-3"><i class="bi bi-search"></i> Verify a hash</h5>
                <p class="small text-muted">Paste a SHA-256 hash to check whether it exists in the audit chain.</p>
                <form method="POST" action="{{ route('admin.blockchain.verify') }}">
                    @csrf
                    <input type="text" name="hash" class="form-control mb-2" placeholder="64-character SHA-256 hash" value="{{ old('hash') }}" required>
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
@endsection
