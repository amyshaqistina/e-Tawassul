@extends('layouts.admin')
@section('title', 'Death Confirmation #' . $confirmation->confirmation_id)
@section('page-title', 'Death Confirmation #' . $confirmation->confirmation_id)

@section('content')
<div class="container-fluid py-3">
    <a href="{{ route('admin.death.index') }}" class="btn btn-link p-0 mb-3"><i class="bi bi-arrow-left"></i> Back</a>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="content-card">
                <div class="d-flex align-items-center mb-3">
                    @php $sc = ['pending'=>'warning','verified'=>'success','rejected'=>'danger'][$confirmation->status] ?? 'secondary'; @endphp
                    <span class="badge bg-{{ $sc }} fs-6">{{ strtoupper($confirmation->status) }}</span>
                    <small class="text-muted ms-auto">Submitted {{ $confirmation->date_triggered?->format('d M Y, h:i A') }}</small>
                </div>

                <h6 class="text-uppercase text-muted small">Student</h6>
                <p class="mb-2"><strong>{{ $confirmation->student?->full_name ?? '—' }}</strong> ({{ $confirmation->student_id }})</p>
                <p class="text-muted small">{{ $confirmation->student?->email }}</p>

                <h6 class="text-uppercase text-muted small mt-3">Submitting next of kin</h6>
                <p class="mb-1"><strong>{{ $confirmation->nextOfKin?->full_name ?? '—' }}</strong> &middot; {{ $confirmation->nextOfKin?->relationship_to_student }}</p>
                <p class="text-muted small mb-0">{{ $confirmation->nextOfKin?->email }} &middot; {{ $confirmation->nextOfKin?->phone }}</p>

                @if($confirmation->media_file_path)
                    <h6 class="text-uppercase text-muted small mt-3">Supporting document</h6>
                    <div class="alert alert-light border">
                        <i class="bi bi-file-earmark-medical"></i>
                        <strong>{{ $confirmation->media_file_name }}</strong>
                        <small class="text-muted ms-2">({{ number_format(($confirmation->media_file_size ?? 0)/1024, 1) }} KB)</small>
                        <div class="small text-muted mt-1">Stored encrypted on the server. Verify offline before approval.</div>
                    </div>
                @endif

                @if($confirmation->admin_comments)
                    <h6 class="text-uppercase text-muted small">Notes</h6>
                    <p class="alert alert-light border">{{ $confirmation->admin_comments }}</p>
                @endif

                @if($confirmation->blockchain_reference)
                    <h6 class="text-uppercase text-muted small">Blockchain record</h6>
                    <x-blockchain-badge :hash="$confirmation->blockchain_reference" />
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            @if($confirmation->status === 'pending')
                <div class="content-card border-success">
                    <h5 class="text-success"><i class="bi bi-shield-check"></i> Verify Confirmation</h5>
                    <p class="small text-muted">Verifying will mark the student as deceased and record the event on the audit chain. Any LDMS messages can then be released.</p>
                    <form method="POST" action="{{ route('admin.death.verify', $confirmation->confirmation_id) }}">
                        @csrf
                        <input type="hidden" name="decision" value="verified">
                        <div class="mb-2">
                            <label class="form-label small">Admin comments (optional)</label>
                            <textarea name="admin_comments" rows="2" maxlength="2000" class="form-control form-control-sm"></textarea>
                        </div>
                        <button class="btn btn-success w-100"><i class="bi bi-check-circle"></i> Approve & Record</button>
                    </form>
                </div>

                <div class="content-card border-danger mt-3">
                    <h5 class="text-danger"><i class="bi bi-x-circle"></i> Reject</h5>
                    <form method="POST" action="{{ route('admin.death.verify', $confirmation->confirmation_id) }}">
                        @csrf
                        <input type="hidden" name="decision" value="rejected">
                        <div class="mb-2">
                            <textarea name="admin_comments" rows="3" maxlength="2000" class="form-control form-control-sm" placeholder="Reason / next steps"></textarea>
                        </div>
                        <button class="btn btn-outline-danger w-100">Reject</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
