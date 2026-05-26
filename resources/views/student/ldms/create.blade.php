@extends('layouts.student')
@section('title', 'Create Last Digital Message')

@push('styles')
<style>
    /* Main card — same style as crisis wizard-card */
    .ldms-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 28px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }

    /* Card header with icon */
    .ldms-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
    }
    .ldms-card-header-icon {
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
    .ldms-card-header h3 {
        margin: 0 0 2px 0;
        font-size: 17px;
        font-weight: 800;
        color: #0f172a;
    }
    .ldms-card-header p {
        margin: 0;
        font-size: 12.5px;
        color: #64748b;
    }

    /* Security info banner — matches crisis ctx-helper.disaster style */
    .ldms-info-banner {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 22px;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .ldms-info-banner i {
        font-size: 16px;
        color: #1a56db;
        margin-top: 1px;
        flex-shrink: 0;
    }
    .ldms-info-banner p {
        margin: 0;
        font-size: 12px;
        color: #1e3a8a;
        line-height: 1.5;
    }

    /* Sidebar cards */
    .ldms-side-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px;
        margin-bottom: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .ldms-side-card-header {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 10px;
    }
    .ldms-side-card-header i {
        font-size: 15px;
        color: #1a56db;
    }
    .ldms-side-card-header h6 {
        margin: 0;
        font-size: 12.5px;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .ldms-side-card ul {
        margin: 0;
        padding-left: 16px;
        font-size: 11.5px;
        color: #64748b;
        line-height: 1.65;
    }
    .ldms-side-card ul li {
        margin-bottom: 4px;
    }
    .ldms-side-card ul li:last-child {
        margin-bottom: 0;
    }

    /* "What you can leave" — icon list */
    .ldms-feature-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .ldms-feature-row {
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .ldms-feature-row i {
        font-size: 15px;
        color: #1a56db;
        margin-top: 1px;
        flex-shrink: 0;
        width: 16px;
        text-align: center;
    }
    .ldms-feature-row-text {
        font-size: 11.5px;
        color: #475569;
        line-height: 1.45;
        flex: 1;
        min-width: 0;
    }
    .ldms-feature-row-text strong {
        color: #0f172a;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="row g-3">

        {{-- ========================================
            LEFT: Main form card
            ======================================== --}}
        <div class="col-lg-8">
            <div class="ldms-card">

                <div class="ldms-card-header">
                    <div class="ldms-card-header-icon">
                        <i class="bi bi-envelope-heart-fill"></i>
                    </div>
                    <div>
                        <h3>Create your last digital message</h3>
                        <p>Encrypted now, released to your registered next of kin later.</p>
                    </div>
                </div>

                <div class="ldms-info-banner">
                    <i class="bi bi-shield-lock-fill"></i>
                    <p>
                        Your message will be encrypted at rest and only released to your registered next of kin
                        once a death confirmation has been verified by an administrator.
                    </p>
                </div>

                @include('student.ldms._form', [
                    'action' => route('student.ldms.store'),
                    'method' => 'POST',
                    'ldms'   => null,
                    'submitLabel' => 'Save Encrypted',
                ])
            </div>
        </div>

        {{-- ========================================
            RIGHT: Sidebar info cards
            ======================================== --}}
        <div class="col-lg-4">

            {{-- Security card --}}
            <div class="ldms-side-card">
                <div class="ldms-side-card-header">
                    <i class="bi bi-shield-lock-fill"></i>
                    <h6>Security</h6>
                </div>
                <ul>
                    <li>Encrypted using Laravel's built-in AES encryption.</li>
                    <li>Audio captured via your browser is uploaded as a webm/ogg file.</li>
                    <li>Photos, videos, and documents are stored encrypted on disk.</li>
                    <li>Only the verified next of kin will be able to view this after release.</li>
                </ul>
            </div>

            {{-- What you can leave card --}}
            <div class="ldms-side-card">
                <div class="ldms-side-card-header">
                    <i class="bi bi-question-circle-fill"></i>
                    <h6>What you can leave</h6>
                </div>
                <div class="ldms-feature-list">
                    <div class="ldms-feature-row">
                        <i class="bi bi-pencil-square"></i>
                        <div class="ldms-feature-row-text">
                            <strong>Written letter</strong> — a final note in your own words.
                        </div>
                    </div>
                    <div class="ldms-feature-row">
                        <i class="bi bi-mic-fill"></i>
                        <div class="ldms-feature-row-text">
                            <strong>Voice recording</strong> — record on-device or upload an audio file.
                        </div>
                    </div>
                    <div class="ldms-feature-row">
                        <i class="bi bi-image-fill"></i>
                        <div class="ldms-feature-row-text">
                            <strong>Photos</strong> — upload from gallery or take a photo right now.
                        </div>
                    </div>
                    <div class="ldms-feature-row">
                        <i class="bi bi-file-earmark-text-fill"></i>
                        <div class="ldms-feature-row-text">
                            <strong>Documents</strong> — PDFs (with preview) or Word docs.
                        </div>
                    </div>
                    <div class="ldms-feature-row">
                        <i class="bi bi-camera-video-fill"></i>
                        <div class="ldms-feature-row-text">
                            <strong>Video</strong> — pre-recorded MP4 or record on the spot.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
