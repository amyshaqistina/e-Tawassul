@extends('layouts.admin')
@section('title', 'Students')
@section('page-title', 'Student Records')

@section('content')
<div class="container-fluid py-3">
    <div class="content-card">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Kulliyyah / Programme</th>
                        <th>Status</th>
                        <th>Last iMaalum sync</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $s)
                        <tr>
                            <td><code>{{ $s->student_id }}</code></td>
                            <td>
                                <div class="fw-semibold">{{ $s->full_name }}</div>
                                <small class="text-muted">{{ $s->email }}</small>
                            </td>
                            <td>
                                {{ $s->kulliyyah ?? '—' }}
                                <div class="small text-muted">{{ $s->programme ?? '' }}</div>
                            </td>
                            <td>
                                @if($s->status === 'deceased')
                                    <span class="badge bg-dark">Deceased</span>
                                @else
                                    <span class="badge bg-success">{{ ucfirst($s->status ?? 'active') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($s->imaalum_synced_at)
                                    {{ $s->imaalum_synced_at->diffForHumans() }}
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $students->links() }}
    </div>
</div>
@endsection
