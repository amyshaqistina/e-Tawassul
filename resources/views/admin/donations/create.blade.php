@extends('layouts.admin')
@section('title', 'Add Donation Manually')

@push('styles')
<style>
    .help-card { background:#FEF3C7; border-left:4px solid #F59E0B;
                 border-radius:0 10px 10px 0; padding:14px 18px; margin-bottom:18px; }
    .help-card h6 { color:#92400E; font-weight:700; margin:0 0 6px;
                    display:flex; align-items:center; gap:8px; }
    .help-card p { color:#78350F; font-size:13px; margin:0; line-height:1.55; }
    .help-card ul { color:#78350F; font-size:13px; margin:6px 0 0 22px; padding:0; }

    .form-card { background:#fff; border:1px solid #E5E7EB; border-radius:12px;
                 padding:24px; max-width:780px; }
    .form-card h5 { font-size:16px; font-weight:700; margin:0 0 6px; color:#111827; }

    .back-link { color:#1E40AF; text-decoration:none; font-size:14px;
                 display:inline-flex; align-items:center; gap:6px; margin-bottom:14px; }
</style>
@endpush

@section('content')
<div class="container-fluid pb-3">

    <a href="{{ route('admin.donations.index') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to Donations
    </a>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $err) <div>{{ $err }}</div> @endforeach
        </div>
    @endif

    <div class="help-card">
        <h6><i class="bi bi-info-circle-fill"></i> When to use this form</h6>
        <p>Use this to record a donation that came in <strong>outside</strong> the public donate page. For example:</p>
        <ul>
            <li>Walk-in donor handed you cash in the office</li>
            <li>Someone transferred to a non-platform account by mistake</li>
            <li>You're reconciling a bank statement entry that wasn't recorded by the donor</li>
            <li>Cheque or any other payment method not supported by the public form</li>
        </ul>
    </div>

    <div class="form-card">
        <h5><i class="bi bi-pencil-square text-success"></i> Donation Details</h5>
        <p class="text-muted small mb-3">All recorded actions are logged in the audit trail with your admin ID.</p>

        <form method="POST" action="{{ route('admin.donations.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Crisis Case <span class="text-danger">*</span></label>
                    <select name="crisis_id" class="form-select" required>
                        <option value="">— Choose a crisis —</option>
                        @foreach($crises as $c)
                            <option value="{{ $c->crisis_id }}" {{ old('crisis_id') == $c->crisis_id ? 'selected' : '' }}>
                                #{{ $c->crisis_id }}
                                — {{ \Illuminate\Support\Str::limit(ucwords(str_replace('_',' ', $c->crisis_type)), 30) }}
                                ({{ $c->student?->full_name ?? '—' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Donor Name <span class="text-danger">*</span></label>
                    <input type="text" name="donor_name" class="form-control" value="{{ old('donor_name') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Donor Email <span class="text-muted">(optional)</span></label>
                    <input type="email" name="donor_email" class="form-control" value="{{ old('donor_email') }}"
                           placeholder="If provided, donor will receive a receipt">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Amount (RM) <span class="text-danger">*</span></label>
                    <input type="number" name="donation_amount" class="form-control"
                           min="1" max="1000000" step="0.01"
                           value="{{ old('donation_amount') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                    <select name="payment_method" class="form-select" required>
                        <option value="cash"          {{ old('payment_method')==='cash'?'selected':'' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method')==='bank_transfer'?'selected':'' }}>Bank Transfer</option>
                        <option value="duitnow_qr"    {{ old('payment_method')==='duitnow_qr'?'selected':'' }}>DuitNow QR</option>
                        <option value="FPX"           {{ old('payment_method')==='FPX'?'selected':'' }}>FPX</option>
                        <option value="credit_card"   {{ old('payment_method')==='credit_card'?'selected':'' }}>Credit / Debit Card</option>
                        <option value="wallet"        {{ old('payment_method')==='wallet'?'selected':'' }}>e-Wallet</option>
                        <option value="other"         {{ old('payment_method')==='other'?'selected':'' }}>Other (specify in note)</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Transfer Reference <span class="text-muted">(optional)</span></label>
                    <input type="text" name="transfer_reference" class="form-control"
                           value="{{ old('transfer_reference') }}"
                           placeholder="Bank reference number / cheque number / receipt number">
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Donor Message <span class="text-muted">(optional)</span></label>
                    <textarea name="support_message" rows="2" class="form-control"
                              placeholder="A short note from the donor, if any">{{ old('support_message') }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Admin Note <span class="text-danger">*</span></label>
                    <textarea name="admin_note" rows="3" class="form-control" required
                              placeholder="Explain why this is recorded manually instead of through the public form. e.g. &quot;Walk-in cash, paper receipt #A0023 issued&quot;">{{ old('admin_note') }}</textarea>
                    <small class="text-muted">This is for audit. Stored permanently with your admin ID.</small>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Record Donation
                </button>
                <a href="{{ route('admin.donations.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
