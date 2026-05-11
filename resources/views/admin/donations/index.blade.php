@extends('layouts.admin')
@section('title', 'Donations')
@section('page-title', 'Donations')
@section('page-subtitle', 'Total raised: RM ' . number_format($totalRaised, 2))

@section('content')
<div class="container-fluid py-3">
    <div class="content-card">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Donor</th>
                        <th>Crisis</th>
                        <th>Method</th>
                        <th class="text-end">Amount</th>
                        <th>Blockchain</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($donations as $d)
                        <tr>
                            <td>{{ $d->donation_id }}</td>
                            <td>{{ $d->donation_date?->format('d M Y, h:i A') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $d->donor_name }}</div>
                                <small class="text-muted">{{ $d->donor_email }}</small>
                            </td>
                            <td>
                                @if($d->crisis_id)
                                    <a href="{{ route('admin.crisis.index') }}">#{{ $d->crisis_id }}</a>
                                    <div class="small text-muted">{{ ucwords(str_replace('_',' ', $d->crisis?->crisis_type ?? '')) }}</div>
                                @else
                                    <span class="text-muted">General</span>
                                @endif
                            </td>
                            <td><span class="badge bg-secondary">{{ strtoupper($d->payment_method) }}</span></td>
                            <td class="text-end fw-semibold text-success">RM {{ number_format($d->donation_amount, 2) }}</td>
                            <td>
                                @if($d->blockchain_hash)
                                    <code class="small" title="{{ $d->blockchain_hash }}">{{ substr($d->blockchain_hash, 0, 10) }}…</code>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $donations->links() }}
    </div>
</div>
@endsection
