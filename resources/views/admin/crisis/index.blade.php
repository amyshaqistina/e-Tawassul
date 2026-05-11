@extends('layouts.admin')
@section('title', 'Crisis Reports')
@section('page-title', 'Crisis Reports')

@section('content')
<div class="container-fluid py-3">
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><a class="nav-link {{ $tab==='pending'?'active':'' }}" href="?tab=pending#pending">Pending ({{ $pending->total() }})</a></li>
        <li class="nav-item"><a class="nav-link {{ $tab==='verified'?'active':'' }}" href="?tab=verified#verified">Verified ({{ $verified->total() }})</a></li>
        <li class="nav-item"><a class="nav-link {{ $tab==='rejected'?'active':'' }}" href="?tab=rejected#rejected">Rejected ({{ $rejected->total() }})</a></li>
    </ul>

    <div class="content-card">
        @php $collection = $$tab; @endphp
        @if($collection->isEmpty())
            <p class="text-muted text-center my-5">No reports in this tab.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Crisis Type</th>
                            <th>Impact</th>
                            <th>Submitted</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($collection as $r)
                            <tr>
                                <td>{{ $r->report_id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $r->student?->full_name ?? '—' }}</div>
                                    <small class="text-muted">{{ $r->student_id }}</small>
                                </td>
                                <td>{{ ucwords(str_replace('_',' ', $r->crisis?->crisis_type ?? '')) }}</td>
                                <td>@if($r->crisis)<x-priority-badge :level="$r->crisis->impact_level" />@endif</td>
                                <td>{{ $r->date_reported?->format('d M Y') }}<br><small class="text-muted">{{ $r->date_reported?->diffForHumans() }}</small></td>
                                <td class="text-end">
                                    <a href="{{ route('admin.crisis.show', $r->report_id) }}" class="btn btn-primary btn-sm">Review</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $collection->links() }}
        @endif
    </div>
</div>
@endsection
