@extends('layouts.nok')
@section('title', 'Update Death Confirmation #' . $confirmation->confirmation_id)
@section('page-title', 'Update Death Confirmation')

@php
    $isRejected = $confirmation->status === 'rejected';
@endphp

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .dced-wrap {
        --bg:#f5f6fa; --card:#fff; --ink:#1a2238; --ink-soft:#5b6479; --ink-faint:#8a92a6;
        --border:#e8eaf0; --border-soft:#f0f2f7;
        --primary:#2563eb; --primary-tint:#eef3ff; --primary-dark:#1d4ed8;
        --success:#15803d; --success-tint:#e8f6ee;
        --amber:#b45309; --amber-tint:#fdf1de;
        --danger:#b91c1c; --danger-tint:#fdeaea;
        --shadow:0 1px 2px rgba(20,28,55,.04), 0 4px 16px rgba(20,28,55,.04);
        font-family:'Inter',-apple-system,sans-serif; color:var(--ink); line-height:1.55;
    }
    .dced-wrap *,.dced-wrap *::before,.dced-wrap *::after{box-sizing:border-box}

    .dced-back{display:inline-flex;align-items:center;gap:6px;color:var(--primary);text-decoration:none;font-size:14px;font-weight:500;padding:8px 12px;margin:0 0 16px -12px;border-radius:10px}
    .dced-back:hover{background:var(--primary-tint)}

    .dced-card{background:#fff;border-radius:16px;box-shadow:var(--shadow);margin-bottom:18px;overflow:hidden}
    .dced-card-head{display:flex;align-items:center;gap:10px;padding:16px 22px;border-bottom:1px solid var(--border-soft)}
    .dced-card-head-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:14px;background:var(--primary-tint);color:var(--primary)}
    .dced-card-head h3{margin:0;font-size:15px;font-weight:700}
    .dced-card-body{padding:22px}

    .dced-hero{padding:26px 30px;position:relative}
    .dced-hero::before{content:"";position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--amber),#f59e0b)}
    .dced-hero.rejected::before{background:linear-gradient(90deg,var(--danger),#ef4444)}
    .dced-hero h1{font-family:'Fraunces',Georgia,serif;font-weight:600;font-size:26px;letter-spacing:-.018em;margin:0 0 6px}
    .dced-hero p{color:var(--ink-soft);font-size:14px;margin:0;line-height:1.55}

    .dced-pill{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:4px 10px;border-radius:999px;text-transform:uppercase;letter-spacing:.04em;margin-bottom:12px}
    .pill-rejected{background:var(--danger-tint);color:var(--danger)}
    .pill-pending{background:var(--amber-tint);color:var(--amber)}

    .dced-reject{background:var(--danger-tint);border:1px solid #fecaca;border-radius:12px;padding:14px 16px;margin-bottom:16px}
    .dced-reject h4{color:var(--danger);font-size:14px;font-weight:700;margin:0 0 8px;display:flex;align-items:center;gap:6px}
    .dced-reject .reason{background:#fff;border-radius:8px;padding:12px 14px;font-size:13px;line-height:1.55;color:var(--ink);border-left:3px solid var(--danger)}
    .dced-reject .reason strong{color:var(--danger)}

    .dced-form-group{margin-bottom:18px}
    .dced-form-label{display:block;font-weight:600;font-size:13px;margin-bottom:6px}
    .dced-form-input,.dced-form-textarea{width:100%;background:#fff;border:1.5px solid var(--border);border-radius:10px;padding:10px 14px;font-size:14px;color:var(--ink);font-family:inherit;transition:all .15s}
    .dced-form-input:focus,.dced-form-textarea:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 4px rgba(37,99,235,.1)}
    .dced-form-textarea{resize:vertical;min-height:110px;line-height:1.5}
    .dced-form-hint{font-size:11.5px;color:var(--ink-faint);margin:4px 0 0}

    .dced-current-file{background:var(--bg);border:1px solid var(--border-soft);border-radius:10px;padding:14px;margin-bottom:10px;display:flex;align-items:center;gap:10px}
    .dced-current-file i{font-size:24px;color:var(--ink-soft)}

    .dced-file-drop{padding:18px;background:var(--bg);border:2px dashed var(--border);border-radius:10px;text-align:center;cursor:pointer;transition:all .15s;display:block;color:inherit;text-decoration:none}
    .dced-file-drop:hover{border-color:var(--primary);background:var(--primary-tint)}
    .dced-file-drop i{font-size:28px;color:var(--ink-faint);display:block;margin-bottom:6px}
    .dced-file-drop p{margin:0;font-size:13px;color:var(--ink-soft)}

    .dced-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:10px 18px;border-radius:10px;font-weight:600;font-size:13.5px;cursor:pointer;text-decoration:none;border:1.5px solid transparent;font-family:inherit;transition:all .15s}
    .dced-btn-primary{background:var(--primary);color:#fff}
    .dced-btn-primary:hover{background:var(--primary-dark);color:#fff}
    .dced-btn-ghost{background:#fff;color:var(--ink);border-color:var(--border)}
    .dced-btn-ghost:hover{background:var(--bg)}
    .dced-form-actions{display:flex;gap:10px;justify-content:flex-end;padding-top:14px;border-top:1px solid var(--border-soft)}

    .dced-help{background:#eef2ff;border:1px solid #c7d2fe;border-radius:10px;padding:14px;margin-bottom:18px;display:flex;gap:10px}
    .dced-help i{color:#4338CA;font-size:18px;flex-shrink:0;margin-top:1px}
    .dced-help p{margin:0;font-size:13px;color:var(--ink-soft);line-height:1.5}
</style>
@endpush

@section('content')
<div class="dced-wrap">
    <a href="{{ route('nok.death.show', $confirmation->confirmation_id) }}" class="dced-back">
        <i class="bi bi-arrow-left"></i> Back to confirmation
    </a>

    <section class="dced-card dced-hero {{ $isRejected ? 'rejected' : 'pending' }}">
        @if ($isRejected)
            <span class="dced-pill pill-rejected"><i class="bi bi-arrow-clockwise"></i> Resubmitting</span>
        @else
            <span class="dced-pill pill-pending"><i class="bi bi-pencil-fill"></i> Editing</span>
        @endif
        <h1>{{ $isRejected ? 'Update Documentation & Resubmit' : 'Update Pending Submission' }}</h1>
        <p>We're here to support you. Update the information below — once you submit, our welfare team will review again as soon as possible.</p>
    </section>

    {{-- Admin feedback reminder (rejected only) --}}
    @if ($isRejected && $confirmation->admin_comments)
        <div class="dced-reject">
            <h4><i class="bi bi-info-circle-fill"></i> Admin's feedback — please address before resubmitting</h4>
            <div class="reason"><strong>Feedback:</strong> {{ $confirmation->admin_comments }}</div>
        </div>
    @endif

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="dced-reject" style="background:#fef2f2">
            <h4><i class="bi bi-exclamation-triangle-fill"></i> Please fix the errors below</h4>
            <ul style="margin:0;padding-left:20px;font-size:13px;color:var(--danger)">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="dced-help">
        <i class="bi bi-info-circle"></i>
        <p>Upload a clear photo or scan of the official death certificate (from JPN or hospital). Accepted: PDF, JPG, PNG. Max size: 10MB.</p>
    </div>

    <form action="{{ route('nok.death.update', $confirmation->confirmation_id) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PATCH')

        <section class="dced-card">
            <div class="dced-card-head">
                <div class="dced-card-head-icon"><i class="bi bi-pencil-square"></i></div>
                <h3>Documentation</h3>
            </div>
            <div class="dced-card-body">

                @if ($confirmation->media_file_path)
                    <div class="dced-form-group">
                        <label class="dced-form-label">Currently uploaded file</label>
                        <div class="dced-current-file">
                            <i class="bi bi-file-earmark-text"></i>
                            <div style="flex:1">
                                <div style="font-weight:600;font-size:13.5px">{{ $confirmation->media_file_name ?? 'Document' }}</div>
                                @if ($confirmation->media_file_size)
                                    <small style="color:var(--ink-faint)">{{ number_format($confirmation->media_file_size / 1024, 0) }} KB</small>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <div class="dced-form-group">
                    <label class="dced-form-label">{{ $confirmation->media_file_path ? 'Replace document (optional)' : 'Upload document *' }}</label>
                    <label for="mediaFile" class="dced-file-drop">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <p>Click to choose a clearer copy (PDF, JPG, PNG)</p>
                    </label>
                    <input type="file" id="mediaFile" name="media_file" accept=".pdf,.jpg,.jpeg,.png" style="display:none" onchange="showFile(this)" @if(!$confirmation->media_file_path) required @endif>
                    <div id="selectedFile" style="margin-top:8px"></div>
                    <p class="dced-form-hint">{{ $confirmation->media_file_path ? 'Leave empty to keep current file.' : 'Required: please upload the death certificate.' }}</p>
                </div>

                <div class="dced-form-group">
                    <label class="dced-form-label">Additional Notes <span style="color:var(--ink-faint);font-weight:400">(optional)</span></label>
                    <textarea name="admin_comments" class="dced-form-textarea" maxlength="2000" placeholder="Any additional context to help the welfare team verify (e.g. hospital name, certificate reference number)">{{ old('admin_comments', $isRejected ? '' : $confirmation->admin_comments) }}</textarea>
                    @if ($isRejected)
                        <p class="dced-form-hint">The admin's feedback above will be replaced if you write here. Leave blank to preserve it.</p>
                    @endif
                </div>

                <div class="dced-form-actions">
                    <a href="{{ route('nok.death.show', $confirmation->confirmation_id) }}" class="dced-btn dced-btn-ghost">Cancel</a>
                    <button type="submit" class="dced-btn dced-btn-primary">
                        <i class="bi bi-send"></i>
                        {{ $isRejected ? 'Resubmit for Review' : 'Save Changes' }}
                    </button>
                </div>
            </div>
        </section>
    </form>
</div>

<script>
    function showFile(input) {
        const list = document.getElementById('selectedFile');
        list.innerHTML = '';
        if (input.files.length > 0) {
            const f = input.files[0];
            const span = document.createElement('span');
            span.style.cssText = 'background:var(--bg);padding:6px 10px;border-radius:8px;font-size:12px;display:inline-flex;align-items:center;gap:6px;border:1px solid var(--border-soft)';
            span.innerHTML = '<i class="bi bi-paperclip" style="color:var(--primary)"></i> ' + f.name + ' <small style="color:var(--ink-faint)">(' + (f.size/1024).toFixed(0) + ' KB)</small>';
            list.appendChild(span);
        }
    }
</script>
@endsection
