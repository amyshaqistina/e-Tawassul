@extends('layouts.student')
@section('title', 'Edit Legacy Message #' . $ldms->ldms_id)
@section('page-title', 'Edit Legacy Message #' . $ldms->ldms_id)

@section('content')
<div class="container-fluid py-3">
    <div class="row">
        <div class="col-lg-8">
            <div class="content-card">
                @include('student.ldms._form', [
                    'action' => route('student.ldms.update', $ldms->ldms_id),
                    'method' => 'PUT',
                    'ldms'   => $ldms,
                    'submitLabel' => 'Update Message',
                ])
            </div>
        </div>
        <div class="col-lg-4">
            <div class="content-card">
                <h6 class="text-uppercase text-muted small">Existing attachments</h6>
                @if($ldms->media_file_path && count((array)$ldms->media_file_path) > 0)
                    <ul class="small">
                        @foreach((array)$ldms->media_file_path as $p)
                            <li><code>{{ basename($p) }}</code></li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted small">No existing attachments.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
