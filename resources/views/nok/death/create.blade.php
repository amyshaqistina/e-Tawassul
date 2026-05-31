@extends('layouts.nok')
@section('title', 'Submit Death Confirmation')

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .dcc-wrap {
        --bg:#f5f6fa; --card:#fff; --ink:#1a2238; --ink-soft:#5b6479; --ink-faint:#8a92a6;
        --border:#e8eaf0; --border-soft:#f0f2f7;
        --primary:#2563eb; --primary-tint:#eef3ff; --primary-dark:#1d4ed8;
        --success:#15803d; --success-tint:#e8f6ee;
        --amber:#b45309; --amber-tint:#fdf1de;
        --danger:#b91c1c; --danger-tint:#fdeaea;
        --purple:#6d28d9; --purple-tint:#f0e9fd;
        --serenity:#1a56db; --serenity-tint:#e6efff;
        --shadow:0 1px 2px rgba(20,28,55,.04), 0 4px 16px rgba(20,28,55,.04);
        font-family:'Inter',-apple-system,sans-serif; color:var(--ink); line-height:1.55;
    }
    .dcc-wrap *,.dcc-wrap *::before,.dcc-wrap *::after{box-sizing:border-box}

    .dcc-back{display:inline-flex;align-items:center;gap:6px;color:var(--primary);text-decoration:none;font-size:14px;font-weight:500;padding:8px 12px;margin:0 0 16px -12px;border-radius:10px;transition:background .15s}
    .dcc-back:hover{background:var(--primary-tint);color:var(--primary-dark)}

    .dcc-card{background:#fff;border-radius:16px;box-shadow:var(--shadow);margin-bottom:18px;overflow:hidden}
    .dcc-card-head{display:flex;align-items:center;gap:10px;padding:16px 22px;border-bottom:1px solid var(--border-soft)}
    .dcc-card-head-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:14px;background:var(--serenity-tint);color:var(--serenity)}
    .dcc-card-head h3{margin:0;font-size:15px;font-weight:700;color:var(--ink);font-family:'Inter',sans-serif}
    .dcc-card-body{padding:24px}

    /* Inna lillahi — soft amber gradient, more elegant than alert-warning */
    .dcc-condolence{
        background:linear-gradient(135deg,#fef9e7,#fef3c7);
        border:1px solid #fde68a;
        border-radius:12px;
        padding:18px 22px;
        margin-bottom:20px;
        display:flex;gap:14px;align-items:flex-start;
    }
    .dcc-condolence-text{flex:1;min-width:0}
    .dcc-condolence-arabic{
        font-weight:600;font-size:14px;
        color:#78350f;margin:0 0 4px;
    }
    .dcc-condolence-meaning{
        font-size:13.5px;color:#92400e;line-height:1.55;margin:0;
    }

    .dcc-grid{display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start}
    @media (max-width:1000px){.dcc-grid{grid-template-columns:1fr}}

    /* Student info card */
    .dcc-student-card{
        background:linear-gradient(135deg,#e6efff,#cfdcff);
        border:1px solid #b6c8ff;
        border-radius:12px;
        padding:18px 22px;
        margin-bottom:22px;
        display:flex;align-items:center;gap:14px;
    }
    .dcc-student-avatar{
        width:52px;height:52px;border-radius:50%;
        background:var(--serenity);color:#fff;
        display:flex;align-items:center;justify-content:center;
        font-weight:700;font-size:18px;
        flex-shrink:0;
        font-family:'Inter',sans-serif;
    }
    .dcc-student-info{flex:1;min-width:0}
    .dcc-student-label{font-size:11px;font-weight:700;color:var(--serenity);text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px}
    .dcc-student-name{font-size:17px;font-weight:700;color:var(--ink);line-height:1.3;font-family:'Inter',sans-serif;letter-spacing:-0.01em}
    .dcc-student-meta{font-size:12.5px;color:#1e3a8a;margin-top:3px}

    /* Form fields */
    .dcc-form-group{margin-bottom:22px}
    .dcc-form-group:last-child{margin-bottom:0}
    .dcc-form-label{display:block;font-weight:600;font-size:13.5px;margin-bottom:8px;color:var(--ink)}
    .dcc-form-label .req{color:var(--danger);margin-left:3px}
    .dcc-form-label .optional{color:var(--ink-faint);font-weight:400;font-size:12px;margin-left:6px}

    .dcc-form-input,.dcc-form-textarea{
        width:100%;background:#fff;
        border:1.5px solid var(--border);
        border-radius:10px;padding:11px 14px;
        font-size:14px;color:var(--ink);
        font-family:inherit;transition:all .15s;
    }
    .dcc-form-input:focus,.dcc-form-textarea:focus{
        outline:none;border-color:var(--serenity);box-shadow:0 0 0 4px var(--serenity-tint);
    }
    .dcc-form-textarea{resize:vertical;min-height:110px;line-height:1.55}
    .dcc-form-hint{font-size:11.5px;color:var(--ink-faint);margin:6px 0 0;display:flex;align-items:center;gap:4px}
    .dcc-form-hint i{color:var(--serenity)}

    /* File upload dropzone */
    .dcc-dropzone{
        background:#f8fafc;
        border:2px dashed #cbd5e1;
        border-radius:12px;
        padding:28px 20px;
        text-align:center;
        cursor:pointer;
        transition:all .2s;
        display:block;
    }
    .dcc-dropzone:hover,.dcc-dropzone.dragover{border-color:var(--serenity);background:var(--serenity-tint)}
    .dcc-dropzone i{font-size:32px;color:var(--serenity);display:block;margin-bottom:8px}
    .dcc-dropzone p{font-size:14px;font-weight:600;color:var(--ink);margin:0 0 4px}
    .dcc-dropzone small{font-size:12px;color:var(--ink-faint)}
    .dcc-dropzone.has-file{border-style:solid;border-color:var(--success);background:var(--success-tint)}
    .dcc-dropzone.has-file i{color:var(--success)}
    .dcc-dropzone.has-file p{color:var(--success)}

    /* Error styling */
    .dcc-form-input.error,.dcc-form-textarea.error,.dcc-dropzone.error{border-color:var(--danger);background:#fef2f2}
    .dcc-form-error{font-size:12px;color:var(--danger);margin:6px 0 0;display:flex;align-items:center;gap:4px}

    .dcc-error-list{
        background:var(--danger-tint);border:1px solid #fecaca;
        border-radius:12px;padding:14px 16px;margin-bottom:18px;
    }
    .dcc-error-list h4{color:var(--danger);font-size:14px;font-weight:700;margin:0 0 6px;display:flex;align-items:center;gap:6px}
    .dcc-error-list ul{margin:0;padding-left:20px;font-size:13px;color:var(--danger)}

    /* Action buttons */
    .dcc-form-actions{
        display:flex;gap:10px;justify-content:flex-end;
        padding-top:18px;border-top:1px solid var(--border-soft);
        margin-top:8px;flex-wrap:wrap;
    }
    .dcc-btn{
        display:inline-flex;align-items:center;justify-content:center;
        gap:7px;padding:11px 22px;border-radius:10px;
        font-weight:600;font-size:14px;cursor:pointer;
        text-decoration:none;border:1.5px solid transparent;
        font-family:inherit;transition:all .15s;
    }
    .dcc-btn-primary{background:var(--serenity);color:#fff}
    .dcc-btn-primary:hover{background:#1245b8;color:#fff;transform:translateY(-1px);box-shadow:0 4px 12px rgba(26,86,219,.25)}
    .dcc-btn-ghost{background:#fff;color:var(--ink);border-color:var(--border)}
    .dcc-btn-ghost:hover{background:var(--bg)}

    /* What happens next sidebar */
    .dcc-next-card{background:linear-gradient(180deg,#fff,#fafbff);border:1px solid var(--border-soft);border-radius:16px;overflow:hidden}
    .dcc-next-head{padding:16px 20px;border-bottom:1px solid var(--border-soft);display:flex;align-items:center;gap:9px}
    .dcc-next-head i{color:var(--serenity);font-size:18px}
    .dcc-next-head h3{margin:0;font-size:14px;font-weight:700;color:var(--ink);font-family:'Inter',sans-serif}
    .dcc-next-body{padding:6px 20px 20px}
    .dcc-step{display:flex;gap:12px;padding:14px 0;border-bottom:1px solid var(--border-soft)}
    .dcc-step:last-child{border-bottom:none}
    .dcc-step-num{
        width:24px;height:24px;border-radius:50%;
        background:var(--serenity-tint);color:var(--serenity);
        display:flex;align-items:center;justify-content:center;
        font-weight:700;font-size:12px;flex-shrink:0;
    }
    .dcc-step-text{flex:1;font-size:13px;color:var(--ink-soft);line-height:1.55}
    .dcc-step-text strong{color:var(--ink);display:block;margin-bottom:2px;font-size:13px;font-weight:600}

    /* Privacy & encryption help */
    .dcc-encryption{
        background:linear-gradient(135deg,var(--serenity),#3b82f6);
        color:#fff;border-radius:14px;padding:18px 22px;margin-top:18px;
    }
    .dcc-encryption h4{margin:0 0 6px;color:#fff;font-size:14px;font-weight:700;display:flex;align-items:center;gap:7px}
    .dcc-encryption h4 i{font-size:16px}
    .dcc-encryption p{margin:0;font-size:12.5px;color:rgba(255,255,255,.9);line-height:1.55}
</style>
@endpush

@section('content')
<div class="dcc-wrap">

    <a href="{{ route('nok.dashboard') }}" class="dcc-back">
        <i class="bi bi-arrow-left"></i> Back to dashboard
    </a>

    {{-- Condolence message --}}
    <div class="dcc-condolence">
        <div class="dcc-condolence-text">
            <p class="dcc-condolence-arabic">Inna lillahi wa inna ilayhi raji'un.</p>
            <p class="dcc-condolence-meaning">
                "Indeed, to Allah we belong, and to Him we shall return." Our administrators will review this submission with respect and care. Once verified, the student's status will be updated and any final messages (LDMS) left for you will be released.
            </p>
        </div>
    </div>

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="dcc-error-list">
            <h4><i class="bi bi-exclamation-triangle-fill"></i> Please address the issues below</h4>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="dcc-grid">

        {{-- LEFT: Form --}}
        <form method="POST" action="{{ route('nok.death.store') }}" enctype="multipart/form-data" id="deathForm">
            @csrf
            <input type="hidden" name="student_id" value="{{ $student?->student_id }}">

            {{-- Student info card (read-only) --}}
            @if ($student)
                @php
                    $initials = strtoupper(substr($student->first_name ?? '', 0, 1) . substr($student->last_name ?? '', 0, 1));
                    if (empty($initials)) {
                        $initials = strtoupper(substr($student->full_name ?? 'S', 0, 1));
                    }
                @endphp
                <div class="dcc-student-card">
                    <div class="dcc-student-avatar">{{ $initials }}</div>
                    <div class="dcc-student-info">
                        <div class="dcc-student-label">For your linked student</div>
                        <div class="dcc-student-name">{{ $student->full_name ?? 'Student' }}</div>
                        @if ($student->student_id)
                            <div class="dcc-student-meta">Matric: {{ $student->student_id }} · IIUM</div>
                        @endif
                    </div>
                </div>
            @endif

            <section class="dcc-card">
                <div class="dcc-card-head">
                    <div class="dcc-card-head-icon"><i class="bi bi-file-earmark-medical-fill"></i></div>
                    <h3>Required Documentation</h3>
                </div>
                <div class="dcc-card-body">

                    {{-- Death Certificate Upload --}}
                    <div class="dcc-form-group">
                        <label class="dcc-form-label" for="mediaFile">
                            Death Certificate / Supporting Document <span class="req">*</span>
                        </label>

                        <label for="mediaFile" class="dcc-dropzone @error('media_file') error @enderror" id="dropzoneLabel">
                            <i class="bi bi-cloud-arrow-up-fill" id="dropzoneIcon"></i>
                            <p id="dropzoneText">Click to upload or drag and drop</p>
                            <small id="dropzoneHint">PDF or image · max 10MB · stored encrypted</small>
                        </label>

                        <input
                            type="file"
                            name="media_file"
                            id="mediaFile"
                            accept=".pdf,.jpg,.jpeg,.png"
                            required
                            style="display:none"
                            onchange="showSelectedFile(this)"
                        >

                        @error('media_file')
                            <div class="dcc-form-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                        @enderror

                        <p class="dcc-form-hint">
                            <i class="bi bi-shield-lock-fill"></i>
                            Your file will be encrypted at rest. Only authorized administrators can decrypt it during verification.
                        </p>
                    </div>

                    {{-- Additional Notes --}}
                    <div class="dcc-form-group">
                        <label class="dcc-form-label" for="adminComments">
                            Additional Notes <span class="optional">(optional)</span>
                        </label>
                        <textarea
                            name="admin_comments"
                            id="adminComments"
                            class="dcc-form-textarea @error('admin_comments') error @enderror"
                            rows="4"
                            maxlength="2000"
                            placeholder="If there's anything the administrator should know — date, place, or special circumstances — share it here."
                        >{{ old('admin_comments') }}</textarea>
                        @error('admin_comments')
                            <div class="dcc-form-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                        @enderror
                        <p class="dcc-form-hint">
                            <i class="bi bi-info-circle-fill"></i>
                            Max 2,000 characters. These notes are visible only to verifying administrators.
                        </p>
                    </div>

                    {{-- Actions --}}
                    <div class="dcc-form-actions">
                        <a href="{{ route('nok.dashboard') }}" class="dcc-btn dcc-btn-ghost">Cancel</a>
                        <button type="submit" class="dcc-btn dcc-btn-primary">
                            <i class="bi bi-shield-lock-fill"></i> Submit for Verification
                        </button>
                    </div>
                </div>
            </section>
        </form>

        {{-- RIGHT: What happens next --}}
        <aside>
            <div class="dcc-next-card">
                <div class="dcc-next-head">
                    <i class="bi bi-list-check"></i>
                    <h3>What happens next</h3>
                </div>
                <div class="dcc-next-body">
                    <div class="dcc-step">
                        <div class="dcc-step-num">1</div>
                        <div class="dcc-step-text">
                            <strong>Admin review</strong>
                            IIUM welfare administrators receive your submission and review the documentation respectfully.
                        </div>
                    </div>
                    <div class="dcc-step">
                        <div class="dcc-step-num">2</div>
                        <div class="dcc-step-text">
                            <strong>You'll be notified</strong>
                            An email will reach you once verified — or if any clarification is needed.
                        </div>
                    </div>
                    <div class="dcc-step">
                        <div class="dcc-step-num">3</div>
                        <div class="dcc-step-text">
                            <strong>Immutable record</strong>
                            On verification, an immutable blockchain record is created — preserving authenticity.
                        </div>
                    </div>
                    <div class="dcc-step">
                        <div class="dcc-step-num">4</div>
                        <div class="dcc-step-text">
                            <strong>Final messages released</strong>
                            Any LDMS (Last Digital Messages) the student left for you will be released securely.
                        </div>
                    </div>
                </div>
            </div>

            <div class="dcc-encryption">
                <h4><i class="bi bi-shield-fill-check"></i> Your data is protected</h4>
                <p>Files are stored with AES-256 encryption. Only authorized welfare staff can decrypt them during verification — and every access is logged.</p>
            </div>
        </aside>
    </div>
</div>

<script>
    function showSelectedFile(input) {
        const dropzone = document.getElementById('dropzoneLabel');
        const icon = document.getElementById('dropzoneIcon');
        const text = document.getElementById('dropzoneText');
        const hint = document.getElementById('dropzoneHint');

        if (input.files && input.files[0]) {
            const file = input.files[0];
            const sizeKB = (file.size / 1024).toFixed(0);
            const sizeMB = (file.size / 1024 / 1024).toFixed(2);
            const display = file.size > 1024 * 1024 ? `${sizeMB} MB` : `${sizeKB} KB`;

            dropzone.classList.add('has-file');
            dropzone.classList.remove('error');
            icon.className = 'bi bi-file-earmark-check-fill';
            text.textContent = file.name;
            hint.textContent = `${display} · click to change`;
        }
    }

    // Drag-and-drop support
    const dropzone = document.getElementById('dropzoneLabel');
    const fileInput = document.getElementById('mediaFile');

    ['dragenter', 'dragover'].forEach(evt => {
        dropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(evt => {
        dropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
        });
    });

    dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        if (dt.files && dt.files[0]) {
            fileInput.files = dt.files;
            showSelectedFile(fileInput);
        }
    });
</script>
@endsection
