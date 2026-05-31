@extends('layouts.nok')
@section('title', 'Edit Report #' . $report->report_id)

@php
    $isRejected = $report->report_status === 'rejected';
    $crisis = $report->crisis;
    $incidentDate = '';
    $incidentTime = '';
    if (!empty($details['incident_at'])) {
        try {
            $dt = \Carbon\Carbon::parse($details['incident_at']);
            $incidentDate = $dt->format('Y-m-d');
            $incidentTime = $dt->format('H:i');
        } catch (\Throwable $e) {}
    } elseif ($crisis && $crisis->incident_at) {
        $dt = \Carbon\Carbon::parse($crisis->incident_at);
        $incidentDate = $dt->format('Y-m-d');
        $incidentTime = $dt->format('H:i');
    }
    $evidence = (array) ($report->supporting_evidence_path ?? []);
@endphp

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .crsh-wrap {
        --bg:#f5f6fa; --card:#fff; --ink:#1a2238; --ink-soft:#5b6479; --ink-faint:#8a92a6;
        --border:#e8eaf0; --border-soft:#f0f2f7;
        --primary:#2563eb; --primary-tint:#eef3ff; --primary-dark:#1d4ed8;
        --success:#15803d; --success-tint:#e8f6ee;
        --amber:#b45309; --amber-tint:#fdf1de;
        --danger:#b91c1c; --danger-tint:#fdeaea;
        --shadow:0 1px 2px rgba(20,28,55,.04), 0 4px 16px rgba(20,28,55,.04);
        font-family:'Inter',-apple-system,sans-serif; color:var(--ink); line-height:1.55;
    }
    .crsh-wrap *,.crsh-wrap *::before,.crsh-wrap *::after{box-sizing:border-box}

    .crsh-back{display:inline-flex;align-items:center;gap:6px;color:var(--primary);text-decoration:none;font-size:14px;font-weight:500;padding:8px 12px;margin:0 0 16px -12px;border-radius:10px}
    .crsh-back:hover{background:var(--primary-tint)}

    .crsh-card{background:#fff;border-radius:16px;box-shadow:var(--shadow);margin-bottom:18px;overflow:hidden}
    .crsh-card-head{display:flex;align-items:center;gap:10px;padding:16px 22px;border-bottom:1px solid var(--border-soft)}
    .crsh-card-head-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:14px;background:var(--primary-tint);color:var(--primary)}
    .crsh-card-head h3{margin:0;font-size:15px;font-weight:700}
    .crsh-card-body{padding:22px}

    .crsh-hero{padding:26px 30px;position:relative}
    .crsh-hero::before{content:"";position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--amber),#f59e0b)}
    .crsh-hero.rejected::before{background:linear-gradient(90deg,var(--danger),#ef4444)}
    .crsh-hero h1{font-family:'Fraunces',Georgia,serif;font-weight:600;font-size:28px;letter-spacing:-.018em;margin:0 0 6px;color:var(--ink)}
    .crsh-hero p{color:var(--ink-soft);font-size:14px;margin:0}

    .crsh-pill{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:4px 10px;border-radius:999px;text-transform:uppercase;letter-spacing:.04em;margin-bottom:12px}
    .pill-pending{background:var(--amber-tint);color:var(--amber)}
    .pill-rejected{background:var(--danger-tint);color:var(--danger)}

    .crsh-reject{background:var(--danger-tint);border:1px solid #fecaca;border-radius:12px;padding:14px 16px;margin-bottom:16px}
    .crsh-reject h4{color:var(--danger);font-size:14px;font-weight:700;margin:0 0 8px;display:flex;align-items:center;gap:6px}
    .crsh-reject .reason{background:#fff;border-radius:8px;padding:12px 14px;font-size:13px;line-height:1.55;color:var(--ink);border-left:3px solid var(--danger)}
    .crsh-reject .reason strong{color:var(--danger)}

    /* "Filed on behalf of" card for NOK */
    .crsh-student-card{background:linear-gradient(135deg,#fff7ed,#ffedd5);border:1px solid #fed7aa;border-radius:12px;padding:14px 16px;margin-bottom:16px;display:flex;align-items:center;gap:12px}
    .crsh-student-avatar{width:42px;height:42px;border-radius:50%;background:#c2410c;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;flex-shrink:0;font-family:'Fraunces',serif}
    .crsh-student-info{flex:1;min-width:0}
    .crsh-student-label{font-size:11px;font-weight:700;color:#9a3412;text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px}
    .crsh-student-name{font-size:14px;font-weight:600;color:#1a2238;line-height:1.3}
    .crsh-student-meta{font-size:11.5px;color:#7c2d12;margin-top:2px}

    .crsh-form-group{margin-bottom:18px}
    .crsh-form-label{display:block;font-weight:600;font-size:13px;margin-bottom:6px;color:var(--ink)}
    .crsh-form-label .req{color:var(--danger);margin-left:2px}
    .crsh-form-input,.crsh-form-select,.crsh-form-textarea{
        width:100%;background:#fff;border:1.5px solid var(--border);border-radius:10px;
        padding:10px 14px;font-size:14px;color:var(--ink);font-family:'Inter',sans-serif;
        transition:all .15s ease;
    }
    .crsh-form-input:focus,.crsh-form-select:focus,.crsh-form-textarea:focus{
        outline:none;border-color:var(--primary);box-shadow:0 0 0 4px rgba(37,99,235,.1);
    }
    .crsh-form-textarea{resize:vertical;min-height:110px;line-height:1.5}
    .crsh-form-hint{font-size:11.5px;color:var(--ink-faint);margin:4px 0 0}
    .crsh-form-error{font-size:12px;color:var(--danger);margin:4px 0 0;display:flex;align-items:center;gap:4px}

    .crsh-grid-form{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    @media (max-width:640px){.crsh-grid-form{grid-template-columns:1fr}}

    .crsh-file-drop{padding:14px;background:var(--bg);border:2px dashed var(--border);border-radius:10px;text-align:center;cursor:pointer;transition:all .15s}
    .crsh-file-drop:hover{border-color:var(--primary);background:var(--primary-tint)}
    .crsh-file-drop i{font-size:28px;color:var(--ink-faint);display:block;margin-bottom:6px}
    .crsh-file-drop p{margin:0;font-size:13px;color:var(--ink-soft)}

    .crsh-file-chip{background:var(--bg);padding:6px 10px;border-radius:8px;font-size:12px;display:inline-flex;align-items:center;gap:6px;margin-right:6px;margin-bottom:6px;border:1px solid var(--border-soft)}

    .crsh-history{background:var(--bg);padding:14px;border-radius:10px;border:1px solid var(--border-soft)}
    .crsh-history-row{display:flex;align-items:center;gap:10px;padding:8px 0;font-size:12.5px;border-bottom:1px solid var(--border-soft)}
    .crsh-history-row:last-child{border-bottom:none}
    .crsh-history-when{color:var(--ink-faint);font-size:11.5px;min-width:140px;font-family:ui-monospace,monospace}

    .crsh-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:10px 18px;border-radius:10px;font-weight:600;font-size:13.5px;cursor:pointer;text-decoration:none;border:1.5px solid transparent;font-family:'Inter',sans-serif;transition:all .15s}
    .crsh-btn-primary{background:var(--primary);color:#fff}
    .crsh-btn-primary:hover{background:var(--primary-dark);color:#fff}
    .crsh-btn-ghost{background:#fff;color:var(--ink);border-color:var(--border)}
    .crsh-btn-ghost:hover{background:var(--bg)}

    .crsh-form-actions{display:flex;gap:10px;justify-content:flex-end;padding-top:14px;border-top:1px solid var(--border-soft);margin-top:6px}
</style>
@endpush

@section('content')
<div class="crsh-wrap">

    <a href="{{ route('nok.crisis.show', $report->report_id) }}" class="crsh-back">
        <i class="bi bi-arrow-left"></i> Back to report
    </a>

    <section class="crsh-card crsh-hero {{ $isRejected ? 'rejected' : 'pending' }}">
        @if ($isRejected)
            <span class="crsh-pill pill-rejected"><i class="bi bi-arrow-clockwise"></i> Resubmitting</span>
        @else
            <span class="crsh-pill pill-pending"><i class="bi bi-pencil-fill"></i> Editing</span>
        @endif
        <h1>{{ $isRejected ? 'Edit & Resubmit Report' : 'Edit Pending Report' }}</h1>
        <p>{{ $isRejected ? 'Update your report based on admin feedback below. Once submitted, it goes back to pending review.' : 'Make changes to your pending report. You can keep editing until an admin starts the review.' }}</p>
    </section>

    {{-- Filed on behalf of - reminder for NOK --}}
    @if ($report->student)
        @php
            $studentInitials = strtoupper(substr($report->student->first_name ?? '', 0, 1) . substr($report->student->last_name ?? '', 0, 1));
            if (empty($studentInitials)) {
                $studentInitials = strtoupper(substr($report->student->full_name ?? 'S', 0, 1));
            }
        @endphp
        <div class="crsh-student-card">
            <div class="crsh-student-avatar">{{ $studentInitials }}</div>
            <div class="crsh-student-info">
                <div class="crsh-student-label">Filed on behalf of</div>
                <div class="crsh-student-name">{{ $report->student->full_name ?? 'Student' }}</div>
                @if ($report->student->student_id)
                    <div class="crsh-student-meta">Matric: {{ $report->student->student_id }}</div>
                @endif
            </div>
        </div>
    @endif

    {{-- Admin feedback reminder (rejected only) --}}
    @if ($isRejected && $report->admin_remarks)
        <div class="crsh-reject">
            <h4><i class="bi bi-info-circle-fill"></i> Admin's feedback — please address before resubmitting</h4>
            <div class="reason"><strong>Feedback:</strong> {{ $report->admin_remarks }}</div>
        </div>
    @endif

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="crsh-reject" style="background:#fef2f2">
            <h4><i class="bi bi-exclamation-triangle-fill"></i> Please fix the errors below</h4>
            <ul style="margin:0;padding-left:20px;font-size:13px;color:var(--danger)">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('nok.crisis.update', $report->report_id) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PATCH')

        <section class="crsh-card">
            <div class="crsh-card-head">
                <div class="crsh-card-head-icon"><i class="bi bi-pencil-square"></i></div>
                <h3>Report Details</h3>
            </div>
            <div class="crsh-card-body">

                <div class="crsh-grid-form">
                    <div class="crsh-form-group">
                        <label class="crsh-form-label">Crisis Type <span class="req">*</span></label>
                        <select name="crisis_type" class="crsh-form-select" required>
                            @php $ct = old('crisis_type', $crisis->crisis_type ?? ''); @endphp
                            <option value="medical"          @selected($ct === 'medical')>Medical / Illness</option>
                            <option value="accident"         @selected($ct === 'accident')>Accident</option>
                            <option value="natural_disaster" @selected($ct === 'natural_disaster')>Natural Disaster</option>
                            <option value="death"            @selected($ct === 'death')>Death / Bereavement</option>
                        </select>
                    </div>
                    <div class="crsh-form-group">
                        <label class="crsh-form-label">Sub-category <span class="req">*</span></label>
                        <input type="text" name="sub_category" class="crsh-form-input" value="{{ old('sub_category', $crisis->sub_category ?? '') }}" placeholder="e.g. Vehicle accident, House fire" required>
                    </div>
                </div>

                <div class="crsh-form-group">
                    <label class="crsh-form-label">Location <span class="req">*</span></label>
                    <input type="text" name="location" class="crsh-form-input" value="{{ old('location', $crisis->location ?? '') }}" placeholder="Full address or landmark" required>
                </div>

                <div class="crsh-grid-form">
                    <div class="crsh-form-group">
                        <label class="crsh-form-label">Incident Date <span class="req">*</span></label>
                        <input type="date" name="incident_date" class="crsh-form-input" value="{{ old('incident_date', $incidentDate) }}" max="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="crsh-form-group">
                        <label class="crsh-form-label">Incident Time <span class="req">*</span></label>
                        <input type="time" name="incident_time" class="crsh-form-input" value="{{ old('incident_time', $incidentTime) }}" required>
                    </div>
                </div>

                <div class="crsh-form-group">
                    <label class="crsh-form-label">Impact Level <span class="req">*</span></label>
                    <select name="impact_level" class="crsh-form-select" required>
                        @php $il = old('impact_level', $crisis->impact_level ?? 'medium'); @endphp
                        <option value="low"      @selected($il === 'low')>Low</option>
                        <option value="medium"   @selected($il === 'medium')>Medium</option>
                        <option value="high"     @selected($il === 'high')>High</option>
                        <option value="critical" @selected($il === 'critical')>Critical</option>
                    </select>
                </div>

                <div class="crsh-form-group">
                    <label class="crsh-form-label">Public Description <span class="req">*</span></label>
                    <textarea name="crisis_description" class="crsh-form-textarea" minlength="10" maxlength="2000" required>{{ old('crisis_description', $crisis->crisis_description ?? $report->report_description) }}</textarea>
                    <p class="crsh-form-hint">This appears on the public donate page. Minimum 10 characters.</p>
                </div>

                <div class="crsh-form-group">
                    <label class="crsh-form-label">Immediate Actions Taken <span style="color:var(--ink-faint);font-weight:400">(optional)</span></label>
                    <textarea name="immediate_actions" class="crsh-form-textarea" maxlength="1000" placeholder="What has already been done? (e.g. hospitalized, family notified, police report filed)">{{ old('immediate_actions', $details['immediate_actions'] ?? '') }}</textarea>
                </div>

                <div class="crsh-form-group">
                    <label class="crsh-form-label">Supporting Documents <span style="color:var(--ink-faint);font-weight:400">(optional, max 5 files, 5MB each)</span></label>

                    @if (!empty($evidence))
                        <div style="margin-bottom:10px">
                            <p class="crsh-form-hint" style="margin-bottom:6px"><strong>Currently uploaded:</strong></p>
                            @foreach ($evidence as $i => $path)
                                <span class="crsh-file-chip">
                                    <i class="bi bi-file-earmark-check" style="color:var(--success)"></i>
                                    <span style="color:var(--ink)">Document {{ $i + 1 }}</span>
                                </span>
                            @endforeach
                            <p class="crsh-form-hint">Existing files are kept. Upload below to add more.</p>
                        </div>
                    @endif

                    <label for="evidenceUpload" class="crsh-file-drop">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <p>Click to add more documents (PDF, JPG, PNG, DOC)</p>
                    </label>
                    <input type="file" id="evidenceUpload" name="supporting_evidence[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="display:none" onchange="showFiles(this)">
                    <div id="newFilesList" style="margin-top:8px"></div>
                </div>

                {{-- Edit history audit trail --}}
                <div class="crsh-form-group">
                    <label class="crsh-form-label" style="display:flex;align-items:center;gap:6px">
                        <i class="bi bi-clock-history"></i> Edit History (audit trail)
                    </label>
                    <div class="crsh-history">
                        <div class="crsh-history-row">
                            <span class="crsh-history-when">{{ $report->date_reported?->format('d M, H:i') }}</span>
                            <span>Original submission</span>
                        </div>
                        @if ($isRejected && $report->verified_at)
                            <div class="crsh-history-row">
                                <span class="crsh-history-when">{{ $report->verified_at->format('d M, H:i') }}</span>
                                <span style="color:var(--danger)">
                                    @if ($report->verifier)
                                        Rejected by {{ $report->verifier->admin_name ?? 'Admin' }}
                                    @else
                                        Rejected
                                    @endif
                                </span>
                            </div>
                        @endif
                        @if (!empty($details['last_edited_at']))
                            <div class="crsh-history-row">
                                <span class="crsh-history-when">{{ \Carbon\Carbon::parse($details['last_edited_at'])->format('d M, H:i') }}</span>
                                <span>Last edited</span>
                            </div>
                        @endif
                        <div class="crsh-history-row">
                            <span class="crsh-history-when">{{ now()->format('d M, H:i') }}</span>
                            <span style="color:var(--primary)">Editing now... (you)</span>
                        </div>
                    </div>
                </div>

                <div class="crsh-form-actions">
                    <a href="{{ route('nok.crisis.show', $report->report_id) }}" class="crsh-btn crsh-btn-ghost">Cancel</a>
                    <button type="submit" class="crsh-btn crsh-btn-primary">
                        <i class="bi bi-send"></i>
                        {{ $isRejected ? 'Submit for Re-review' : 'Save Changes' }}
                    </button>
                </div>
            </div>
        </section>
    </form>
</div>

<script>
    function showFiles(input) {
        const list = document.getElementById('newFilesList');
        list.innerHTML = '';
        Array.from(input.files).forEach(f => {
            const span = document.createElement('span');
            span.className = 'crsh-file-chip';
            span.innerHTML = '<i class="bi bi-paperclip" style="color:var(--primary)"></i> ' + f.name + ' <small style="color:var(--ink-faint)">(' + (f.size/1024).toFixed(0) + ' KB)</small>';
            list.appendChild(span);
        });
    }
</script>
@endsection
