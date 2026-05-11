@extends('layouts.public')
@section('title', 'Two-Factor Verification - e-Tawassul')

@section('content')
<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="text-center mb-4">
                        <div class="otp-icon"><i class="bi bi-shield-lock-fill"></i></div>
                        <h3 class="mb-1">Verify your identity</h3>
                        <p class="text-muted small">We've sent a 6-digit code to your registered email. The code expires in 5 minutes.</p>
                    </div>

                    @if(session('demo_otp'))
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle"></i>
                            <strong>Demo mode:</strong> Mail isn't configured, so your code is
                            <code class="ms-1">{{ session('demo_otp') }}</code>
                        </div>
                    @endif

                    @if(session('status'))
                        <div class="alert alert-success small">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('nok.twofactor.verify') }}">
                        @csrf
                        <div class="otp-input-group mb-3" data-otp-input>
                            @for($i = 0; $i < 6; $i++)
                                <input type="text" inputmode="numeric" maxlength="1" class="form-control otp-digit" autocomplete="off">
                            @endfor
                        </div>
                        <input type="hidden" name="code" id="otp-final">
                        @error('code')<div class="alert alert-danger small">{{ $message }}</div>@enderror

                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-2">Verify & Continue</button>
                    </form>

                    <form method="POST" action="{{ route('nok.twofactor.resend') }}" class="text-center">
                        @csrf
                        <button type="submit" class="btn btn-link btn-sm">Resend code</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
