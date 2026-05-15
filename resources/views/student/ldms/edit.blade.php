@extends('layouts.student')
@section('title', 'Edit Last Digital Message #' . $ldms->ldms_id)
@section('page-title', 'Edit Last Digital Message #' . $ldms->ldms_id)

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
                <h6 class="text-uppercase text-muted small mb-2">
                    <i class="bi bi-paperclip"></i> Existing attachments
                </h6>
                @php
                    $existingPaths = $ldms->media_file_path ? (array) $ldms->media_file_path : [];
                @endphp

                @if(count($existingPaths) > 0)
                    <div class="d-flex flex-column gap-2">
                        @foreach($existingPaths as $p)
                            @php
                                $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg','jpeg','png','webp','gif']);
                                $isPdf   = $ext === 'pdf';
                                $isWord  = in_array($ext, ['doc','docx']);
                                $isAudio = in_array($ext, ['mp3','wav','webm','ogg','m4a']);
                                $isVideo = in_array($ext, ['mp4','webm','mov']);
                                $icon = match(true) {
                                    $isImage => 'bi-image text-success',
                                    $isPdf   => 'bi-file-earmark-pdf text-danger',
                                    $isWord  => 'bi-file-earmark-word text-primary',
                                    $isAudio => 'bi-music-note-beamed text-info',
                                    $isVideo => 'bi-camera-video text-warning',
                                    default  => 'bi-file-earmark text-muted',
                                };
                            @endphp
                            <div class="border rounded p-2 small d-flex align-items-center gap-2">
                                <i class="bi {{ $icon }} fs-4"></i>
                                <div class="flex-grow-1 text-truncate">
                                    <div class="fw-semibold text-truncate">{{ basename($p) }}</div>
                                    <code class="text-muted small">{{ strtoupper($ext) }}</code>
                                </div>
                                <label class="form-check-label small text-danger" title="Remove on save">
                                    <input type="checkbox" name="remove_files[]" value="{{ $p }}" class="form-check-input">
                                    Delete
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted d-block mt-2">
                        Tick "Delete" then save to remove an attachment. New uploads will be added alongside the rest.
                    </small>
                @else
                    <p class="text-muted small mb-0">No existing attachments.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
