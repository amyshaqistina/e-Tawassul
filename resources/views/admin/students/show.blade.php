@extends('layouts.admin')
@section('title', 'Student ' . $student->student_id)

@push('styles')
<style>
    /* ===== Hero (matches the student profile page) ===== */
    .sp-hero {
        background: linear-gradient(135deg, #1E40AF, #06B6D4);
        color: #fff; border-radius: 16px; padding: 26px 28px;
        display: flex; align-items: center; gap: 20px; margin-bottom: 18px;
        position: relative; overflow: hidden;
    }
    .sp-hero-body { 
        z-index: 1; 
        flex: 1; 
        min-width: 0; 
    }
    .sp-hero h3 { 
        margin: 0 0 6px; 
        font-size: 24px; 
        font-weight: 800; 
        word-wrap: break-word;
        overflow-wrap: break-word;
        white-space: normal;
    }
    @media (max-width: 575px) {
        .sp-hero {
            flex-direction: column;
            text-align: center;
            padding: 40px 20px;
            gap: 20px;
            align-items: center;
        }
        .sp-hero .avatar {
            margin: 0 auto;
        }
        .sp-hero-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .sp-hero h3 { 
            margin: 0 0 8px; 
            line-height: 1.4;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            font-size: 22px;
        }
        .sp-hero .meta {
            flex-direction: column;
            gap: 6px;
            justify-content: center;
            opacity: 0.9;
            line-height: 1.4;
        }
        .sp-hero .status-pill {
            margin-top: 16px;
            justify-content: center;
        }
    }
    .sp-hero::after {
        content: ""; position: absolute; right: -40px; top: -40px;
        width: 220px; height: 220px; border-radius: 50%;
        background: rgba(255,255,255,0.06);
    }
    .sp-hero .avatar {
        width: 78px; height: 78px; border-radius: 50%;
        background: rgba(255,255,255,0.18); border: 3px solid rgba(255,255,255,0.35);
        font-size: 28px; font-weight: 700; color: #fff;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0; z-index: 1;
    }
    .sp-hero-body { z-index: 1; }
    .sp-hero h3 { margin: 0 0 6px; font-size: 24px; font-weight: 800; }
    .sp-hero .meta { font-size: 13.5px; opacity: 0.92; display: flex; gap: 6px; align-items: center; }
    .sp-hero .status-pill {
        background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.35);
        padding: 5px 14px; border-radius: 14px; font-size: 11px; font-weight: 700;
        letter-spacing: 0.4px; text-transform: uppercase; display: inline-flex; align-items: center; gap: 6px;
        margin-top: 10px;
    }

    /* ===== Section cards ===== */
    .sp-card { background: #fff; border: 1px solid #E5E7EB; border-radius: 14px;
               margin-bottom: 16px; overflow: hidden; }
    .sp-card-head {
        padding: 16px 20px; border-bottom: 1px solid #F1F5F9;
        display: flex; align-items: center; gap: 12px;
    }
    .sp-card-head .hicon {
        width: 38px; height: 38px; border-radius: 10px; background: #EFF6FF; color: #1E40AF;
        display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;
    }
    .sp-card-head h5 { margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; flex: 1; }
    .sp-edit-link {
        color: #1E40AF; text-decoration: none; font-size: 13.5px; font-weight: 600;
        display: inline-flex; align-items: center; gap: 5px; cursor: pointer;
    }
    .sp-edit-link:hover { color: #1d4ed8; text-decoration: underline; }

    /* ===== Info rows (icon + label + value), mirrors the profile layout ===== */
    .sp-row { display: grid; grid-template-columns: 44px 170px 1fr; gap: 14px;
              padding: 15px 20px; border-bottom: 1px solid #F3F4F6; align-items: start; }
    .sp-row:last-child { border-bottom: none; }
    .sp-row .ricon {
        width: 32px; height: 32px; border-radius: 8px; background: #EFF6FF; color: #1E40AF;
        display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0;
    }
    .sp-row .rlabel { font-size: 11px; font-weight: 700; color: #6B7280;
                      text-transform: uppercase; letter-spacing: 0.5px; padding-top: 7px; }
    .sp-row .rvalue { font-size: 14.5px; color: #111827; line-height: 1.5; padding-top: 5px; word-break: break-word; }
    .sp-row .rvalue.empty { color: #94A3B8; font-style: italic; }
    .sp-row .rhint { font-size: 11.5px; color: #94A3B8; margin-top: 2px; }

    @media (max-width: 575px) {
        .sp-row { grid-template-columns: 32px 1fr; }
        .sp-row .rlabel { grid-column: 2; padding-top: 0; }
        .sp-row .rvalue { grid-column: 2; }
    }

    /* ===== NOK rows ===== */
    .nok-row { padding: 14px 20px; border-bottom: 1px solid #F3F4F6; display: flex; gap: 14px; align-items: flex-start; }
    .nok-row:last-child { border-bottom: none; }
    .nok-row .nok-avatar { width: 42px; height: 42px; border-radius: 50%;
                           background: linear-gradient(135deg,#1E40AF,#06B6D4); color: #fff;
                           font-weight: 700; font-size: 14px;
                           display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .nok-row .nok-name { font-size: 14px; font-weight: 700; color: #111827; }
    .nok-row .nok-contact { font-size: 12px; color: #6B7280; margin: 2px 0 0; }
    .pill-primary { background: #1E40AF; color: #fff; font-size: 9px; font-weight: 700;
                    padding: 2px 7px; border-radius: 10px; margin-left: 6px; text-transform: uppercase; letter-spacing: 0.4px; }
    .pill-admin-added { background: #FEF3C7; color: #92400E; font-size: 10px; padding: 2px 8px;
                        border-radius: 10px; margin-top: 4px; display: inline-block; }

    .empty-state { text-align: center; padding: 30px 20px; color: #6B7280; }
    .empty-state i { font-size: 36px; color: #D1D5DB; display: block; margin-bottom: 8px; }

    .back-link { color: #1E40AF; text-decoration: none; font-size: 14px;
                 display: inline-flex; align-items: center; gap: 6px; margin-bottom: 12px; }
    .back-link:hover { text-decoration: underline; }

    /* ===== Blue brand button (Register Next of Kin / Save) ===== */
    .btn-brand {
        background: #1a56db; color: #fff; border: none; font-weight: 600; font-size: 13.5px;
        padding: 9px 18px; border-radius: 8px; display: inline-flex; align-items: center; gap: 7px;
    }
    .btn-brand:hover { background: #1245b8; color: #fff; }

    .sp-form-note { background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px;
                    padding: 10px 12px; font-size: 12px; color: #1e3a8a; margin: 0 20px 14px; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <a href="{{ route('admin.students.index') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to Student Records
    </a>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    @php
        $st = strtolower($student->status ?? 'active');
        $stLabel = ucfirst($st);
        $initials = strtoupper(substr($student->first_name ?? $student->full_name ?? '?', 0, 1)
                    . substr($student->last_name ?? '', 0, 1));
    @endphp

    {{-- ===== Hero ===== --}}
    <div class="sp-hero">
        <div class="avatar">{{ $initials }}</div>
        <div class="sp-hero-body">
            <h3>{{ $student->full_name }}</h3>
            <div class="meta"><i class="bi bi-person-vcard"></i> {{ $student->student_id }}
                <span>·</span> <i class="bi bi-envelope"></i> {{ $student->email }}</div>
            <span class="status-pill">
                @if($st === 'deceased')<i class="bi bi-circle-fill" style="font-size:6px;"></i>@else<i class="bi bi-check-circle-fill"></i>@endif
                {{ $stLabel }}
            </span>
        </div>
    </div>

    {{-- Single stacked column — same top-to-bottom flow as the student profile page.
         Cards now run full width, one after another, and you scroll through them. --}}

            {{-- ===== READ-ONLY VIEW ===== --}}
            <div id="viewMode">

                {{-- Academic --}}
                <div class="sp-card">
                    <div class="sp-card-head">
                        <div class="hicon"><i class="bi bi-mortarboard-fill"></i></div>
                        <h5>Academic Information</h5>
                        <a class="sp-edit-link" onclick="toggleEdit(true)"><i class="bi bi-pencil-square"></i> Edit</a>
                    </div>
                    <div class="sp-row">
                        <div class="ricon"><i class="bi bi-bank"></i></div>
                        <div class="rlabel">Kulliyyah</div>
                        <div class="rvalue {{ $student->kulliyyah ? '' : 'empty' }}">
                            {{ $student->kulliyyah ?? 'Not specified' }}
                            <div class="rhint">From iMaalum — not editable</div>
                        </div>
                    </div>
                    <div class="sp-row">
                        <div class="ricon"><i class="bi bi-book"></i></div>
                        <div class="rlabel">Programme</div>
                        <div class="rvalue {{ $student->programme ? '' : 'empty' }}">{{ $student->programme ?? 'Not specified' }}</div>
                    </div>
                    <div class="sp-row">
                        <div class="ricon"><i class="bi bi-calendar3"></i></div>
                        <div class="rlabel">Year of Study</div>
                        <div class="rvalue {{ $student->year_of_study ? '' : 'empty' }}">{{ $student->year_of_study ? 'Year ' . $student->year_of_study : 'Not specified' }}</div>
                    </div>
                    <div class="sp-row">
                        <div class="ricon"><i class="bi bi-house-door"></i></div>
                        <div class="rlabel">Mahallah</div>
                        <div class="rvalue {{ $student->mahallah ? '' : 'empty' }}">{{ $student->mahallah ?? 'Not specified' }}</div>
                    </div>
                    <div class="sp-row">
                        <div class="ricon"><i class="bi bi-clipboard-check"></i></div>
                        <div class="rlabel">Enrollment</div>
                        <div class="rvalue {{ $student->enrollment_status ? '' : 'empty' }}">{{ $student->enrollment_status ?? 'Not specified' }}</div>
                    </div>
                    <div class="sp-row">
                        <div class="ricon"><i class="bi bi-clock-history"></i></div>
                        <div class="rlabel">Last iMaalum Sync</div>
                        <div class="rvalue {{ $student->imaalum_synced_at ? '' : 'empty' }}">{{ $student->imaalum_synced_at?->format('d M Y, H:i') ?? 'Never' }}</div>
                    </div>
                </div>

                {{-- Contact --}}
                <div class="sp-card">
                    <div class="sp-card-head">
                        <div class="hicon"><i class="bi bi-person-lines-fill"></i></div>
                        <h5>Contact</h5>
                        <a class="sp-edit-link" onclick="toggleEdit(true)"><i class="bi bi-pencil-square"></i> Edit</a>
                    </div>
                    <div class="sp-row">
                        <div class="ricon"><i class="bi bi-envelope"></i></div>
                        <div class="rlabel">Email</div>
                        <div class="rvalue">{{ $student->email }}<div class="rhint">From iMaalum — not editable</div></div>
                    </div>
                    <div class="sp-row">
                        <div class="ricon"><i class="bi bi-telephone"></i></div>
                        <div class="rlabel">Phone</div>
                        <div class="rvalue {{ $student->phone ? '' : 'empty' }}">{{ $student->phone ?? 'Not provided' }}</div>
                    </div>
                    <div class="sp-row">
                        <div class="ricon"><i class="bi bi-exclamation-circle"></i></div>
                        <div class="rlabel">Emergency Contact</div>
                        <div class="rvalue {{ $student->emergency_contact ? '' : 'empty' }}">{{ $student->emergency_contact ?? 'Not provided' }}</div>
                    </div>
                </div>

                {{-- Bank account (student-provided) --}}
                <div class="sp-card">
                    <div class="sp-card-head">
                        <div class="hicon"><i class="bi bi-credit-card-2-front"></i></div>
                        <h5>Bank Account</h5>
                        <a class="sp-edit-link" onclick="toggleEdit(true)"><i class="bi bi-pencil-square"></i> Edit</a>
                    </div>
                    <div class="sp-row">
                        <div class="ricon"><i class="bi bi-bank2"></i></div>
                        <div class="rlabel">Bank Name</div>
                        <div class="rvalue {{ $student->bank_name ? '' : 'empty' }}">{{ $student->bank_name ?? 'Not provided' }}</div>
                    </div>
                    <div class="sp-row">
                        <div class="ricon"><i class="bi bi-hash"></i></div>
                        <div class="rlabel">Account Number</div>
                        <div class="rvalue {{ $student->bank_account_number ? '' : 'empty' }}">{{ $student->bank_account_number ?? 'Not provided' }}</div>
                    </div>
                    <div class="sp-row">
                        <div class="ricon"><i class="bi bi-person-badge"></i></div>
                        <div class="rlabel">Account Holder</div>
                        <div class="rvalue {{ $student->bank_account_holder ? '' : 'empty' }}">{{ $student->bank_account_holder ?? 'Not provided' }}</div>
                    </div>
                </div>
            </div>

            {{-- ===== EDIT MODE (admin-editable fields) ===== --}}
            <div id="editMode" style="display:none;">
                <div class="sp-card">
                    <div class="sp-card-head">
                        <div class="hicon"><i class="bi bi-pencil-square"></i></div>
                        <h5>Edit Student Details</h5>
                        <a class="sp-edit-link" onclick="toggleEdit(false)"><i class="bi bi-x-lg"></i> Cancel</a>
                    </div>
                    <div class="sp-form-note">
                        <i class="bi bi-info-circle"></i> iMaalum-sourced fields (kulliyyah, email, year, enrollment)
                        are not editable here. Changes are saved against the student record.
                    </div>
                    {{-- NOTE: raw URL because the route doesn't exist yet. After you add the
                         route + controller method (see chat), switch this to
                         route('admin.students.update', $student->student_id). --}}
                    <form method="POST" action="/admin/students/{{ $student->student_id }}">
                        @csrf
                        @method('PATCH')
                        <div class="row g-3" style="padding:4px 20px 20px;">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1">Programme</label>
                                <input type="text" name="programme" class="form-control form-control-sm" value="{{ old('programme', $student->programme) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1">Mahallah</label>
                                <input type="text" name="mahallah" class="form-control form-control-sm" value="{{ old('mahallah', $student->mahallah) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1">Phone</label>
                                <input type="text" name="phone" class="form-control form-control-sm" value="{{ old('phone', $student->phone) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1">Emergency Contact</label>
                                <input type="text" name="emergency_contact" class="form-control form-control-sm" value="{{ old('emergency_contact', $student->emergency_contact) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    @foreach(['active'=>'Active','inactive'=>'Inactive','suspended'=>'Suspended','deceased'=>'Deceased'] as $val=>$lab)
                                        <option value="{{ $val }}" {{ $st === $val ? 'selected' : '' }}>{{ $lab }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12"><hr class="my-1"><strong class="small text-muted">Bank account</strong></div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold mb-1">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control form-control-sm" value="{{ old('bank_name', $student->bank_name) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold mb-1">Account Number</label>
                                <input type="text" name="bank_account_number" class="form-control form-control-sm" value="{{ old('bank_account_number', $student->bank_account_number) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold mb-1">Account Holder</label>
                                <input type="text" name="bank_account_holder" class="form-control form-control-sm" value="{{ old('bank_account_holder', $student->bank_account_holder) }}">
                            </div>

                            <div class="col-12 mt-2">
                                <button type="submit" class="btn-brand"><i class="bi bi-check-lg"></i> Save Changes</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="toggleEdit(false)">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        {{-- ============ Next of Kin (stacked below) ============ --}}

            <div class="sp-card">
                <div class="sp-card-head">
                    <div class="hicon"><i class="bi bi-people-fill"></i></div>
                    <h5>Next of Kin ({{ $student->nextOfKin->count() }})</h5>
                </div>

                @if($student->nextOfKin->isEmpty())
                    <div class="empty-state">
                        <i class="bi bi-people"></i>
                        <p class="mb-2">No next of kin registered for this student.</p>
                        <small class="text-muted">
                            If a death event has occurred and the family has provided contact information,
                            you can register a kin manually below.
                        </small>
                    </div>
                @else
                    @foreach($student->nextOfKin as $nok)
                        <div class="nok-row">
                            <div class="nok-avatar">{{ strtoupper(substr($nok->full_name, 0, 1)) }}</div>
                            <div style="flex:1; min-width:0;">
                                <div class="nok-name">
                                    {{ $nok->full_name }}
                                    <small style="color:#6B7280; font-weight:400;">({{ $nok->relationship_to_student }})</small>
                                    @if($nok->is_primary)
                                        <span class="pill-primary"><i class="bi bi-star-fill"></i> Primary</span>
                                    @endif
                                </div>
                                <p class="nok-contact">
                                    <i class="bi bi-envelope"></i> {{ $nok->email }}<br>
                                    <i class="bi bi-telephone"></i> {{ $nok->phone }}
                                </p>
                                @if($nok->address)
                                    <p class="nok-contact"><i class="bi bi-geo-alt"></i> {{ $nok->address }}</p>
                                @endif
                                @if($nok->registered_by === 'admin')
                                    <span class="pill-admin-added">
                                        <i class="bi bi-shield-check"></i> Added by admin {{ $nok->registered_at?->format('d M Y') }}
                                    </span>
                                @else
                                    <small class="text-muted d-block" style="font-size:11px; margin-top:4px;">Registered by student</small>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Admin-add kin form --}}
            <div class="sp-card">
                <div class="sp-card-head">
                    <div class="hicon"><i class="bi bi-person-plus-fill"></i></div>
                    <h5>Register a Next of Kin (Admin)</h5>
                </div>
                <div class="sp-form-note">
                    <i class="bi bi-info-circle"></i> Use this when a death event has occurred and the student did not
                    pre-fill kin records. Gather info from the family / death certificate before submitting.
                    This action is recorded in the audit log.
                </div>
                <div style="padding:0 20px 18px;">
                    <form method="POST" action="{{ route('admin.students.kin.store', $student->student_id) }}">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control form-control-sm" value="{{ old('first_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control form-control-sm" value="{{ old('last_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1">Relationship <span class="text-danger">*</span></label>
                                <select name="relationship_to_student" class="form-select form-select-sm" required>
                                    <option value="">— Choose —</option>
                                    @foreach(['Father','Mother','Spouse','Sibling','Guardian','Grandparent','Uncle','Aunt','Cousin','Other'] as $rel)
                                        <option value="{{ $rel }}" {{ old('relationship_to_student') === $rel ? 'selected' : '' }}>{{ $rel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1">Phone <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control form-control-sm" value="{{ old('phone') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold mb-1">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email') }}" required>
                                <small class="text-muted">Used for OTP login. They will be able to sign in immediately after this.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold mb-1">Address</label>
                                <textarea name="address" class="form-control form-control-sm" rows="2">{{ old('address') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold mb-1">Admin Note (audit log)</label>
                                <textarea name="admin_note" class="form-control form-control-sm" rows="2"
                                          placeholder="e.g. Registered after death event on 2026-05-17; family contacted by phone, info from death cert">{{ old('admin_note') }}</textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn-brand mt-3">
                            <i class="bi bi-shield-check"></i> Register Next of Kin
                        </button>
                    </form>
                </div>
            </div>
</div>

@push('scripts')
<script>
    function toggleEdit(on) {
        document.getElementById('viewMode').style.display = on ? 'none' : 'block';
        document.getElementById('editMode').style.display = on ? 'block' : 'none';
        if (on) document.getElementById('editMode').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
</script>
@endpush
@endsection
