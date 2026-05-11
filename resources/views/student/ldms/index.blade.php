@extends('layouts.student')
@section('title', 'My Legacy Messages')
@section('page-title', 'Legacy Digital Messages')
@section('page-subtitle', 'Encrypted messages for your next of kin')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between mb-3">
        <p class="text-muted mb-0">Messages here are encrypted at rest. They are only released to your registered NOK once a verified death confirmation has been recorded.</p>
        <a href="{{ route('student.ldms.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Message</a>
    </div>

    <div class="content-card">
        @if($messages->isEmpty())
            <div class="text-muted text-center py-5">
                <i class="bi bi-envelope-paper fs-1 text-secondary"></i>
                <p class="mb-0 mt-2">No messages yet. Create your first message to be securely stored.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Created</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($messages as $m)
                            <tr>
                                <td>#{{ $m->ldms_id }}</td>
                                <td><span class="badge bg-secondary">{{ strtoupper($m->media_type) }}</span></td>
                                <td>{{ $m->updated_at?->format('d M Y, h:i A') }}</td>
                                <td>
                                    @if($m->is_released)
                                        <span class="badge bg-success">Released {{ $m->date_triggered?->diffForHumans() }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Encrypted &middot; Held</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(!$m->is_released)
                                        <a href="{{ route('student.ldms.edit', $m->ldms_id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form method="POST" action="{{ route('student.ldms.destroy', $m->ldms_id) }}" class="d-inline" onsubmit="return confirm('Permanently delete this message?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @else
                                        <span class="text-muted small">Locked</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $messages->links() }}
        @endif
    </div>
</div>
@endsection
