@extends('layouts.student')
@section('title', 'Edit Message #' . $ldms->ldms_id)

@php
    $existingPaths = $ldms->media_file_path ? (array) $ldms->media_file_path : [];
@endphp

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .lded-wrap {
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
    .lded-wrap *,.lded-wrap *::before,.lded-wrap *::after{box-sizing:border-box}

    .lded-back{display:inline-flex;align-items:center;gap:6px;color:var(--primary);text-decoration:none;font-size:14px;font-weight:500;padding:8px 12px;margin:0 0 16px -12px;border-radius:10px}
    .lded-back:hover{background:var(--primary-tint);color:var(--primary-dark)}

    .lded-card{background:#fff;border-radius:16px;box-shadow:var(--shadow);margin-bottom:18px;overflow:hidden}
    .lded-card-head{display:flex;align-items:center;gap:10px;padding:16px 22px;border-bottom:1px solid var(--border-soft)}
    .lded-card-head-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:14px;background:var(--purple-tint);color:var(--purple)}
    .lded-card-head h3{margin:0;font-size:15px;font-weight:700;color:var(--ink)}
    .lded-card-body{padding:22px}

    .lded-hero{padding:26px 30px;position:relative}
    .lded-hero::before{content:"";position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--purple),#8b5cf6)}
    .lded-hero h1{font-family:'Inter',Georgia,serif;font-weight:600;font-size:28px;letter-spacing:-.018em;margin:0 0 6px;color:var(--ink)}
    .lded-hero p{color:var(--ink-soft);font-size:14px;margin:0;line-height:1.55}

    .lded-pill{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:4px 10px;border-radius:999px;text-transform:uppercase;letter-spacing:.04em;margin-bottom:12px;background:var(--purple-tint);color:var(--purple)}

    .lded-info-banner{
        background:linear-gradient(135deg,#eff6ff,#dbeafe);
        border:1px solid #bfdbfe;
        border-radius:12px;
        padding:14px 18px;
        margin-bottom:18px;
        display:flex;gap:12px;align-items:flex-start;
    }
    .lded-info-banner i{color:#2563eb;font-size:18px;flex-shrink:0;margin-top:1px}
    .lded-info-banner p{margin:0;font-size:13px;color:var(--ink);line-height:1.55}
    .lded-info-banner strong{color:#1d4ed8}

    .lded-grid{display:grid;grid-template-columns:1fr 320px;gap:18px;align-items:start}
    @media (max-width:1000px){.lded-grid{grid-template-columns:1fr}}

    .lded-attachment{
        background:var(--bg);
        border:1px solid var(--border-soft);
        border-radius:10px;padding:12px 14px;
        margin-bottom:8px;
        display:flex;align-items:center;gap:10px;
    }
    .lded-attachment-icon{
        width:32px;height:32px;border-radius:8px;
        display:flex;align-items:center;justify-content:center;
        flex-shrink:0;font-size:16px;
    }
    .lded-attachment-icon.image{background:#dcfce7;color:#15803d}
    .lded-attachment-icon.pdf{background:#fee2e2;color:#b91c1c}
    .lded-attachment-icon.word{background:#dbeafe;color:#1d4ed8}
    .lded-attachment-icon.audio{background:#cffafe;color:#0e7490}
    .lded-attachment-icon.video{background:#fef3c7;color:#b45309}
    .lded-attachment-icon.file{background:#f1f5f9;color:#64748b}
    .lded-attachment-info{flex:1;min-width:0}
    .lded-attachment-name{font-weight:600;font-size:13px;color:var(--ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .lded-attachment-ext{font-size:11px;color:var(--ink-faint);font-family:ui-monospace,monospace;margin-top:2px}
    .lded-delete-checkbox{
        display:flex;align-items:center;gap:5px;
        font-size:11.5px;color:var(--danger);
        cursor:pointer;
        padding:4px 8px;
        border-radius:6px;
        flex-shrink:0;
    }
    .lded-delete-checkbox:hover{background:var(--danger-tint)}
    .lded-delete-checkbox input{cursor:pointer;accent-color:var(--danger)}

    .lded-no-attachments{
        text-align:center;padding:20px;
        color:var(--ink-faint);font-size:13px;
        background:var(--bg);border-radius:10px;
        border:1px dashed var(--border);
    }
    .lded-no-attachments i{font-size:24px;display:block;margin-bottom:6px;color:var(--ink-faint)}

    .lded-help-note{
        font-size:12px;color:var(--ink-soft);
        margin:10px 0 0;line-height:1.5;
        padding:10px 12px;background:var(--amber-tint);
        border:1px solid #FDE68A;border-radius:8px;
    }
    .lded-help-note i{color:var(--amber);margin-right:4px}
</style>
@endpush

@section('content')
<div class="lded-wrap">

    <a href="{{ route('student.ldms.show', $ldms->ldms_id) }}" class="lded-back">
        <i class="bi bi-arrow-left"></i> Back to message
    </a>

    <section class="lded-card lded-hero">
        <span class="lded-pill"><i class="bi bi-pencil-fill"></i> Editing</span>
        <h1>Edit Message #{{ str_pad($ldms->ldms_id, 4, '0', STR_PAD_LEFT) }}</h1>
        <p>Make changes to your message. It stays encrypted and held until released to your next-of-kin.</p>
    </section>

    <div class="lded-info-banner">
        <i class="bi bi-shield-fill-check"></i>
        <p><strong>Your message stays private.</strong> All changes are re-encrypted with AES-256 on save. Existing attachments are kept unless you mark them for deletion.</p>
    </div>

    <div class="lded-grid">
        {{-- LEFT: original _form partial preserved (camera, mic, gallery upload, all media types) --}}
        <div>
            <section class="lded-card">
                <div class="lded-card-head">
                    <div class="lded-card-head-icon"><i class="bi bi-pencil-square"></i></div>
                    <h3>Message Details</h3>
                </div>
                <div class="lded-card-body">
                    @include('student.ldms._form', [
                        'action' => route('student.ldms.update', $ldms->ldms_id),
                        'method' => 'PUT',
                        'ldms'   => $ldms,
                        'submitLabel' => 'Save Changes',
                    ])
                </div>
            </section>
        </div>

        {{-- RIGHT SIDEBAR: existing attachments with delete checkboxes --}}
        <div>
            <section class="lded-card">
                <div class="lded-card-head">
                    <div class="lded-card-head-icon"><i class="bi bi-paperclip"></i></div>
                    <h3>Existing Attachments</h3>
                </div>
                <div class="lded-card-body">
                    @if (count($existingPaths) > 0)
                        @foreach ($existingPaths as $p)
                            @php
                                $filename = basename($p);
                                $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                                $iconType = match (true) {
                                    in_array($ext, ['jpg','jpeg','png','webp','gif']) => 'image',
                                    $ext === 'pdf' => 'pdf',
                                    in_array($ext, ['doc','docx']) => 'word',
                                    in_array($ext, ['mp3','wav','webm','ogg','m4a']) => 'audio',
                                    in_array($ext, ['mp4','mov']) => 'video',
                                    default => 'file',
                                };
                                $iconClass = match ($iconType) {
                                    'image' => 'bi-file-image-fill',
                                    'pdf'   => 'bi-file-pdf-fill',
                                    'word'  => 'bi-file-word-fill',
                                    'audio' => 'bi-file-music-fill',
                                    'video' => 'bi-file-play-fill',
                                    default => 'bi-file-earmark-fill',
                                };
                            @endphp
                            <div class="lded-attachment">
                                <div class="lded-attachment-icon {{ $iconType }}">
                                    <i class="bi {{ $iconClass }}"></i>
                                </div>
                                <div class="lded-attachment-info">
                                    <div class="lded-attachment-name" title="{{ $filename }}">{{ $filename }}</div>
                                    <div class="lded-attachment-ext">{{ strtoupper($ext) }}</div>
                                </div>
                                {{-- These checkboxes sit OUTSIDE the _form partial's form element,
                                     so we use a small JS bridge below to inject remove_files[] hidden
                                     inputs into the form on submit. This preserves the original
                                     backend behavior without modifying the _form partial. --}}
                                <label class="lded-delete-checkbox" title="Mark for deletion">
                                    <input type="checkbox" class="lded-remove-file-cb" data-path="{{ $p }}">
                                    Delete
                                </label>
                            </div>
                        @endforeach

                        <p class="lded-help-note">
                            <i class="bi bi-info-circle-fill"></i>
                            Tick "Delete" then save the form to remove attachments. New uploads will be added alongside.
                        </p>
                    @else
                        <div class="lded-no-attachments">
                            <i class="bi bi-paperclip"></i>
                            No existing attachments
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>

<script>
    // Bridge "Delete" checkboxes (which live OUTSIDE the form partial) into
    // the actual <form> by injecting hidden inputs on submit. This keeps the
    // backend behavior identical (`remove_files[]`) without modifying _form.
    document.addEventListener('DOMContentLoaded', () => {
        const ldmsForm = document.querySelector('form[action*="/student/ldms/"]');
        if (!ldmsForm) return;

        ldmsForm.addEventListener('submit', () => {
            ldmsForm.querySelectorAll('input[data-injected-remove]').forEach(el => el.remove());
            document.querySelectorAll('.lded-remove-file-cb:checked').forEach(cb => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'remove_files[]';
                hidden.value = cb.dataset.path;
                hidden.setAttribute('data-injected-remove', '1');
                ldmsForm.appendChild(hidden);
            });
        });
    });
</script>
@endsection
