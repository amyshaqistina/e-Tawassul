@extends('layouts.public')
@section('title', 'Sign In - e-Tawassul')

@section('content')
<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-8">
                <div class="login-card">
                    <div class="text-center mb-4">
                        <h2 class="mb-1">Welcome back</h2>
                        <p class="text-muted small mb-0">Sign in to e-Tawassul to continue</p>
                    </div>

                    <ul class="nav nav-tabs role-tabs mb-3" role="tablist" x-data="{ tab: '{{ old('role','student') }}' }">
                        @foreach(['student'=>'Student','admin'=>'Admin','nok'=>'Next of Kin','lecturer'=>'Lecturer'] as $val => $label)
                            <li class="nav-item">
                                <button type="button"
                                        class="nav-link"
                                        :class="tab === '{{ $val }}' ? 'active' : ''"
                                        @click="tab = '{{ $val }}'; document.getElementById('role').value = '{{ $val }}'">
                                    {{ $label }}
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <form method="POST" action="{{ route('login.post') }}" novalidate>
                        @csrf
                        <input type="hidden" name="role" id="role" value="{{ old('role','student') }}">

                        <div class="mb-3">
                            <label class="form-label small text-uppercase fw-semibold">Identifier</label>
                            <input type="text" name="identifier" value="{{ old('identifier') }}"
                                   class="form-control form-control-lg @error('identifier') is-invalid @enderror"
                                   placeholder="Student ID, email, or username" required autofocus>
                            @error('identifier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-uppercase fw-semibold">Password</label>
                            <input type="password" name="password" class="form-control form-control-lg @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" name="remember" value="1" id="remember" class="form-check-input">
                            <label for="remember" class="form-check-label small">Keep me signed in</label>
                        </div>

                        @if($errors->has('auth'))
                            <div class="alert alert-danger small">{{ $errors->first('auth') }}</div>
                        @endif

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-shield-lock"></i> Sign In
                        </button>
                    </form>
                </div>

                <div class="demo-credentials mt-4">
                    <h6 class="text-uppercase small text-muted mb-2"><i class="bi bi-info-circle"></i> Demo Credentials</h6>
                    <div class="table-responsive">
                        <table class="table table-sm small mb-0">
                            <tbody>
                                <tr><td><strong>Admin</strong></td><td><code>admin@iium.edu.my</code></td><td><code>password</code></td></tr>
                                <tr><td><strong>Student</strong></td><td><code>2225498</code></td><td><code>password</code></td></tr>
                                <tr><td><strong>NOK</strong></td><td><code>nok@example.com</code></td><td><code>password</code> + OTP</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
