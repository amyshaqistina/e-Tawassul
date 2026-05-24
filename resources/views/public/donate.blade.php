@extends('layouts.public')
@section('title', 'Donate to Case #' . $crisis->crisis_id)

{{--
    Receives from DonationController::create():
        $crisis      App\Models\Crisis (with student loaded)
        $isClosed    bool — true when donations are NOT being accepted
        $closedKind  string — 'goal_reached' or 'admin_closed'
--}}

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
    .pill-closed   { background:#E5E7EB; color:#374151; }

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
    .qr-frame img { max-width:100%; height:auto; display:block; margin:0 auto; }
    .qr-label { color:#1E40AF; font-weight:600; font-size:12px; margin-top:8px;
                display:flex; align-items:center; justify-content:center; gap:5px; }

    .verified-note { background:#F0FDF4; border:1px solid #BBF7D0; color:#15803d;
                     font-size:12px; padding:9px 12px; border-radius:8px;
                     margin-bottom:14px; display:flex; align-items:flex-start; gap:7px;
                     line-height:1.5; }
    .verified-note i { margin-top:1px; }

    /* === No-bank fallback === */
    .no-bank-card { background:#FEF3C7; border:1px solid #FCD34D; border-radius:14px;
                    padding:22px; margin-bottom:18px; color:#78350F; }
    .no-bank-card h4 { font-size:16px; font-weight:700; color:#92400E; margin:0 0 8px;
                       display:flex; align-items:center; gap:8px; }
    .no-bank-card p  { margin:6px 0; font-size:13.5px; line-height:1.55; }
    .no-bank-card a  { color:#92400E; text-decoration:underline; }

    /* === Record donation form === */
    .record-card { background:#fff; border:1px solid #E5E7EB; border-radius:14px;
                   padding:22px; margin-bottom:18px; }
    .record-card h4 { font-size:17px; font-weight:700; color:#15803D; margin:0 0 4px;
                      display:flex; align-items:center; gap:8px; }
    .record-card .blurb { font-size:13px; color:#6B7280; margin-bottom:18px; }
    .preset-amounts { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:10px; }
    .preset-amount { background:#F3F4F6; border:1.5px solid #E5E7EB; color:#111827;
                     font-weight:600; font-size:13px; padding:7px 14px;
                     border-radius:8px; cursor:pointer; transition:all 0.15s; }
    .preset-amount:hover { background:#DBEAFE; border-color:#1E40AF; }
    .preset-amount.active { background:#1E40AF; color:#fff; border-color:#1E40AF; }
    .btn-record { background:#15803D; color:#fff; font-weight:600; padding:12px 22px;
                  border:none; border-radius:9px; font-size:15px; cursor:pointer;
                  display:inline-flex; align-items:center; gap:8px;
                  box-shadow:0 4px 12px rgba(21,128,61,0.25); }
    .btn-record:hover { background:#166534; transform:translateY(-1px); }

    /* === Closed state === */
    .closed-card {
        background:#fff; border:1px solid #E5E7EB; border-radius:14px;
        padding:32px; margin-bottom:18px; text-align:center;
        position:relative; overflow:hidden;
    }
    .closed-card::before {
        content:""; position:absolute; top:0; left:0; right:0; height:4px;
    }
    .closed-card.goal-reached::before {
        background:linear-gradient(90deg,#15803d,#22c55e);
    }
    .closed-card.admin-closed::before {
        background:linear-gradient(90deg,#6B7280,#9CA3AF);
    }
    .closed-icon {
        width:64px; height:64px; border-radius:50%;
        display:flex; align-items:center; justify-content:center;
        margin:0 auto 16px; font-size:30px;
    }
    .closed-icon.success { background:#D1FAE5; color:#15803d; }
    .closed-icon.neutral { background:#F3F4F6; color:#6B7280; }
    .closed-card h3 {
        font-size:22px; font-weight:700; color:#111827; margin:0 0 8px;
    }
    .closed-card .closed-blurb {
        font-size:14px; color:#6B7280; line-height:1.6; margin:0 auto 20px;
        max-width:520px;
    }
    .closed-stats {
        display:flex; justify-content:center; gap:32px; flex-wrap:wrap;
        background:#F9FAFB; border-radius:12px; padding:18px 24px;
        margin:0 auto 20px; max-width:520px;
    }
    .closed-stat { text-align:center; }
    .closed-stat-num { font-size:24px; font-weight:700; color:#111827; line-height:1; }
    .closed-stat-label { font-size:11px; color:#9CA3AF; text-transform:uppercase;
                         letter-spacing:0.05em; margin-top:4px; }
    .closed-back-link {
        color:#1E40AF; text-decoration:none; font-weight:600; font-size:14px;
        display:inline-flex; align-items:center; gap:6px;
    }
    .closed-back-link:hover { text-decoration:underline; color:#1E40AF; }
</style>
@endpush

@section('content')
<section class="py-4">
    <div class="container donate-wrap">

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-warning">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $err) <div>{{ $err }}</div> @endforeach
            </div>
        @endif

        {{-- Case summary --}}
        <div class="case-card">
            <span class="case-pill pill-verified"><i class="bi bi-shield-check"></i> Verified</span>
            @if($crisis->status === 'active' && !$isClosed)
                <span class="case-pill pill-active">Active</span>
            @endif
            @if($isClosed)
                <span class="case-pill pill-closed">
                    <i class="bi bi-lock-fill"></i> Donations Closed
                </span>
            @endif
            <h3 class="mt-2">{{ ucwords(str_replace('_', ' ', $crisis->crisis_type)) }}</h3>
            @if($crisis->location)
                <div class="meta"><i class="bi bi-geo-alt"></i> {{ $crisis->location }}</div>
            @endif
            <p class="desc">{{ \Illuminate\Support\Str::limit($crisis->crisis_description, 300) }}</p>
            <hr class="my-3">
            <x-donation-progress :crisis="$crisis" />
        </div>

        @if($isClosed)
            {{-- ==================================================== --}}
            {{-- CLOSED STATE — celebratory or neutral message         --}}
            {{-- ==================================================== --}}

            @if($closedKind === 'goal_reached')
                <div class="closed-card goal-reached">
                    <div class="closed-icon success">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h3>Goal reached — thank you!</h3>
                    <p class="closed-blurb">
                        This case successfully raised
                        <strong>RM {{ number_format((float) $crisis->donation_raised, 2) }}</strong>
                        with the help of generous donors like you. Donations are no longer being collected for this case.
                    </p>

                    <div class="closed-stats">
                        <div class="closed-stat">
                            <div class="closed-stat-num">RM {{ number_format((float) $crisis->donation_raised, 0) }}</div>
                            <div class="closed-stat-label">Total raised</div>
                        </div>
                        <div class="closed-stat">
                            <div class="closed-stat-num">{{ $crisis->donations()->count() }}</div>
                            <div class="closed-stat-label">Donors</div>
                        </div>
                        <div class="closed-stat">
                            <div class="closed-stat-num">{{ $crisis->donation_closed_at?->diffInDays($crisis->date_reported) ?? 0 }}d</div>
                            <div class="closed-stat-label">Campaign length</div>
                        </div>
                    </div>

                    <a href="{{ route('crisis.show', $crisis->crisis_id) }}" class="closed-back-link">
                        <i class="bi bi-arrow-right"></i> View case updates
                    </a>
                </div>
            @else
                <div class="closed-card admin-closed">
                    <div class="closed-icon neutral">
                        <i class="bi bi-lock-fill"></i>
                    </div>
                    <h3>Donations are closed</h3>
                    <p class="closed-blurb">
                        Donations are no longer being accepted for this case. Thank you to everyone who contributed —
                        <strong>RM {{ number_format((float) $crisis->donation_raised, 2) }}</strong>
                        was raised from {{ $crisis->donations()->count() }} {{ \Illuminate\Support\Str::plural('donor', $crisis->donations()->count()) }}.
                    </p>

                    <a href="{{ route('crisis.show', $crisis->crisis_id) }}" class="closed-back-link">
                        <i class="bi bi-arrow-right"></i> View case updates
                    </a>
                </div>
            @endif

        @else
            {{-- ==================================================== --}}
            {{-- OPEN STATE — normal donate flow                       --}}
            {{-- ==================================================== --}}

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
                                    <span class="value" id="acct-masked">
                                        @php
                                            $acct = (string) $student->bank_account_number;
                                            $last4 = substr($acct, -4);
                                        @endphp
                                        •••• •••• {{ $last4 }}
                                    </span>
                                    <span class="value" id="acct-full" style="display:none;">{{ $acct }}</span>
                                    <button type="button" class="reveal-btn" id="acct-reveal" onclick="revealAccount()">
                                        <i class="bi bi-eye"></i> Show
                                    </button>
                                    <button type="button" class="copy-btn" id="acct-copy" style="display:none;"
                                            onclick="copyToClipboard('{{ addslashes($acct) }}', this)">
                                        <i class="bi bi-copy"></i> Copy
                                    </button>
                                </div>
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
        const m = document.getElementById('acct-masked');
        const f = document.getElementById('acct-full');
        const r = document.getElementById('acct-reveal');
        const c = document.getElementById('acct-copy');
        if (m) m.style.display = 'none';
        if (f) f.style.display = '';
        if (r) r.style.display = 'none';
        if (c) c.style.display = '';
    }

    // Copy-to-clipboard with visual feedback
    function copyToClipboard(text, btn) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => flashCopied(btn));
        } else {
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
