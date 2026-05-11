@extends('layouts.student')
@section('title', 'Submit Crisis Report')
@section('page-title', 'Submit a Crisis Report')

@section('content')
<div class="container-fluid py-3">
    <div class="row">
        <div class="col-lg-9">
            <div class="content-card">
                <p class="text-muted">Provide as much detail as possible. Your report will be reviewed by an administrator and you will be notified once a decision is made.</p>

                <form method="POST" action="{{ route('student.crisis.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Crisis Type</label>
                            <select name="crisis_type" class="form-select @error('crisis_type') is-invalid @enderror" required>
                                <option value="">Choose…</option>
                                @foreach(['death','accident','illness','natural_disaster','family_emergency'] as $t)
                                    <option value="{{ $t }}" {{ old('crisis_type')===$t?'selected':'' }}>{{ ucwords(str_replace('_',' ', $t)) }}</option>
                                @endforeach
                            </select>
                            @error('crisis_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Impact Level</label>
                            <select name="impact_level" class="form-select @error('impact_level') is-invalid @enderror" required>
                                @foreach(['low','medium','high','critical'] as $l)
                                    <option value="{{ $l }}" {{ old('impact_level','medium')===$l?'selected':'' }}>{{ ucfirst($l) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" value="{{ old('location') }}" maxlength="255" placeholder="e.g. Gombak Campus / Hometown">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Brief Description of the Crisis <small class="text-muted">(public summary, min 30 chars)</small></label>
                        <textarea name="crisis_description" rows="3" class="form-control @error('crisis_description') is-invalid @enderror" required minlength="30" maxlength="5000">{{ old('crisis_description') }}</textarea>
                        @error('crisis_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Additional Details <small class="text-muted">(optional, kept internal)</small></label>
                        <textarea name="crisis_details" rows="2" class="form-control" maxlength="5000">{{ old('crisis_details') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Your Personal Statement <small class="text-muted">(min 30 chars)</small></label>
                        <textarea name="report_description" rows="4" class="form-control @error('report_description') is-invalid @enderror" required minlength="30" maxlength="5000">{{ old('report_description') }}</textarea>
                        @error('report_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Suggested Donation Target (RM) <small class="text-muted">(optional)</small></label>
                        <input type="number" name="donation_target" min="0" max="1000000" step="100" class="form-control" value="{{ old('donation_target') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Supporting Evidence <small class="text-muted">(up to 5 files, 5MB each — PDF/JPG/PNG/DOC)</small></label>
                        <input type="file" name="supporting_evidence[]" multiple class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        @error('supporting_evidence.*')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Submit Report</button>
                        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="content-card bg-light">
                <h6><i class="bi bi-info-circle"></i> Important</h6>
                <ul class="small text-muted ps-3">
                    <li>All reports are confidential until verified.</li>
                    <li>Verified cases are added to the public dashboard for community support.</li>
                    <li>A blockchain hash is recorded upon verification.</li>
                    <li>You can track the status from your dashboard.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
