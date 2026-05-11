@extends('layouts.nok')
@section('title', 'Message from ' . ($ldms->student?->first_name ?? 'Student'))
@section('page-title', 'Legacy Message #' . $ldms->ldms_id)

@section('content')
<div class="container-fluid py-3">
    <a href="{{ route('nok.dashboard') }}" class="btn btn-link p-0 mb-3"><i class="bi bi-arrow-left"></i> Back to dashboard</a>

    <div class="row g-3">
        <div class="col-lg-9">
            <div class="content-card">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-success fs-6"><i class="bi bi-envelope-open"></i> RELEASED</span>
                    <span class="badge bg-secondary ms-2">{{ strtoupper($ldms->media_type) }}</span>
                    <small class="text-muted ms-auto">Released {{ $ldms->date_triggered?->format('d M Y, h:i A') }}</small>
                </div>

                <h5 class="mb-1">A message from {{ $ldms->student?->full_name }}</h5>
                <p class="text-muted small mb-4">{{ $ldms->student?->student_id }}</p>

                @if($ldms->message_content)
                    <div class="ldms-message-body">
                        {!! nl2br(e($ldms->message_content)) !!}
                    </div>
                @endif

                @if($ldms->media_file_path && count((array)$ldms->media_file_path) > 0)
                    <hr>
                    <h6 class="text-uppercase text-muted small mb-3">Attached media</h6>
                    @foreach((array)$ldms->media_file_path as $path)
                        @php
                            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                            $isAudio = in_array($ext, ['mp3','wav','ogg','m4a','webm']);
                            $isVideo = in_array($ext, ['mp4','mov']);
                            $url = route('nok.ldms.show', $ldms->ldms_id);
                        @endphp
                        <div class="ldms-media-item">
                            @if($isImage)
                                <i class="bi bi-image"></i> <code>{{ basename($path) }}</code>
                                <div class="text-muted small">Image attachment (stored encrypted)</div>
                            @elseif($isAudio)
                                <i class="bi bi-mic-fill"></i> <code>{{ basename($path) }}</code>
                                <div class="text-muted small">Audio recording (encrypted)</div>
                            @elseif($isVideo)
                                <i class="bi bi-camera-video"></i> <code>{{ basename($path) }}</code>
                                <div class="text-muted small">Video recording (encrypted)</div>
                            @else
                                <i class="bi bi-file-earmark"></i> <code>{{ basename($path) }}</code>
                            @endif
                        </div>
                    @endforeach
                    <p class="small text-muted mt-2">Media files are stored encrypted. Please contact the IIUM Welfare Office to retrieve the decrypted attachments securely.</p>
                @endif
            </div>
        </div>

        <div class="col-lg-3">
            <div class="content-card">
                <h6 class="text-uppercase text-muted small">Authenticity</h6>
                <p class="small text-muted">This message was sealed by the student and only released by an administrator after a verified death confirmation.</p>
                <a href="{{ route('notifications.index') }}" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-bell"></i> View notifications
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
