@extends('layouts.student')
@section('title', 'My Profile')
@section('page-title', 'My Profile')
@section('page-subtitle', 'Your personal and academic details')

@section('content')
    <style>
        .profile-wrap {
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            min-width: 0 !important;
            max-width: 100% !important;
        }

        .profile-wrap>* {
            min-width: 0 !important;
        }

        /* ===== Profile Hero Card (left) ===== */
        .profile-hero {
            background: linear-gradient(135deg, #1a56db, #06b6d4) !important;
            border-radius: 16px !important;
            padding: 32px 24px !important;
            text-align: center !important;
            color: #fff !important;
            position: relative !important;
            overflow: hidden !important;
            box-shadow: 0 8px 24px rgba(26, 86, 219, 0.18) !important;
        }

        .profile-hero::before {
            content: '';
            position: absolute;
            right: -50px;
            top: -50px;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
        }

        .profile-hero::after {
            content: '';
            position: absolute;
            left: -40px;
            bottom: -40px;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
        }

        .profile-hero>* {
            position: relative;
            z-index: 1;
        }

        .profile-avatar-lg {
            width: 96px !important;
            height: 96px !important;
            border-radius: 50% !important;
            background: rgba(255, 255, 255, 0.18) !important;
            border: 3px solid rgba(255, 255, 255, 0.3) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 36px !important;
            font-weight: 800 !important;
            color: #fff !important;
            margin: 0 auto 16px !important;
        }

        .profile-hero h3 {
            color: #fff !important;
            font-size: 20px !important;
            font-weight: 800 !important;
            margin: 0 0 4px 0 !important;
        }

        .profile-hero-id {
            color: rgba(255, 255, 255, 0.85) !important;
            font-size: 13px !important;
            margin: 0 0 14px 0 !important;
        }

        .profile-status-badge {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            background: rgba(255, 255, 255, 0.18) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            color: #fff !important;
            padding: 5px 14px !important;
            border-radius: 20px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
        }

        /* ===== Info Cards ===== */
        .info-card {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            padding: 22px 24px !important;
            margin-bottom: 16px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03) !important;
        }

        .info-card-header {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            margin-bottom: 18px !important;
            padding-bottom: 14px !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }

        .info-card-header-icon {
            width: 36px !important;
            height: 36px !important;
            border-radius: 9px !important;
            background: #eff6ff !important;
            color: #1a56db !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 16px !important;
            flex-shrink: 0 !important;
        }

        .info-card-header h5 {
            font-size: 15px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            margin: 0 !important;
        }

        /* ===== Field Rows ===== */
        .field-row {
            display: flex !important;
            align-items: flex-start !important;
            gap: 14px !important;
            padding: 11px 0 !important;
            border-bottom: 1px solid #f8fafc !important;
        }

        .field-row:last-child {
            border-bottom: none !important;
        }

        .field-icon {
            width: 34px !important;
            height: 34px !important;
            border-radius: 8px !important;
            background: #f1f5f9 !important;
            color: #1a56db !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 14px !important;
            flex-shrink: 0 !important;
        }

        .field-label {
            flex: 0 0 130px !important;
            font-size: 12.5px !important;
            color: #64748b !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.3px !important;
            padding-top: 8px !important;
        }

        .field-value {
            flex: 1 !important;
            font-size: 14px !important;
            color: #0f172a !important;
            font-weight: 500 !important;
            padding-top: 6px !important;
            word-break: break-word !important;
        }

        .field-value-empty {
            color: #94a3b8 !important;
            font-style: italic !important;
            font-weight: 400 !important;
        }

        /* ===== NOK Card ===== */
        .nok-item {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            padding: 12px 14px !important;
            background: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            margin-bottom: 8px !important;
        }

        .nok-item:last-child {
            margin-bottom: 0 !important;
        }

        .nok-avatar {
            width: 40px !important;
            height: 40px !important;
            border-radius: 50% !important;
            background: linear-gradient(135deg, #1a56db, #06b6d4) !important;
            color: #fff !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-weight: 700 !important;
            font-size: 14px !important;
            flex-shrink: 0 !important;
        }

        .nok-info {
            flex: 1;
            min-width: 0;
        }

        .nok-name {
            font-size: 14px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            margin: 0 0 2px 0 !important;
        }

        .nok-name small {
            font-weight: 500 !important;
            color: #64748b !important;
        }

        .nok-contact {
            font-size: 12px !important;
            color: #64748b !important;
            margin: 0 !important;
        }

        .nok-badge {
            font-size: 11px !important;
            font-weight: 600 !important;
            padding: 4px 10px !important;
            border-radius: 14px !important;
            flex-shrink: 0 !important;
        }

        .nok-badge.verified {
            background: #ecfdf5 !important;
            color: #059669 !important;
            border: 1px solid #a7f3d0 !important;
        }

        .nok-badge.pending {
            background: #fef9c3 !important;
            color: #b45309 !important;
            border: 1px solid #fde68a !important;
        }

        .sync-note {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 10px 12px !important;
            background: #eff6ff !important;
            border: 1px solid #bfdbfe !important;
            border-radius: 8px !important;
            margin-top: 14px !important;
            font-size: 11.5px !important;
            color: #1e40af !important;
        }

        .sync-note i {
            font-size: 13px !important;
        }

        @media (max-width: 768px) {
            .field-label {
                flex: 0 0 100px !important;
                font-size: 11px !important;
            }
        }
    </style>

    <div class="profile-wrap container-fluid py-3">
        <div class="row g-3">
            {{-- ===== Left: Profile Hero Card ===== --}}
            <div class="col-lg-4">
                <div class="profile-hero">
                    <div class="profile-avatar-lg">
                        {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                    </div>
                    <h3>{{ $student->full_name }}</h3>
                    <p class="profile-hero-id">
                        <i class="bi bi-person-vcard"></i> {{ $student->student_id }}
                    </p>
                    <span class="profile-status-badge">
                        <i class="bi bi-check-circle-fill"></i> {{ ucfirst($student->status) }}
                    </span>
                </div>
            </div>

            {{-- ===== Right: Info Cards ===== --}}
            <div class="col-lg-8">
                {{-- Flash + errors --}}
                @if(session('status'))
                    <div class="alert alert-success py-2 small mb-3">
                        <i class="bi bi-check-circle-fill"></i> {{ session('status') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger py-2 small mb-3">
                        @foreach($errors->all() as $err)<div><i class="bi bi-exclamation-triangle-fill"></i> {{ $err }}</div>@endforeach
                    </div>
                @endif

                {{-- Academic Info --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-card-header-icon">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <h5>Academic Information</h5>
                        <button type="button" class="btn btn-link btn-sm ms-auto p-0" onclick="toggleEdit('academic')" id="academic-toggle">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>
                    </div>

                    <div id="academic-view">
                        <div class="field-row">
                            <div class="field-icon"><i class="bi bi-bank"></i></div>
                            <div class="field-label">Kulliyyah</div>
                            <div class="field-value {{ !$student->kulliyyah ? 'field-value-empty' : '' }}">
                                {{ $student->kulliyyah ?? 'Not specified' }}
                                <small class="text-muted d-block" style="font-size:11px; margin-top:2px;">From iMaalum — not editable</small>
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-icon"><i class="bi bi-book-fill"></i></div>
                            <div class="field-label">Programme</div>
                            <div class="field-value {{ !$student->programme ? 'field-value-empty' : '' }}">
                                {{ $student->programme ?? 'Not specified' }}
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-icon"><i class="bi bi-calendar3"></i></div>
                            <div class="field-label">Year of Study</div>
                            <div class="field-value {{ !$student->year_of_study ? 'field-value-empty' : '' }}">
                                {{ $student->year_of_study ? 'Year ' . $student->year_of_study : 'Not specified' }}
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-icon"><i class="bi bi-house-door-fill"></i></div>
                            <div class="field-label">Mahallah</div>
                            <div class="field-value {{ !$student->mahallah ? 'field-value-empty' : '' }}">
                                {{ $student->mahallah ?? 'Not specified' }}
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-icon"><i class="bi bi-clock-fill"></i></div>
                            <div class="field-label">Enrollment</div>
                            <div class="field-value {{ !$student->enrollment_status ? 'field-value-empty' : '' }}">
                                {{ $student->enrollment_status ?? 'Not specified' }}
                            </div>
                        </div>
                    </div>

                    {{-- Edit form (hidden by default) --}}
                    <form id="academic-edit" method="POST" action="{{ route('student.profile.update') }}" style="display:none;">
                        @csrf
                        @method('PATCH')
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Programme</label>
                            <input type="text" name="programme" class="form-control form-control-sm" value="{{ old('programme', $student->programme) }}" placeholder="e.g. Bachelor of Information Systems">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Year of Study</label>
                            <select name="year_of_study" class="form-select form-select-sm">
                                <option value="">— Not specified —</option>
                                @foreach(['1','2','3','4','5','6'] as $y)
                                    <option value="{{ $y }}" {{ (string) old('year_of_study', $student->year_of_study) === $y ? 'selected' : '' }}>Year {{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Mahallah</label>
                            <input type="text" name="mahallah" class="form-control form-control-sm" value="{{ old('mahallah', $student->mahallah) }}" placeholder="e.g. Mahallah Salahuddin">
                        </div>
                        {{-- Carry contact fields through so they're not wiped --}}
                        <input type="hidden" name="phone" value="{{ $student->phone }}">
                        <input type="hidden" name="emergency_contact" value="{{ $student->emergency_contact }}">

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check2"></i> Save
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleEdit('academic')">Cancel</button>
                        </div>
                    </form>

                    @if ($student->imaalum_synced_at)
                        <div class="sync-note">
                            <i class="bi bi-arrow-clockwise"></i>
                            Last synced from iMaalum {{ $student->imaalum_synced_at->diffForHumans() }}.
                            Your edits are preserved on future syncs.
                        </div>
                    @endif
                </div>

                {{-- Contact Info --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-card-header-icon">
                            <i class="bi bi-person-lines-fill"></i>
                        </div>
                        <h5>Contact Information</h5>
                        <button type="button" class="btn btn-link btn-sm ms-auto p-0" onclick="toggleEdit('contact')" id="contact-toggle">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>
                    </div>

                    <div id="contact-view">
                        <div class="field-row">
                            <div class="field-icon"><i class="bi bi-envelope-fill"></i></div>
                            <div class="field-label">Email</div>
                            <div class="field-value">
                                {{ $student->email }}
                                <small class="text-muted d-block" style="font-size:11px; margin-top:2px;">From iMaalum — not editable</small>
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-icon"><i class="bi bi-telephone-fill"></i></div>
                            <div class="field-label">Phone</div>
                            <div class="field-value {{ !$student->phone ? 'field-value-empty' : '' }}">
                                {{ $student->phone ?? 'Not provided' }}
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-icon" style="background:#fef2f2 !important; color:#dc2626 !important;">
                                <i class="bi bi-telephone-plus-fill"></i>
                            </div>
                            <div class="field-label">Emergency</div>
                            <div class="field-value {{ !$student->emergency_contact ? 'field-value-empty' : '' }}">
                                {{ $student->emergency_contact ?? 'Not provided' }}
                            </div>
                        </div>
                    </div>

                    <form id="contact-edit" method="POST" action="{{ route('student.profile.update') }}" style="display:none;">
                        @csrf
                        @method('PATCH')
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Phone</label>
                            <input type="tel" name="phone" class="form-control form-control-sm"
                                   value="{{ old('phone', $student->phone) }}" placeholder="+60123456789">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Emergency Contact</label>
                            <input type="tel" name="emergency_contact" class="form-control form-control-sm"
                                   value="{{ old('emergency_contact', $student->emergency_contact) }}" placeholder="+60198765432">
                            <small class="text-muted">A 24-hour contact number for serious emergencies.</small>
                        </div>
                        {{-- Carry academic fields through --}}
                        <input type="hidden" name="mahallah" value="{{ $student->mahallah }}">
                        <input type="hidden" name="programme" value="{{ $student->programme }}">
                        <input type="hidden" name="year_of_study" value="{{ $student->year_of_study }}">

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check2"></i> Save
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleEdit('contact')">Cancel</button>
                        </div>
                    </form>
                </div>

                {{-- Next of Kin (CRUD) --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-card-header-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h5>Registered Next of Kin</h5>
                        <button type="button" class="btn btn-primary btn-sm ms-auto" onclick="toggleAddKin()">
                            <i class="bi bi-plus-lg"></i> Add Kin
                        </button>
                    </div>

                    {{-- Warning if no kin yet --}}
                    @if($student->nextOfKin->isEmpty())
                        <div style="padding:14px; background:#FFFBEB; border:1px solid #FCD34D; border-radius:8px; margin-bottom:14px;">
                            <div style="display:flex; align-items:flex-start; gap:10px;">
                                <i class="bi bi-exclamation-triangle-fill" style="color:#F59E0B; font-size:20px; flex-shrink:0; margin-top:2px;"></i>
                                <div>
                                    <strong style="color:#92400E; display:block; margin-bottom:4px;">Please add at least one next of kin</strong>
                                    <small style="color:#78350F; line-height:1.5;">
                                        In an emergency, your kin can use this platform to coordinate
                                        crisis response and receive any final messages you've left.
                                        Without a kin on file, your family cannot easily access the
                                        system on your behalf.
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Add-kin form (hidden by default) --}}
                    <form id="add-kin-form" method="POST" action="{{ route('student.kin.store') }}" style="display:none; padding:14px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; margin-bottom:12px;">
                        @csrf
                        <h6 class="mb-3 small fw-bold text-uppercase text-muted">Add a New Next of Kin</h6>
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
                                <input type="tel" name="phone" class="form-control form-control-sm" value="{{ old('phone') }}" placeholder="+60123456789" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold mb-1">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email') }}" placeholder="kin@example.com" required>
                                <small class="text-muted">Used for OTP login and notifications. Must be unique in our system.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold mb-1">Address (optional)</label>
                                <textarea name="address" class="form-control form-control-sm" rows="2" placeholder="Postal address">{{ old('address') }}</textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_primary" value="1" id="kin-primary-add">
                                    <label class="form-check-label small" for="kin-primary-add">
                                        Set as primary emergency contact
                                        @if($student->nextOfKin->isEmpty())
                                            <small class="text-muted d-block">First kin you add is automatically primary.</small>
                                        @endif
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check2"></i> Add Next of Kin
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleAddKin()">Cancel</button>
                        </div>
                    </form>

                    @forelse($student->nextOfKin as $nok)
                        <div class="nok-item" style="flex-wrap:wrap;">
                            <div class="nok-avatar">{{ strtoupper(substr($nok->full_name, 0, 1)) }}</div>
                            <div class="nok-info">
                                <div class="nok-name">
                                    {{ $nok->full_name }}
                                    <small>({{ $nok->relationship_to_student }})</small>
                                    @if($nok->is_primary)
                                        <span style="background:#1E40AF; color:#fff; font-size:9px; font-weight:700; padding:2px 7px; border-radius:10px; margin-left:6px; vertical-align:middle;">
                                            <i class="bi bi-star-fill"></i> PRIMARY
                                        </span>
                                    @endif
                                </div>
                                <p class="nok-contact">
                                    <i class="bi bi-envelope"></i> {{ $nok->email }}
                                    &nbsp;&middot;&nbsp;
                                    <i class="bi bi-telephone"></i> {{ $nok->phone }}
                                </p>
                                @if($nok->address)
                                    <p class="nok-contact"><i class="bi bi-geo-alt"></i> {{ \Illuminate\Support\Str::limit($nok->address, 80) }}</p>
                                @endif
                                @if($nok->registered_by === 'admin')
                                    <p class="nok-contact" style="color:#92400E;">
                                        <i class="bi bi-shield-check"></i> Added by administrator on {{ $nok->registered_at?->format('d M Y') }}
                                    </p>
                                @endif
                            </div>
                            <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-end;">
                                @if(!$nok->is_primary)
                                    <form method="POST" action="{{ route('student.kin.primary', $nok->nok_id) }}" style="display:inline; margin:0;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-link btn-sm p-0" style="font-size:11px;">
                                            <i class="bi bi-star"></i> Make primary
                                        </button>
                                    </form>
                                @endif
                                <button type="button" class="btn btn-link btn-sm p-0" style="font-size:11px;" onclick="toggleKinEdit({{ $nok->nok_id }})">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <form method="POST" action="{{ route('student.kin.destroy', $nok->nok_id) }}" style="display:inline; margin:0;"
                                      onsubmit="return confirm('Remove {{ addslashes($nok->full_name) }} from your next of kin? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm p-0 text-danger" style="font-size:11px;">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </form>
                            </div>

                            {{-- Edit form (hidden) — appears below the row --}}
                            <form id="kin-edit-{{ $nok->nok_id }}" method="POST" action="{{ route('student.kin.update', $nok->nok_id) }}"
                                  style="display:none; width:100%; margin-top:10px; padding:12px; background:#F8FAFC; border-radius:8px; border:1px solid #E2E8F0;">
                                @csrf
                                @method('PATCH')
                                @php $locked = $nok->deathConfirmations()->exists(); @endphp

                                @if($locked)
                                    <div class="alert alert-warning py-2 small mb-2">
                                        <i class="bi bi-lock"></i> Identity fields locked — this kin already submitted records. Phone/address still editable.
                                    </div>
                                @endif

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold mb-1">First Name</label>
                                        <input type="text" name="first_name" class="form-control form-control-sm" value="{{ $nok->first_name }}" {{ $locked ? 'readonly' : 'required' }}>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold mb-1">Last Name</label>
                                        <input type="text" name="last_name" class="form-control form-control-sm" value="{{ $nok->last_name }}" {{ $locked ? 'readonly' : 'required' }}>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold mb-1">Relationship</label>
                                        <input type="text" name="relationship_to_student" class="form-control form-control-sm" value="{{ $nok->relationship_to_student }}" {{ $locked ? 'readonly' : 'required' }}>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold mb-1">Phone</label>
                                        <input type="tel" name="phone" class="form-control form-control-sm" value="{{ $nok->phone }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold mb-1">Email</label>
                                        <input type="email" name="email" class="form-control form-control-sm" value="{{ $nok->email }}" {{ $locked ? 'readonly' : 'required' }}>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold mb-1">Address</label>
                                        <textarea name="address" class="form-control form-control-sm" rows="2">{{ $nok->address }}</textarea>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mt-2">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check2"></i> Save</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleKinEdit({{ $nok->nok_id }})">Cancel</button>
                                </div>
                            </form>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 24px 12px; color: #94a3b8;">
                            <i class="bi bi-people" style="font-size: 32px; opacity: 0.3; display: block; margin-bottom: 8px;"></i>
                            <p style="font-size: 13px; margin: 0;">No next of kin registered yet. Click <strong>Add Kin</strong> above to get started.</p>
                        </div>
                    @endforelse
                </div>

                {{-- ======================================================
                     Donation Receiving — Phase 3b
                     If filled in, donors see this on your public donate
                     page when your crisis is verified.
                ====================================================== --}}
                <div class="info-card" id="donation-receiving">
                    <div class="info-card-header">
                        <div class="info-card-header-icon" style="background:#ECFDF5 !important; color:#065F46 !important;">
                            <i class="bi bi-bank2"></i>
                        </div>
                        <h5>Donation Receiving</h5>
                        <button type="button" class="btn btn-link btn-sm ms-auto p-0" onclick="toggleEdit('bank')" id="bank-toggle">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>
                    </div>

                    <div style="padding:0 4px 10px; font-size:12.5px; color:#6B7280; line-height:1.55;">
                        <i class="bi bi-info-circle"></i>
                        Optional. If you fill these in, donors visiting your verified crisis page can
                        transfer directly to your account. Your account number is encrypted at rest
                        and only the last 4 digits show by default on the public page.
                    </div>

                    <div id="bank-view">
                        <div class="field-row">
                            <div class="field-icon" style="background:#ECFDF5 !important; color:#065F46 !important;">
                                <i class="bi bi-bank"></i>
                            </div>
                            <div class="field-label">Bank</div>
                            <div class="field-value {{ !$student->bank_name ? 'field-value-empty' : '' }}">
                                {{ $student->bank_name ?? 'Not provided' }}
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-icon"><i class="bi bi-person-fill"></i></div>
                            <div class="field-label">Holder</div>
                            <div class="field-value {{ !$student->bank_account_holder ? 'field-value-empty' : '' }}">
                                {{ $student->bank_account_holder ?? 'Not provided' }}
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-icon"><i class="bi bi-credit-card-fill"></i></div>
                            <div class="field-label">Account No.</div>
                            <div class="field-value {{ !$student->bank_account_number ? 'field-value-empty' : '' }}">
                                @if($student->bank_account_number)
                                    {{ $student->bank_account_masked }}
                                    <small class="text-muted d-block" style="font-size:11px;">
                                        Stored encrypted. Donors see masked version by default.
                                    </small>
                                @else
                                    Not provided
                                @endif
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-icon"><i class="bi bi-qr-code"></i></div>
                            <div class="field-label">DuitNow QR</div>
                            <div class="field-value">
                                @if($student->qr_code_path)
                                    <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                                        <img src="{{ asset('storage/' . $student->qr_code_path) }}"
                                             alt="Your DuitNow QR"
                                             style="max-width:120px; max-height:120px; border:1px solid #E5E7EB; border-radius:8px; padding:4px; background:#fff;">
                                        <form method="POST" action="{{ route('student.qr.delete') }}" style="margin:0;"
                                              onsubmit="return confirm('Remove the QR code? You can upload a new one anytime.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link btn-sm p-0 text-danger" style="font-size:12px;">
                                                <i class="bi bi-trash"></i> Remove QR
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="field-value-empty">Not uploaded</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Edit form (hidden by default) --}}
                    <form id="bank-edit" method="POST" action="{{ route('student.bank.update') }}" style="display:none;">
                        @csrf
                        @method('PATCH')

                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Bank Name</label>
                            <select name="bank_name" class="form-select form-select-sm">
                                <option value="">— Not specified —</option>
                                @foreach(['Maybank','CIMB Bank','Public Bank','RHB Bank','Hong Leong Bank',
                                          'AmBank','Bank Islam','Bank Rakyat','OCBC','HSBC',
                                          'Standard Chartered','BSN','Affin Bank','Alliance Bank',
                                          'MBSB Bank','Agrobank','Citibank','UOB Bank'] as $bankOption)
                                    <option value="{{ $bankOption }}"
                                            {{ old('bank_name', $student->bank_name) === $bankOption ? 'selected' : '' }}>
                                        {{ $bankOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Account Holder Name</label>
                            <input type="text" name="bank_account_holder" class="form-control form-control-sm"
                                   value="{{ old('bank_account_holder', $student->bank_account_holder) }}"
                                   placeholder="As it appears on your bank statement">
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Account Number</label>
                            <input type="text" name="bank_account_number" class="form-control form-control-sm"
                                   value="{{ old('bank_account_number', $student->bank_account_number) }}"
                                   placeholder="1234567890123" pattern="[0-9 \-]+">
                            <small class="text-muted">Digits only. Stored encrypted. Donors see masked version.</small>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check2"></i> Save Bank Details
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleEdit('bank')">Cancel</button>
                        </div>
                    </form>

                    {{-- QR upload form (always visible — small) --}}
                    <div style="padding:14px 4px 4px; border-top:1px solid #F3F4F6; margin-top:8px;">
                        <form method="POST" action="{{ route('student.qr.upload') }}" enctype="multipart/form-data">
                            @csrf
                            <label class="form-label small fw-semibold mb-1">
                                {{ $student->qr_code_path ? 'Replace DuitNow QR' : 'Upload DuitNow QR' }}
                            </label>
                            <div class="d-flex gap-2 align-items-stretch">
                                <input type="file" name="qr_code" class="form-control form-control-sm"
                                       accept="image/png,image/jpeg,image/jpg" required>
                                <button type="submit" class="btn btn-primary btn-sm" style="white-space:nowrap;">
                                    <i class="bi bi-upload"></i> Upload
                                </button>
                            </div>
                            <small class="text-muted">PNG or JPG. Max 2 MB. Generate from your banking app's DuitNow feature.</small>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle profile section view <-> edit
        function toggleEdit(section) {
            const view = document.getElementById(section + '-view');
            const form = document.getElementById(section + '-edit');
            const btn  = document.getElementById(section + '-toggle');
            if (view.style.display === 'none') {
                view.style.display = '';
                form.style.display = 'none';
                btn.innerHTML = '<i class="bi bi-pencil-square"></i> Edit';
            } else {
                view.style.display = 'none';
                form.style.display = 'block';
                btn.innerHTML = '<i class="bi bi-x-lg"></i> Cancel';
            }
        }

        function toggleAddKin() {
            const form = document.getElementById('add-kin-form');
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
            if (form.style.display === 'block') {
                form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                form.querySelector('input[name="first_name"]')?.focus();
            }
        }

        function toggleKinEdit(id) {
            const form = document.getElementById('kin-edit-' + id);
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
            if (form.style.display === 'block') {
                form.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        // Auto-open add-kin form if validation failed for it
        @if($errors->any() && (old('first_name') || old('email')))
            document.addEventListener('DOMContentLoaded', () => toggleAddKin());
        @endif
    </script>
@endsection
