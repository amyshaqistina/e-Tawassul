@extends('layouts.student')
@section('title', 'Create Last Digital Message')
@section('page-title', 'Create Last Digital Message')

@section('content')
<div class="container-fluid py-3">
    <div class="row">
        <div class="col-lg-8">
            <div class="content-card">
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle"></i>
                    Your message will be encrypted at rest and only released to your registered next of kin
                    once a death confirmation has been verified by an administrator.
                </div>

                @include('student.ldms._form', [
                    'action' => route('student.ldms.store'),
                    'method' => 'POST',
                    'ldms'   => null,
                    'submitLabel' => 'Save Encrypted',
                ])
            </div>
        </div>
        <div class="col-lg-4">
            <div class="content-card bg-light">
                <h6><i class="bi bi-shield-lock"></i> Security</h6>
                <ul class="small text-muted ps-3 mb-0">
                    <li>Encrypted using Laravel's built-in AES encryption.</li>
                    <li>Audio captured via your browser is uploaded as a webm/ogg file.</li>
                    <li>Photos, videos, and documents are stored encrypted on disk.</li>
                    <li>Only the verified next of kin will be able to view this after release.</li>
                </ul>
            </div>
            <div class="content-card mt-3">
                <h6 class="text-uppercase text-muted small mb-2"><i class="bi bi-question-circle"></i> What can I leave?</h6>
                <ul class="small text-muted ps-3 mb-0">
                    <li><strong>Written letter</strong> — a final note in your own words.</li>
                    <li><strong>Voice recording</strong> — record on-device or upload an audio file.</li>
                    <li><strong>Photos</strong> — upload from gallery or take a photo right now.</li>
                    <li><strong>Documents</strong> — PDFs (with preview) or Word docs.</li>
                    <li><strong>Video</strong> — pre-recorded MP4 or record on the spot.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
