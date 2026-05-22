@extends('layouts.student')
@section('title', 'Message #' . $ldms->ldms_id)
@section('page-title', 'Your Last Digital Message')
@section('page-subtitle', 'Saved on ' . $ldms->updated_at?->format('d M Y, h:i A'))

@push('styles')
<style>
    .ldms-show-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 28px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        margin-bottom: 16px;
    }
    .ldms-show-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 18px;
        border-bottom: 1px solid #f1f5f9;
        margin-bottom: 22px;
        flex-wrap: wrap;
    }
    .ldms-show-header-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 11px;
        background: #eff6ff;
        color: #1a56db;
        font-size: 22px;
        flex-shrink: 0;
    }
    .ldms-show-header h3 {
        margin: 0 0 2px 0;
        font-size: 17px;
        font-weight: 800;
        color: #0f172a;
    }
    .ldms-show-header p {
        margin: 0;
        font-size: 12.5px;
        color: #64748b;
    }
    .ldms-show-header .status-pill {
        margin-left: auto;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .status-pill.held { background: #fef3c7; color: #92400e; }
    .status-pill.released { background: #d1fae5; color: #065f46; }

    .ldms-section-label {
        text-transform: uppercase;
        font-size: 11px;
        font-weight: 700;
        color: #94a3b8;
        letter-spacing: 0.5px;
        margin: 0 0 8px 0;
    }
    .ldms-message-body {
        background: #f8faff;
        border-left: 3px solid #1a56db;
        border-radius: 0 8px 8px 0;
        padding: 18px 20px;
        font-size: 14.5px;
        line-height: 1.7;
        color: #0f172a;
        white-space: pre-wrap;
        font-family: Georgia, 'Times New Roman', serif;
    }
    .ldms-message-empty {
        font-size: 13px;
        color: #94a3b8;
        font-style: italic;
        padding: 8px 0;
    }

    .ldms-attachment-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
    }
    .ldms-attachment {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8faff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px;
        text-decoration: none;
        color: inherit;
        transition: all 0.15s;
    }
    .ldms-attachment:hover {
        background: #eff6ff;
        border-color: #93c5fd;
        color: inherit;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(26,86,219,0.08);
    }
    .ldms-attachment-icon {
        font-size: 26px;
        color: #1a56db;
        flex-shrink: 0;
    }
    .ldms-attachment-info {
        flex: 1;
        min-width: 0;
    }
    .ldms-attachment-name {
        font-size: 12.5px;
        font-weight: 600;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .ldms-attachment-hint {
        font-size: 10.5px;
        color: #64748b;
        margin-top: 2px;
    }

    .ldms-action-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid #f1f5f9;
        flex-wrap: wrap;
    }
    .ldms-back-link {
        color: #1a56db;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .ldms-back-link:hover { text-decoration: underline; }
    .ldms-btn-edit {
        background: #1a56db;
        color: #fff;
        border: none;
        padding: 10px 22px;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .ldms-btn-edit:hover {
        background: #1245b8;
        color: #fff;
    }

    .ldms-released-warning {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-radius: 10px;
        padding: 12px 14px;
        margin-top: 16px;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .ldms-released-warning i {
        color: #059669;
        font-size: 16px;
        margin-top: 1px;
        flex-shrink: 0;
    }
    .ldms-released-warning p {
        margin: 0;
        font-size: 12.5px;
        color: #065f46;
        line-height: 1.5;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <a href="{{ route('student.ldms.index') }}" class="ldms-back-link mb-3 d-inline-flex">
        <i class="bi bi-arrow-left"></i> Back to all messages
    </a>

    <div class="ldms-show-card">

        {{-- Header --}}
        <div class="ldms-show-header">
            @php
                $typeIcon = match($ldms->media_type) {
                    'text'     => 'bi-pencil-square',
                    'audio'    => 'bi-mic-fill',
                    'image'    => 'bi-image-fill',
                    'document' => 'bi-file-earmark-text-fill',
                    'video'    => 'bi-camera-video-fill',
                    'mixed'    => 'bi-collection-fill',
                    default    => 'bi-envelope-fill',
                };
                $typeLabel = match($ldms->media_type) {
                    'text'     => 'Written Letter',
                    'audio'    => 'Voice Recording',
                    'image'    => 'Photos',
                    'document' => 'Document',
                    'video'    => 'Video',
                    'mixed'    => 'Mixed Media',
                    default    => 'Message',
                };
            @endphp
            <div class="ldms-show-header-icon">
                <i class="bi {{ $typeIcon }}"></i>
            </div>
            <div>
                <h3>{{ $typeLabel }} #{{ $ldms->ldms_id }}</h3>
                <p><i class="bi bi-shield-lock-fill"></i> Encrypted with AES-256 at rest</p>
            </div>
            <span class="status-pill {{ $ldms->is_released ? 'released' : 'held' }}">
                @if($ldms->is_released)
                    <i class="bi bi-unlock-fill"></i> Released
                @else
                    <i class="bi bi-shield-lock-fill"></i> Held & Encrypted
                @endif
            </span>
        </div>

        {{-- Written message --}}
        @if(in_array($ldms->media_type, ['text', 'mixed']) || !empty($ldms->message_content))
            <p class="ldms-section-label">Your message</p>
            @if($ldms->message_content)
                <div class="ldms-message-body">{{ $ldms->message_content }}</div>
            @else
                <div class="ldms-message-empty">No written message saved.</div>
            @endif
        @endif

        {{-- Attachments --}}
        @php $attachments = (array) ($ldms->media_file_path ?? []); @endphp
        @if(count($attachments) > 0)
            <p class="ldms-section-label" style="margin-top: 22px;">
                Attachments ({{ count($attachments) }})
            </p>
            <div class="ldms-attachment-grid">
                @foreach($attachments as $path)
                    @php
                        $name = basename($path);
                        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        $icon = match(true) {
                            in_array($ext, ['jpg','jpeg','png','gif','webp']) => 'bi-file-earmark-image-fill',
                            in_array($ext, ['mp3','wav','webm','ogg'])        => 'bi-file-earmark-music-fill',
                            in_array($ext, ['mp4','mov','m4v'])               => 'bi-file-earmark-play-fill',
                            $ext === 'pdf'                                    => 'bi-file-earmark-pdf-fill',
                            in_array($ext, ['doc','docx'])                    => 'bi-file-earmark-word-fill',
                            default                                            => 'bi-file-earmark-fill',
                        };
                    @endphp
                    <a href="{{ route('student.ldms.download', [$ldms->ldms_id, $name]) }}"
                       target="_blank" rel="noopener"
                       class="ldms-attachment">
                        <i class="bi {{ $icon }} ldms-attachment-icon"></i>
                        <div class="ldms-attachment-info">
                            <div class="ldms-attachment-name">{{ $name }}</div>
                            <div class="ldms-attachment-hint">
                                <i class="bi bi-box-arrow-up-right"></i> Click to open / download
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Released state warning --}}
        @if($ldms->is_released)
            <div class="ldms-released-warning">
                <i class="bi bi-check-circle-fill"></i>
                <p>
                    <strong>This message has been released to your next of kin.</strong>
                    Released messages are locked and can no longer be edited or deleted.
                    @if($ldms->date_triggered)
                        Released {{ $ldms->date_triggered->diffForHumans() }}.
                    @endif
                </p>
            </div>
        @endif

        {{-- Action row --}}
        <div class="ldms-action-row">
            <small class="text-muted" style="font-size: 11.5px;">
                <i class="bi bi-clock-history"></i>
                Last updated {{ $ldms->updated_at?->format('d M Y, h:i A') }}
            </small>
            @if(!$ldms->is_released)
                <a href="{{ route('student.ldms.edit', $ldms->ldms_id) }}" class="ldms-btn-edit">
                    <i class="bi bi-pencil-square"></i> Edit message
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
