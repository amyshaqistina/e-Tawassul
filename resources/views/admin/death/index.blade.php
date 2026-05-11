@extends('layouts.admin')
@section('title', 'Death Confirmations')
@section('page-title', 'Death Confirmations')

@section('content')
<div class="container-fluid py-3">
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#pending">Pending ({{ $pending->total() }})</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#verified">Verified ({{ $verified->total() }})</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#rejected">Rejected ({{ $rejected->total() }})</a></li>
    </ul>

    <div class="tab-content">
        @foreach(['pending'=>$pending, 'verified'=>$verified, 'rejected'=>$rejected] as $key => $list)
            <div class="tab-pane fade {{ $key==='pending'?'show active':'' }}" id="{{ $key }}">
                <div class="content-card">
                    @if($list->isEmpty())
                        <p class="text-muted text-center my-4">None.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Student</th>
                                        <th>Submitted by NOK</th>
                                        <th>Triggered</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($list as $c)
                                        <tr>
                                            <td>{{ $c->confirmation_id }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $c->student?->full_name ?? '—' }}</div>
                                                <small class="text-muted">{{ $c->student_id }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $c->nextOfKin?->full_name ?? '—' }}</div>
                                                <small class="text-muted">{{ $c->nextOfKin?->relationship_to_student }}</small>
                                            </td>
                                            <td>{{ $c->date_triggered?->format('d M Y') }}<br><small class="text-muted">{{ $c->date_triggered?->diffForHumans() }}</small></td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.death.show', $c->confirmation_id) }}" class="btn btn-primary btn-sm">Review</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $list->links() }}
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
