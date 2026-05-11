@extends('layouts.nok')
@section('title', 'Submit Death Confirmation')
@section('page-title', 'Submit Death Confirmation')

@section('content')
<div class="container-fluid py-3">
    <div class="row">
        <div class="col-lg-8">
            <div class="content-card">
                <div class="alert alert-warning small">
                    <i class="bi bi-info-circle"></i>
                    <strong>Inna lillahi wa inna ilayhi raji'un.</strong>
                    Submitting this form will notify IIUM administrators who will verify the information. Once verified, the student's status will be updated and any final messages (LDMS) left for you will be released.
                </div>

                <form method="POST" action="{{ route('nok.death.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student?->student_id }}">

                    <div class="mb-3">
                        <label class="form-label">Student</label>
                        <input type="text" class="form-control" value="{{ $student?->full_name }} ({{ $student?->student_id }})" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Death Certificate / Supporting Document <span class="text-danger">*</span></label>
                        <input type="file" name="media_file" accept=".pdf,.jpg,.jpeg,.png" class="form-control @error('media_file') is-invalid @enderror" required>
                        @error('media_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">PDF or image, max 10MB. Stored encrypted.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Additional Notes (optional)</label>
                        <textarea name="admin_comments" rows="3" maxlength="2000" class="form-control"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="bi bi-shield-lock"></i> Submit for Verification</button>
                    <a href="{{ route('nok.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="content-card bg-light">
                <h6><i class="bi bi-shield-check"></i> What happens next</h6>
                <ol class="small text-muted ps-3 mb-0">
                    <li>Administrators receive your submission for review.</li>
                    <li>You will receive an email once verified or if more information is needed.</li>
                    <li>On verification, an immutable blockchain record is created.</li>
                    <li>Any final messages the student left for you will be released.</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
