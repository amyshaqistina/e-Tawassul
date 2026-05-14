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
                {{-- Academic Info --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-card-header-icon">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <h5>Academic Information</h5>
                    </div>

                    <div class="field-row">
                        <div class="field-icon"><i class="bi bi-bank"></i></div>
                        <div class="field-label">Kulliyyah</div>
                        <div class="field-value {{ !$student->kulliyyah ? 'field-value-empty' : '' }}">
                            {{ $student->kulliyyah ?? 'Not specified' }}
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

                    @if ($student->imaalum_synced_at)
                        <div class="sync-note">
                            <i class="bi bi-arrow-clockwise"></i>
                            Last synced from iMaalum {{ $student->imaalum_synced_at->diffForHumans() }}
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
                    </div>

                    <div class="field-row">
                        <div class="field-icon"><i class="bi bi-envelope-fill"></i></div>
                        <div class="field-label">Email</div>
                        <div class="field-value">{{ $student->email }}</div>
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

                {{-- Next of Kin --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-card-header-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h5>Registered Next of Kin</h5>
                    </div>

                    @forelse($student->nextOfKin as $nok)
                        <div class="nok-item">
                            <div class="nok-avatar">{{ strtoupper(substr($nok->full_name, 0, 1)) }}</div>
                            <div class="nok-info">
                                <div class="nok-name">
                                    {{ $nok->full_name }}
                                    <small>({{ $nok->relationship_to_student }})</small>
                                </div>
                                <p class="nok-contact">
                                    <i class="bi bi-envelope"></i> {{ $nok->email }}
                                    &nbsp;&middot;&nbsp;
                                    <i class="bi bi-telephone"></i> {{ $nok->phone }}
                                </p>
                            </div>
                            <div>
                                @if ($nok->emergency_contact_verified)
                                    <span class="nok-badge verified"><i class="bi bi-patch-check-fill"></i> Verified</span>
                                @else
                                    <span class="nok-badge pending"><i class="bi bi-clock-history"></i> Pending</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 24px 12px; color: #94a3b8;">
                            <i class="bi bi-people"
                                style="font-size: 32px; opacity: 0.3; display: block; margin-bottom: 8px;"></i>
                            <p style="font-size: 13px; margin: 0;">No next of kin registered yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
