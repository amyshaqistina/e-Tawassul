@extends('layouts.student')
@section('title', 'Message #' . $ldms->ldms_id)

@php
    $isReleased = $ldms->is_released;
    $typeIcon = match($ldms->media_type) {
        'audio' => 'bi-mic-fill',
        'video' => 'bi-camera-video-fill',
        'mixed' => 'bi-files',
        default => 'bi-pencil-fill',
    };
    $typeLabel = match($ldms->media_type) {
        'audio' => 'Audio Message',
        'video' => 'Video Message',
        'mixed' => 'Mixed Content',
        default => 'Written Message',
    };
@endphp

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .ldsh-wrap {
        --bg:#f5f6fa; --card:#fff; --ink:#1a2238; --ink-soft:#5b6479; --ink-faint:#8a92a6;
        --border:#e8eaf0; --border-soft:#f0f2f7;
        --primary:#2563eb; --primary-tint:#eef3ff; --primary-dark:#1d4ed8;
        --success:#15803d; --success-tint:#e8f6ee;
        --amber:#b45309; --amber-tint:#fdf1de;
        --danger:#b91c1c; --danger-tint:#fdeaea;
        --purple:#6d28d9; --purple-tint:#f0e9fd;
        --shadow:0 1px 2px rgba(20,28,55,.04), 0 4px 16px rgba(20,28,55,.04);
        font-family:'Inter',-apple-system,sans-serif; color:var(--ink); line-height:1.55;
    }
    .ldsh-wrap *,.ldsh-wrap *::before,.ldsh-wrap *::after{box-sizing:border-box}

    .ldsh-back{display:inline-flex;align-items:center;gap:6px;color:var(--primary);text-decoration:none;font-size:14px;font-weight:500;padding:8px 12px;margin:0 0 16px -12px;border-radius:10px;transition:background .15s}
    .ldsh-back:hover{background:var(--primary-tint);color:var(--primary-dark)}

    .ldsh-card{background:#fff;border-radius:16px;box-shadow:var(--shadow);margin-bottom:18px;overflow:hidden}
    .ldsh-card-head{display:flex;align-items:center;gap:10px;padding:16px 22px;border-bottom:1px solid var(--border-soft)}
    .ldsh-card-head-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:14px;background:var(--purple-tint);color:var(--purple)}
    .ldsh-card-head-icon.green{background:var(--success-tint);color:var(--success)}
    .ldsh-card-head-icon.amber{background:var(--amber-tint);color:var(--amber)}
    .ldsh-card-head-icon.blue{background:var(--primary-tint);color:var(--primary)}
    .ldsh-card-head h3{margin:0;font-size:15px;font-weight:700;color:var(--ink)}
    .ldsh-card-body{padding:22px}

    .ldsh-pill{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:4px 10px;border-radius:999px;text-transform:uppercase;letter-spacing:.04em}
    .pill-held{background:var(--amber-tint);color:var(--amber)}
    .pill-released{background:var(--success-tint);color:var(--success)}
    .pill-type{background:var(--purple-tint);color:var(--purple)}

    .ldsh-grid{display:grid;grid-template-columns:1fr 380px;gap:18px;align-items:start}
    @media (max-width:1000px){.ldsh-grid{grid-template-columns:1fr}}

    .ldsh-hero{padding:26px 30px;position:relative}
    .ldsh-hero::before{content:"";position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--amber),#f59e0b)}
    .ldsh-hero.released::before{background:linear-gradient(90deg,var(--success),#22c55e)}
    .ldsh-hero-pills{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
    .ldsh-hero h1{font-family:'Fraunces',Georgia,serif;font-weight:600;font-size:30px;letter-spacing:-.018em;margin:0 0 10px;line-height:1.15;color:var(--ink)}
    .ldsh-hero-meta{display:flex;flex-wrap:wrap;gap:10px 20px;color:var(--ink-soft);font-size:13px}
    .ldsh-hero-meta span{display:inline-flex;align-items:center;gap:5px}
    .ldsh-hero-meta i{font-size:13px;color:var(--ink-faint)}

    .ldsh-info-row{display:flex;align-items:flex-start;padding:14px 0;border-bottom:1px solid var(--border-soft)}
    .ldsh-info-row:last-child{border-bottom:none;padding-bottom:0}
    .ldsh-info-row:first-child{padding-top:0}
    .ldsh-info-icon{width:38px;height:38px;border-radius:10px;background:var(--purple-tint);color:var(--purple);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:15px;margin-right:14px}
    .ldsh-info-icon.green{background:var(--success-tint);color:var(--success)}
    .ldsh-info-icon.blue{background:var(--primary-tint);color:var(--primary)}
    .ldsh-info-icon.amber{background:var(--amber-tint);color:var(--amber)}
    .ldsh-info-content{flex:1;min-width:0}
    .ldsh-info-label{font-size:11px;font-weight:700;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px}
    .ldsh-info-value{font-size:14px;line-height:1.5;word-break:break-word;color:var(--ink)}
    .ldsh-info-value.large{font-size:15px;font-weight:500}
    .ldsh-info-value a{color:var(--primary);text-decoration:none}
    .ldsh-info-value a:hover{text-decoration:underline}

    .ldsh-message-body{
        background:linear-gradient(135deg,#fef9e7,#fef3c7);
        border:1px solid #fde68a;
        border-radius:12px;
        padding:22px 26px;
        font-family:'Fraunces',Georgia,serif;
        font-size:17px;line-height:1.7;
        color:#1a2238;
        white-space:pre-wrap;
        position:relative;
    }
    .ldsh-message-body::before{
        content:"\201C";
        position:absolute;top:-10px;left:14px;
        font-family:'Fraunces',serif;
        font-size:60px;color:#fcd34d;
        line-height:1;
    }
    .ldsh-message-body::after{
        content:"\201D";
        position:absolute;bottom:-30px;right:14px;
        font-family:'Fraunces',serif;
        font-size:60px;color:#fcd34d;
        line-height:1;
    }

    .ldsh-attachment-row{
        display:flex;align-items:center;gap:12px;
        padding:12px 14px;
        background:var(--bg);
        border:1px solid var(--border-soft);
        border-radius:10px;
        margin-bottom:8px;
        text-decoration:none;color:var(--ink);
        transition:all .15s;
    }
    .ldsh-attachment-row:hover{background:#fff;border-color:var(--primary);color:var(--primary)}
    .ldsh-attachment-icon{width:36px;height:36px;border-radius:9px;background:var(--primary-tint);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
    .ldsh-attachment-info{flex:1;min-width:0}
    .ldsh-attachment-name{font-weight:600;font-size:13.5px}
    .ldsh-attachment-meta{font-size:11.5px;color:var(--ink-faint);margin-top:2px}

    .ldsh-timeline{padding:4px 0}
    .ldsh-timeline-item{display:flex;gap:12px;padding-bottom:18px;position:relative}
    .ldsh-timeline-item:last-child{padding-bottom:0}
    .ldsh-timeline-item:not(:last-child)::before{content:"";position:absolute;left:7px;top:24px;bottom:0;width:2px;background:var(--border)}
    .ldsh-timeline-dot{width:16px;height:16px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:4px;box-shadow:0 0 0 4px var(--primary-tint)}
    .ldsh-timeline-dot.green{background:var(--success);box-shadow:0 0 0 4px var(--success-tint)}
    .ldsh-timeline-dot.amber{background:var(--amber);box-shadow:0 0 0 4px var(--amber-tint)}
    .ldsh-timeline-dot.muted{background:#cbd5e1;box-shadow:0 0 0 4px #f1f5f9}
    .ldsh-timeline-text{flex:1;min-width:0}
    .ldsh-timeline-title{font-weight:600;font-size:14px;color:var(--ink);margin:0 0 2px}
    .ldsh-timeline-meta{font-size:12px;color:var(--ink-faint);line-height:1.4}

    .ldsh-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:9px 16px;border-radius:10px;font-weight:600;font-size:13.5px;cursor:pointer;text-decoration:none;border:1.5px solid transparent;font-family:inherit;transition:all .15s}
    .ldsh-btn-primary{background:var(--primary);color:#fff}
    .ldsh-btn-primary:hover{background:var(--primary-dark);color:#fff}
    .ldsh-btn-ghost{background:#fff;color:var(--ink);border-color:var(--border)}
    .ldsh-btn-ghost:hover{background:var(--bg)}
    .ldsh-btn-danger-ghost{background:#fff;color:var(--danger);border-color:#fecaca}
    .ldsh-btn-danger-ghost:hover{background:var(--danger-tint)}
    .ldsh-btn-block{width:100%}

    .ldsh-notice{padding:14px 16px;border-radius:12px;display:flex;gap:12px;align-items:flex-start;margin-bottom:16px;border:1px solid}
    .ldsh-notice.lock{background:var(--success-tint);border-color:#BBF7D0}
    .ldsh-notice.lock i{color:var(--success);font-size:18px;flex-shrink:0;margin-top:1px}
    .ldsh-notice.lock h4{color:var(--success);margin:0 0 4px;font-size:14px;font-weight:700}
    .ldsh-notice.lock p{margin:0;font-size:13px;color:var(--ink-soft);line-height:1.5}
    .ldsh-notice.editable{background:var(--amber-tint);border-color:#FDE68A}
    .ldsh-notice.editable i{color:var(--amber);font-size:18px;flex-shrink:0;margin-top:1px}
    .ldsh-notice.editable h4{color:var(--amber);margin:0 0 4px;font-size:14px;font-weight:700}
    .ldsh-notice.editable p{margin:0;font-size:13px;color:var(--ink-soft);line-height:1.5}
    .ldsh-notice-actions{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap}

    .ldsh-help{background:linear-gradient(135deg,var(--primary),#3b82f6);color:#fff;border-radius:16px;overflow:hidden}
    .ldsh-help-body{padding:18px 22px}
    .ldsh-help h3{margin:0 0 6px;color:#fff;font-size:15px;font-weight:700}
    .ldsh-help p{margin:0;font-size:13px;color:rgba(255,255,255,.85);line-height:1.55}
</style>
@endpush

@section('content')
<div class="ldsh-wrap">

    <a href="{{ route('student.ldms.index') }}" class="ldsh-back">
        <i class="bi bi-arrow-left"></i> Back to my messages
    </a>

    @if (session('status'))
        <div class="ldsh-notice lock" style="margin-bottom:16px">
            <i class="bi bi-check-circle-fill"></i>
            <div><h4>Success</h4><p>{{ session('status') }}</p></div>
        </div>
    @endif
    @if (session('error') || $errors->any())
        <div class="ldsh-notice editable" style="margin-bottom:16px">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <h4 style="color:var(--danger)">Action not allowed</h4>
                <p>{{ session('error') ?: $errors->first() }}</p>
            </div>
        </div>
    @endif

    {{-- HERO --}}
    <section class="ldsh-card ldsh-hero {{ $isReleased ? 'released' : '' }}">
        <div class="ldsh-hero-pills">
            @if ($isReleased)
                <span class="ldsh-pill pill-released"><i class="bi bi-send-check"></i> Released to NOK</span>
            @else
                <span class="ldsh-pill pill-held"><i class="bi bi-shield-lock-fill"></i> Held (Encrypted)</span>
            @endif
            <span class="ldsh-pill pill-type"><i class="bi {{ $typeIcon }}"></i> {{ $typeLabel }}</span>
        </div>
        <h1>Message #{{ str_pad($ldms->ldms_id, 4, '0', STR_PAD_LEFT) }}</h1>
        <div class="ldsh-hero-meta">
            <span><i class="bi bi-calendar-event"></i> Saved {{ $ldms->updated_at?->format('d M Y, h:i A') }}</span>
            @if ($ldms->date_triggered)
                <span><i class="bi bi-send-fill"></i> Triggered {{ $ldms->date_triggered->format('d M Y, h:i A') }}</span>
            @endif
        </div>
    </section>

    {{-- STATE-AWARE NOTICE --}}
    @if ($isReleased)
        <div class="ldsh-notice lock">
            <i class="bi bi-shield-fill-check"></i>
            <div>
                <h4>This message has been delivered</h4>
                <p>Your next-of-kin has received this message. It's now part of your digital legacy and can't be edited — preserving its authenticity at the moment of release.</p>
            </div>
        </div>
    @else
        <div class="ldsh-notice editable">
            <i class="bi bi-shield-lock-fill"></i>
            <div style="flex:1">
                <h4>Safely held — encrypted and waiting</h4>
                <p>This message is encrypted at rest using AES-256 and will only be released to your registered next-of-kin once a verified death confirmation is recorded. You can still edit or delete it while it's held.</p>
                <div class="ldsh-notice-actions">
                    <a href="{{ route('student.ldms.edit', $ldms->ldms_id) }}" class="ldsh-btn ldsh-btn-primary" style="padding:6px 12px;font-size:12.5px">
                        <i class="bi bi-pencil"></i> Edit Message
                    </a>
                    <form method="POST" action="{{ route('student.ldms.destroy', $ldms->ldms_id) }}" style="display:inline" onsubmit="return confirm('Permanently delete this message? This cannot be undone.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="ldsh-btn ldsh-btn-danger-ghost" style="padding:6px 12px;font-size:12.5px">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="ldsh-grid">
        {{-- LEFT --}}
        <div>
            <section class="ldsh-card">
                <div class="ldsh-card-head">
                    <div class="ldsh-card-head-icon"><i class="bi bi-envelope-paper-heart-fill"></i></div>
                    <h3>Your Message</h3>
                </div>
                <div class="ldsh-card-body">
                    @if (in_array($ldms->media_type, ['text', 'mixed']) || !empty($ldms->message_content))
                        @if ($ldms->message_content)
                            <div class="ldsh-message-body">{{ $ldms->message_content }}</div>
                        @endif
                    @endif

                    @php $attachments = (array) ($ldms->media_file_path ?? []); @endphp
                    @if (!empty($attachments))
                        <div style="margin-top:{{ $ldms->message_content ? '24px' : '0' }}">
                            <div class="ldsh-info-label" style="margin-bottom:10px">
                                <i class="bi bi-paperclip"></i> Attachments ({{ count($attachments) }})
                            </div>
                            @foreach ($attachments as $path)
                                @php
                                    $filename = basename($path);
                                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                    $fileIcon = match ($ext) {
                                        'mp3', 'wav', 'm4a', 'ogg' => 'bi-file-music',
                                        'mp4', 'mov', 'avi', 'webm' => 'bi-file-play',
                                        'jpg', 'jpeg', 'png', 'gif' => 'bi-file-image',
                                        'pdf' => 'bi-file-earmark-pdf',
                                        'doc', 'docx' => 'bi-file-earmark-word',
                                        default => 'bi-file-earmark',
                                    };
                                @endphp
                                <a href="{{ route('student.ldms.download', ['ldms' => $ldms->ldms_id, 'filename' => $filename]) }}" class="ldsh-attachment-row">
                                    <div class="ldsh-attachment-icon"><i class="bi {{ $fileIcon }}"></i></div>
                                    <div class="ldsh-attachment-info">
                                        <div class="ldsh-attachment-name">{{ $filename }}</div>
                                        <div class="ldsh-attachment-meta">Click to download · encrypted at rest</div>
                                    </div>
                                    <i class="bi bi-download" style="color:var(--ink-faint)"></i>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if (empty($ldms->message_content) && empty($attachments))
                        <p style="margin:0;text-align:center;color:var(--ink-faint);padding:30px 0;font-style:italic">
                            This message has no content yet.
                        </p>
                    @endif
                </div>
            </section>

            {{-- Recipient info --}}
            <section class="ldsh-card">
                <div class="ldsh-card-head">
                    <div class="ldsh-card-head-icon blue"><i class="bi bi-people-fill"></i></div>
                    <h3>Recipient</h3>
                </div>
                <div class="ldsh-card-body">
                    <div class="ldsh-info-row">
                        <div class="ldsh-info-icon blue"><i class="bi bi-person-heart"></i></div>
                        <div class="ldsh-info-content">
                            <div class="ldsh-info-label">Will be delivered to</div>
                            <div class="ldsh-info-value large">Your registered Next-of-Kin</div>
                            <div class="ldsh-info-value" style="color:var(--ink-soft);font-size:13px;margin-top:6px">
                                The system will deliver this message automatically once a verified death confirmation has been recorded on the blockchain.
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- RIGHT SIDEBAR --}}
        <div>
            {{-- Status Timeline --}}
            <section class="ldsh-card">
                <div class="ldsh-card-head">
                    <div class="ldsh-card-head-icon"><i class="bi bi-list-check"></i></div>
                    <h3>Timeline</h3>
                </div>
                <div class="ldsh-card-body">
                    <div class="ldsh-timeline">
                        <div class="ldsh-timeline-item">
                            <div class="ldsh-timeline-dot green"></div>
                            <div class="ldsh-timeline-text">
                                <p class="ldsh-timeline-title">Created & Encrypted</p>
                                <div class="ldsh-timeline-meta">{{ $ldms->created_at?->format('d M Y, h:i A') }}</div>
                            </div>
                        </div>

                        @if ($ldms->updated_at && $ldms->updated_at->ne($ldms->created_at))
                            <div class="ldsh-timeline-item">
                                <div class="ldsh-timeline-dot"></div>
                                <div class="ldsh-timeline-text">
                                    <p class="ldsh-timeline-title">Last Edited</p>
                                    <div class="ldsh-timeline-meta">{{ $ldms->updated_at->format('d M Y, h:i A') }}</div>
                                </div>
                            </div>
                        @endif

                        @if ($isReleased && $ldms->date_triggered)
                            <div class="ldsh-timeline-item">
                                <div class="ldsh-timeline-dot green"></div>
                                <div class="ldsh-timeline-text">
                                    <p class="ldsh-timeline-title">Released to NOK</p>
                                    <div class="ldsh-timeline-meta">{{ $ldms->date_triggered->format('d M Y, h:i A') }}</div>
                                </div>
                            </div>
                        @else
                            <div class="ldsh-timeline-item">
                                <div class="ldsh-timeline-dot amber"></div>
                                <div class="ldsh-timeline-text">
                                    <p class="ldsh-timeline-title">Held — Awaiting Trigger</p>
                                    <div class="ldsh-timeline-meta">Released only after verified death confirmation</div>
                                </div>
                            </div>
                            <div class="ldsh-timeline-item">
                                <div class="ldsh-timeline-dot muted"></div>
                                <div class="ldsh-timeline-text">
                                    <p class="ldsh-timeline-title">Delivery to NOK</p>
                                    <div class="ldsh-timeline-meta">Pending</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Security info --}}
            <section class="ldsh-help">
                <div class="ldsh-help-body">
                    <h3><i class="bi bi-shield-lock-fill"></i> Your message is safe</h3>
                    <p>Encrypted with AES-256 at rest. Released only after blockchain-verified death confirmation. Even system administrators cannot read your message while it's held.</p>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
