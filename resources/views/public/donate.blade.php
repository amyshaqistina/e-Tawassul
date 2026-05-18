@extends('layouts.public')
@section('title', 'Donate to Case #' . $crisis->crisis_id)

@push('styles')
<style>
    .donate-wrap { max-width:1100px; margin:0 auto; }

    /* === Case summary card === */
    .case-card { background:#fff; border:1px solid #E5E7EB; border-radius:14px;
                 padding:22px; margin-bottom:18px; }
    .case-card h3 { font-size:22px; font-weight:700; color:#111827; margin:0 0 6px; }
    .case-card .meta { font-size:13px; color:#6B7280; margin-bottom:8px; }
    .case-card .desc { font-size:14px; color:#374151; line-height:1.6; }
    .case-pill { display:inline-block; font-size:11px; font-weight:700;
                 padding:3px 10px; border-radius:10px; text-transform:uppercase;
                 letter-spacing:0.4px; margin-right:6px; }
    .pill-verified { background:#D1FAE5; color:#065F46; }
    .pill-active   { background:#DBEAFE; color:#1E40AF; }

    /* === Direct transfer card === */
    .transfer-card { background:linear-gradient(135deg,#EFF6FF,#FFFFFF);
                     border:1px solid #BFDBFE; border-radius:14px;
                     padding:24px; margin-bottom:18px; }
    .transfer-card h4 { font-size:17px; font-weight:700; color:#1E40AF;
                        margin:0 0 4px; display:flex; align-items:center; gap:8px; }
    .transfer-card .blurb { font-size:13px; color:#6B7280; margin-bottom:18px; }

    .bank-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; align-items:start; }
    @media (max-width:700px) { .bank-grid { grid-template-columns:1fr; } }

    .bank-detail-list { background:#fff; border:1px solid #E5E7EB; border-radius:10px;
                        padding:16px 18px; }
    .bank-row { display:flex; padding:8px 0; border-bottom:1px solid #F3F4F6;
                font-size:13.5px; align-items:center; }
    .bank-row:last-child { border-bottom:none; }
    .bank-row .label { color:#6B7280; flex:0 0 110px; font-weight:600; font-size:11px;
                       text-transform:uppercase; letter-spacing:0.4px; }
    .bank-row .value { color:#111827; font-weight:600; font-family:'Courier New', monospace;
                       flex:1; word-break:break-word; }
    .bank-row .copy-btn { background:#1E40AF; color:#fff; font-size:11px; font-weight:600;
                          border:none; padding:4px 10px; border-radius:5px;
                          cursor:pointer; margin-left:8px; white-space:nowrap; }
    .bank-row .copy-btn:hover { background:#1E3A8A; }
    .bank-row .reveal-btn { background:transparent; color:#1E40AF; font-size:11px;
                            font-weight:600; border:1px solid #1E40AF; padding:3px 9px;
                            border-radius:5px; cursor:pointer; margin-left:8px; }
    .bank-row .reveal-btn:hover { background:#1E40AF; color:#fff; }

    .qr-frame { background:#fff; border:2px dashed #BFDBFE; border-radius:10px;
                padding:14px; text-align:center; }
    .qr-frame img { max-width:100%; max-height:280px; border-radius:6px; }
    .qr-frame .qr-label { font-size:12px; color:#6B7280; margin-top:10px;
                          font-weight:600; text-transform:uppercase; letter-spacing:0.4px; }

    .no-bank-card { background:#FEF3C7; border:1px solid #FCD34D; border-radius:14px;
                    padding:24px; margin-bottom:18px; }
    .no-bank-card h4 { font-size:16px; color:#92400E; font-weight:700; margin:0 0 8px;
                       display:flex; align-items:center; gap:8px; }
    .no-bank-card p { font-size:13.5px; color:#78350F; margin:0 0 4px; line-height:1.55; }
    .no-bank-card a { color:#92400E; font-weight:600; }

    /* === Record-donation form === */
    .record-card { background:#fff; border:1px solid #E5E7EB; border-radius:14px;
                   padding:24px; }
    .record-card h4 { font-size:17px; font-weight:700; color:#111827;
                      margin:0 0 4px; display:flex; align-items:center; gap:8px; }
    .record-card .blurb { font-size:13px; color:#6B7280; margin-bottom:18px; line-height:1.55; }

    .preset-amounts { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:8px; }
    .preset-amount  { background:#F9FAFB; border:1px solid #E5E7EB; border-radius:8px;
                      padding:6px 12px; font-size:13px; cursor:pointer; font-weight:600; }
    .preset-amount:hover  { background:#EFF6FF; border-color:#BFDBFE; }
    .preset-amount.active { background:#1E40AF; color:#fff; border-color:#1E40AF; }

    .btn-record { background:#10B981; color:#fff; font-size:15px; font-weight:700;
                  padding:12px; border-radius:10px; border:none; width:100%;
                  display:flex; align-items:center; justify-content:center; gap:8px; }
    .btn-record:hover { background:#059669; }

    .verified-note { background:#ECFDF5; border-left:3px solid #10B981; border-radius:0 8px 8px 0;
                     padding:10px 14px; font-size:12px; color:#065F46; line-height:1.5;
                     margin-bottom:16px; }

    .back-link { color:#1E40AF; text-decoration:none; font-size:14px;
                 display:inline-flex; align-items:center; gap:6px; margin-bottom:14px; }
    .back-link:hover { text-decoration:underline; }
</style>
@endpush

@section('content')
<section class="container py-4">
    <div class="donate-wrap">
        <a href="{{ route('crisis.show', $crisis->crisis_id) }}" class="back-link">
            <i class="bi bi-arrow-left"></i> Back to case details
        </a>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $err) <div>{{ $err }}</div> @endforeach
            </div>
        @endif

        {{-- Case summary --}}
        <div class="case-card">
            <span class="case-pill pill-verified"><i class="bi bi-shield-check"></i> Verified</span>
            @if($crisis->status === 'active')
                <span class="case-pill pill-active">Active</span>
            @endif
            <h3 class="mt-2">{{ ucwords(str_replace('_', ' ', $crisis->crisis_type)) }}</h3>
            @if($crisis->location)
                <div class="meta"><i class="bi bi-geo-alt"></i> {{ $crisis->location }}</div>
            @endif
            <p class="desc">{{ \Illuminate\Support\Str::limit($crisis->crisis_description, 300) }}</p>
            <hr class="my-3">
            <x-donation-progress :crisis="$crisis" />
        </div>

        @php
            $student = $crisis->student;
            $hasDirect = $student && ($student->bank_account_number || $student->qr_code_path);
        @endphp

        {{-- Direct transfer card (if student has bank info) --}}
        @if($hasDirect)
            <div class="transfer-card">
                <h4><i class="bi bi-bank2 text-primary"></i> Transfer directly to the student</h4>
                <p class="blurb">
                    Your donation goes straight to {{ $student->full_name }}'s account.
                    No platform middleman. After you've transferred, please record your donation
                    in the form below so the case shows it.
                </p>

                <div class="verified-note">
                    <i class="bi bi-shield-check"></i>
                    This case and the recipient's identity have been verified by IIUM administration.
                    The bank details below are confirmed by the student.
                </div>

                <div class="bank-grid">
                    {{-- Left: bank details --}}
                    <div class="bank-detail-list">
                        @if($student->bank_name)
                            <div class="bank-row">
                                <span class="label">Bank</span>
                                <span class="value">{{ $student->bank_name }}</span>
                            </div>
                        @endif

                        @if($student->bank_account_holder)
                            <div class="bank-row">
                                <span class="label">Account Holder</span>
                                <span class="value">{{ $student->bank_account_holder }}</span>
                                <button type="button" class="copy-btn"
                                        onclick="copyToClipboard('{{ addslashes($student->bank_account_holder) }}', this)">
                                    <i class="bi bi-copy"></i> Copy
                                </button>
                            </div>
                        @endif

                        @if($student->bank_account_number)
                            <div class="bank-row">
                                <span class="label">Account No.</span>
                                <span class="value" id="acct-masked">{{ $student->bank_account_masked }}</span>
                                <span class="value" id="acct-full" style="display:none;">{{ $student->bank_account_number }}</span>
                                <button type="button" class="reveal-btn" id="acct-reveal" onclick="revealAccount()">
                                    <i class="bi bi-eye"></i> Show
                                </button>
                                <button type="button" class="copy-btn" id="acct-copy" style="display:none;"
                                        onclick="copyToClipboard('{{ $student->bank_account_number }}', this)">
                                    <i class="bi bi-copy"></i> Copy
                                </button>
                            </div>
                        @endif

                        @if(!$student->bank_account_number && !$student->bank_name)
                            <p class="text-muted small mb-0">No bank details provided — use the QR code instead.</p>
                        @endif
                    </div>

                    {{-- Right: QR code --}}
                    @if($student->qr_code_path)
                        <div class="qr-frame">
                            <img src="{{ asset('storage/' . $student->qr_code_path) }}"
                                 alt="DuitNow QR code for {{ $student->full_name }}">
                            <div class="qr-label"><i class="bi bi-qr-code"></i> DuitNow QR — scan with your banking app</div>
                        </div>
                    @else
                        <div class="qr-frame" style="display:flex; align-items:center; justify-content:center; min-height:200px;">
                            <div style="color:#94A3B8; text-align:center;">
                                <i class="bi bi-qr-code" style="font-size:48px; opacity:0.4; display:block;"></i>
                                <small>No QR code provided</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @else
            {{-- No bank info — fallback message --}}
            <div class="no-bank-card">
                <h4><i class="bi bi-info-circle-fill"></i> Direct donations not yet set up</h4>
                <p>This student hasn't shared their bank details with the platform yet.</p>
                <p>If you'd like to contribute to this case, please contact administration:</p>
                <p>
                    <i class="bi bi-envelope"></i> <a href="mailto:etawassul@iium.edu.my">etawassul@iium.edu.my</a>
                    &middot; <i class="bi bi-telephone"></i> +603-6196-XXXX
                </p>
                <p class="mb-0"><small>Administration will guide you through alternative arrangements.</small></p>
            </div>
        @endif

        {{-- Record the donation form --}}
        @if($hasDirect)
            <div class="record-card">
                <h4><i class="bi bi-pencil-square text-success"></i> Record your donation</h4>
                <p class="blurb">
                    Once your bank transfer is complete, fill this in so the donation appears on the case page
                    and the student knows it arrived. You'll receive a PDF receipt by email.
                </p>

                <form method="POST" action="{{ route('donate.store', $crisis->crisis_id) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Donation Amount (RM) <span class="text-danger">*</span></label>
                        <div class="preset-amounts">
                            @foreach([20, 50, 100, 250, 500, 1000] as $amt)
                                <button type="button" class="preset-amount" data-amount="{{ $amt }}">RM {{ $amt }}</button>
                            @endforeach
                        </div>
                        <input type="number" name="donation_amount" id="donation_amount"
                               class="form-control form-control-lg @error('donation_amount') is-invalid @enderror"
                               min="1" max="1000000" value="{{ old('donation_amount', 100) }}" required>
                        @error('donation_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Your Name <span class="text-danger">*</span></label>
                            <input type="text" name="donor_name" class="form-control @error('donor_name') is-invalid @enderror"
                                   value="{{ old('donor_name') }}" required>
                            @error('donor_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="donor_email" class="form-control @error('donor_email') is-invalid @enderror"
                                   value="{{ old('donor_email') }}" required>
                            <small class="text-muted">Receipt will be sent here.</small>
                            @error('donor_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="bank_transfer" {{ old('payment_method','bank_transfer')==='bank_transfer'?'selected':'' }}>Bank Transfer</option>
                                <option value="duitnow_qr"    {{ old('payment_method')==='duitnow_qr'?'selected':'' }}>DuitNow QR</option>
                                <option value="FPX"           {{ old('payment_method')==='FPX'?'selected':'' }}>FPX (Online Banking)</option>
                                <option value="credit_card"   {{ old('payment_method')==='credit_card'?'selected':'' }}>Credit / Debit Card</option>
                                <option value="wallet"        {{ old('payment_method')==='wallet'?'selected':'' }}>e-Wallet</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Bank Reference (optional)</label>
                            <input type="text" name="transfer_reference" class="form-control"
                                   value="{{ old('transfer_reference') }}"
                                   placeholder="e.g. TXN20260517-9082">
                            <small class="text-muted">From your bank's confirmation slip.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Support Message (optional)</label>
                        <textarea name="support_message" rows="2" maxlength="1000" class="form-control"
                                  placeholder="A short note of support…">{{ old('support_message') }}</textarea>
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" name="anonymous" value="1" id="anon" class="form-check-input">
                        <label for="anon" class="form-check-label small">
                            Donate anonymously — your name will appear as "Anonymous Donor"
                        </label>
                    </div>

                    <button type="submit" class="btn-record">
                        <i class="bi bi-check-circle"></i> I've completed my transfer
                    </button>
                </form>
            </div>
        @endif
    </div>
</section>

@push('scripts')
<script>
    // Preset amount buttons
    document.querySelectorAll('.preset-amount').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.preset-amount').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('donation_amount').value = this.dataset.amount;
        });
    });

    // Reveal full account number
    function revealAccount() {
        document.getElementById('acct-masked').style.display = 'none';
        document.getElementById('acct-full').style.display   = '';
        document.getElementById('acct-reveal').style.display = 'none';
        document.getElementById('acct-copy').style.display   = '';
    }

    // Copy-to-clipboard with visual feedback
    function copyToClipboard(text, btn) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => flashCopied(btn));
        } else {
            // fallback
            const ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); flashCopied(btn); } catch(e) {}
            document.body.removeChild(ta);
        }
    }
    function flashCopied(btn) {
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied';
        btn.style.background = '#10B981';
        setTimeout(() => { btn.innerHTML = original; btn.style.background = ''; }, 1500);
    }
</script>
@endpush
@endsection
