@extends('layouts.admin')
@section('title', 'Student ' . $student->student_id)
@section('page-title', 'Student Detail')

@push('styles')
<style>
    .student-hero { background:linear-gradient(135deg,#1E40AF,#06B6D4); color:#fff;
                    border-radius:14px; padding:24px; display:flex; align-items:center; gap:18px;
                    margin-bottom:16px; }
    .student-hero .avatar { width:72px; height:72px; border-radius:50%;
                            background:rgba(255,255,255,0.18); border:3px solid rgba(255,255,255,0.3);
                            font-size:28px; font-weight:700; color:#fff;
                            display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .student-hero h3 { margin:0 0 4px; font-size:22px; }
    .student-hero .meta { font-size:13px; opacity:0.92; }
    .student-hero .status-pill { background:rgba(255,255,255,0.18); border:1px solid rgba(255,255,255,0.3);
                                 padding:4px 12px; border-radius:14px; font-size:11px;
                                 font-weight:700; letter-spacing:0.3px; text-transform:uppercase; }

    .info-card { background:#fff; border:1px solid #E5E7EB; border-radius:12px;
                 margin-bottom:16px; overflow:hidden; }
    .info-card-header { padding:14px 20px; border-bottom:1px solid #E5E7EB; background:#F9FAFB;
                        display:flex; align-items:center; gap:8px; }
    .info-card-header h5 { margin:0; font-size:15px; font-weight:700; color:#111827; }
    .info-card-header i { color:#1E40AF; }

    .info-row { display:grid; grid-template-columns:200px 1fr; padding:12px 20px;
                border-bottom:1px solid #F3F4F6; align-items:start; }
    .info-row:last-child { border-bottom:none; }
    .info-row .label { font-size:11px; font-weight:700; color:#6B7280;
                       text-transform:uppercase; letter-spacing:0.5px; }
    .info-row .value { font-size:14px; color:#111827; line-height:1.55; word-break:break-word; }
    .info-row .value.empty { color:#94A3B8; font-style:italic; }

    .nok-row { padding:14px 20px; border-bottom:1px solid #F3F4F6;
               display:flex; gap:14px; align-items:flex-start; }
    .nok-row:last-child { border-bottom:none; }
    .nok-row .nok-avatar { width:42px; height:42px; border-radius:50%;
                           background:linear-gradient(135deg,#1E40AF,#06B6D4); color:#fff;
                           font-weight:700; font-size:14px;
                           display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .nok-row .nok-name { font-size:14px; font-weight:700; color:#111827; }
    .nok-row .nok-contact { font-size:12px; color:#6B7280; margin:2px 0 0; }
    .pill-primary { background:#1E40AF; color:#fff; font-size:9px; font-weight:700;
                    padding:2px 7px; border-radius:10px; margin-left:6px;
                    text-transform:uppercase; letter-spacing:0.4px; }
    .pill-admin-added { background:#FEF3C7; color:#92400E; font-size:10px; padding:2px 8px;
                        border-radius:10px; margin-top:4px; display:inline-block; }

    .empty-state { text-align:center; padding:30px 20px; color:#6B7280; }
    .empty-state i { font-size:36px; color:#D1D5DB; display:block; margin-bottom:8px; }

    .back-link { color:#1E40AF; text-decoration:none; font-size:14px;
                 display:inline-flex; align-items:center; gap:6px; margin-bottom:12px; }
    .back-link:hover { text-decoration:underline; }
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

    {{-- Hero --}}
    <div class="student-hero">
        <div class="avatar">{{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}</div>
        <div>
            <h3>{{ $student->full_name }}</h3>
            <div class="meta">
                {{ $student->student_id }} ·
                <i class="bi bi-envelope"></i> {{ $student->email }}
            </div>
            <div class="mt-2">
                @php
                    $st = strtolower($student->status ?? 'active');
                    $stLabel = ucfirst($st);
                @endphp
                <span class="status-pill">
                    @if($st === 'deceased')<i class="bi bi-circle-fill" style="font-size:6px;"></i>@else<i class="bi bi-check-circle-fill"></i>@endif
                    {{ $stLabel }}
                </span>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            {{-- Academic --}}
            <div class="info-card">
                <div class="info-card-header">
                    <i class="bi bi-mortarboard-fill"></i>
                    <h5>Academic</h5>
                </div>
                <div class="info-row"><div class="label">Kulliyyah</div><div class="value {{ $student->kulliyyah ? '' : 'empty' }}">{{ $student->kulliyyah ?? 'Not specified' }}</div></div>
                <div class="info-row"><div class="label">Programme</div><div class="value {{ $student->programme ? '' : 'empty' }}">{{ $student->programme ?? 'Not specified' }}</div></div>
                <div class="info-row"><div class="label">Year of Study</div><div class="value {{ $student->year_of_study ? '' : 'empty' }}">{{ $student->year_of_study ? 'Year ' . $student->year_of_study : 'Not specified' }}</div></div>
                <div class="info-row"><div class="label">Mahallah</div><div class="value {{ $student->mahallah ? '' : 'empty' }}">{{ $student->mahallah ?? 'Not specified' }}</div></div>
                <div class="info-row"><div class="label">Enrollment</div><div class="value {{ $student->enrollment_status ? '' : 'empty' }}">{{ $student->enrollment_status ?? 'Not specified' }}</div></div>
                <div class="info-row"><div class="label">Last iMaalum Sync</div><div class="value {{ $student->imaalum_synced_at ? '' : 'empty' }}">{{ $student->imaalum_synced_at?->format('d M Y, H:i') ?? 'Never' }}</div></div>
            </div>

            {{-- Contact --}}
            <div class="info-card">
                <div class="info-card-header">
                    <i class="bi bi-person-lines-fill"></i>
                    <h5>Contact</h5>
                </div>
                <div class="info-row"><div class="label">Email</div><div class="value">{{ $student->email }}</div></div>
                <div class="info-row"><div class="label">Phone</div><div class="value {{ $student->phone ? '' : 'empty' }}">{{ $student->phone ?? 'Not provided' }}</div></div>
                <div class="info-row"><div class="label">Emergency</div><div class="value {{ $student->emergency_contact ? '' : 'empty' }}">{{ $student->emergency_contact ?? 'Not provided' }}</div></div>
            </div>
        </div>

        <div class="col-lg-5">
            {{-- NoK list + admin add --}}
            <div class="info-card">
                <div class="info-card-header">
                    <i class="bi bi-people-fill"></i>
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
                                    <small class="text-muted d-block" style="font-size:11px; margin-top:4px;">
                                        Registered by student
                                    </small>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Admin-add kin form (emergency fallback) --}}
            <div class="info-card">
                <div class="info-card-header">
                    <i class="bi bi-person-plus-fill"></i>
                    <h5>Register a Next of Kin (Admin)</h5>
                </div>
                <div style="padding:16px 20px;">
                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle"></i>
                        Use this when a death event has occurred and the student did not pre-fill
                        kin records. Information should be gathered from the family / death certificate
                        before submitting. This action is recorded in the audit log.
                    </p>
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
                        <button type="submit" class="btn btn-primary btn-sm mt-3">
                            <i class="bi bi-shield-check"></i> Register Next of Kin
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
