@extends('layouts.public')
@section('title', 'Verify your code - e-Tawassul')

@section('content')
<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="text-center mb-4">
                        <div class="otp-icon"><i class="bi bi-shield-lock-fill"></i></div>
                        <h3 class="mb-1">Verify your identity</h3>
                        <p class="text-muted small mb-0">
                            We've sent a 4-digit code to your
                            @if(($channel ?? 'email') === 'sms')
                                phone <strong>{{ $contact ?? '' }}</strong>
                            @else
                                email <strong>{{ $contact ?? '' }}</strong>
                            @endif.
                        </p>
                        <p class="text-muted small">The code expires in 5 minutes.</p>
                    </div>

                    @if(session('demo_otp'))
                        <div class="alert alert-warning small">
                            <i class="bi bi-info-circle"></i>
                            <strong>Demo mode (SMS):</strong> No real SMS provider is configured,
                            so your code is
                            <code class="ms-1 fs-6">{{ session('demo_otp') }}</code>.
                            (In production, this would arrive on your phone.)
                        </div>
                    @endif

                    @if(($channel ?? 'email') === 'email')
                        <div class="alert alert-info small">
                            <i class="bi bi-envelope"></i>
                            Check your inbox. In local development, open
                            <a href="http://localhost:8025" target="_blank" class="alert-link">Mailpit</a>
                            to see the code.
                        </div>
                    @endif

                    @if(session('status'))
                        <div class="alert alert-success small">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('nok.twofactor.verify') }}" id="otpForm">
                        @csrf
                        <div class="otp-input-group mb-3 d-flex justify-content-center gap-2" data-otp-input>
                            @for($i = 0; $i < 4; $i++)
                                <input type="text" inputmode="numeric" maxlength="1"
                                       class="form-control otp-digit text-center fs-3 fw-bold"
                                       style="width:60px; height:70px;"
                                       autocomplete="off"
                                       @if($i === 0) autofocus @endif>
                            @endfor
                        </div>
                        <input type="hidden" name="code" id="otp-final">
                        @error('code')<div class="alert alert-danger small">{{ $message }}</div>@enderror

                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-2">Verify &amp; Continue</button>
                    </form>

                    <form method="POST" action="{{ route('nok.twofactor.resend') }}" class="text-center">
                        @csrf
                        <button type="submit" class="btn btn-link btn-sm">Resend code</button>
                    </form>

                    <div class="text-center mt-2">
                        <a href="{{ route('login') }}" class="small text-muted">
                            <i class="bi bi-arrow-left"></i> Back to login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Auto-advance OTP inputs + paste support + form auto-submit when 4 digits entered.
    (function() {
        const wrap = document.querySelector('[data-otp-input]');
        if (!wrap) return;
        const inputs = Array.from(wrap.querySelectorAll('.otp-digit'));
        const hidden = document.getElementById('otp-final');
        const form   = document.getElementById('otpForm');

        function sync() {
            hidden.value = inputs.map(i => i.value).join('');
        }

        inputs.forEach((inp, idx) => {
            inp.addEventListener('input', () => {
                inp.value = inp.value.replace(/\D/g, '').slice(0, 1);
                sync();
                if (inp.value && idx < inputs.length - 1) inputs[idx + 1].focus();
                if (hidden.value.length === inputs.length) form.requestSubmit();
            });
            inp.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !inp.value && idx > 0) inputs[idx - 1].focus();
            });
            inp.addEventListener('paste', (e) => {
                e.preventDefault();
                const txt = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, inputs.length);
                txt.split('').forEach((ch, i) => { if (inputs[i]) inputs[i].value = ch; });
                sync();
                const next = Math.min(txt.length, inputs.length - 1);
                inputs[next].focus();
                if (hidden.value.length === inputs.length) form.requestSubmit();
            });
        });
    })();
</script>
@endsection
