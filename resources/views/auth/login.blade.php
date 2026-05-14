<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In - e-Tawassul</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; min-height: 100vh; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f1b4c 0%, #1a3a6e 50%, #0c2d5a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
            overflow-x: hidden;
            color: #0f172a;
        }

        .bg-shapes { position: fixed; inset: 0; overflow: hidden; pointer-events: none; z-index: 0; }
        .bg-shape {
            position: absolute; border-radius: 50%; opacity: 0.07;
            background: radial-gradient(circle, #06b6d4, transparent);
            animation: drift 8s ease-in-out infinite;
        }
        .bg-shape:nth-child(1) { width:500px; height:500px; top:-150px; right:-100px; }
        .bg-shape:nth-child(2) { width:300px; height:300px; bottom:-80px; left:-80px; animation-delay:2s; }
        .bg-shape:nth-child(3) { width:200px; height:200px; top:50%; left:10%; animation-delay:4s; }
        @keyframes drift {
            0%,100%{transform:translate(0,0) scale(1)}
            50%{transform:translate(20px,-20px) scale(1.05)}
        }

        .particles { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
        .particle {
            position: absolute; border-radius: 50%;
            background: rgba(255,255,255,0.3); animation: rise linear infinite;
        }
        @keyframes rise {
            0%{transform:translateY(100vh) scale(0);opacity:0}
            10%{opacity:1}
            90%{opacity:0.5}
            100%{transform:translateY(-100px) scale(1);opacity:0}
        }

        .login-shell {
            display: flex;
            width: 100%;
            max-width: 1040px;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 32px 80px rgba(0,0,0,0.4);
            position: relative;
            z-index: 10;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(160deg, rgba(26,86,219,0.95), rgba(6,182,212,0.75));
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #fff;
            position: relative;
            overflow: hidden;
            min-height: 640px;
        }
        .login-left::before {
            content: ''; position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }
        .login-left > * { position: relative; z-index: 1; }

        .brand-logo {
            width: 56px; height: 56px;
            background: rgba(255,255,255,0.15);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; color: #fff;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .login-left h1 {
            font-size: 28px; font-weight: 800;
            color: #fff; margin: 0 0 8px 0;
            letter-spacing: -0.02em;
        }
        .brand-tag {
            font-size: 13px;
            color: rgba(255,255,255,0.8);
            line-height: 1.6;
            max-width: 360px;
            margin: 0;
        }

        .feature-list { margin: 32px 0; }
        .feature-item {
            display: flex; align-items: flex-start; gap: 14px;
            margin-bottom: 22px;
        }
        .feature-item:last-child { margin-bottom: 0; }
        .feature-item-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            background: rgba(255,255,255,0.12);
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
            border: 1px solid rgba(255,255,255,0.15);
        }
        .feature-item-text h4 {
            font-size: 14px; font-weight: 700;
            color: #fff; margin: 0 0 3px 0;
        }
        .feature-item-text p {
            font-size: 12px;
            color: rgba(255,255,255,0.7);
            line-height: 1.5;
            margin: 0;
        }

        .left-footer {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 16px;
            background: rgba(255,255,255,0.08);
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .left-footer i { color: rgba(255,255,255,0.5); font-size: 16px; }
        .left-footer p {
            font-size: 12px;
            color: rgba(255,255,255,0.75);
            margin: 0;
        }

        .login-right {
            width: 460px;
            background: #ffffff;
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-right h2 {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .role-tabs { border-bottom: 1px solid #e2e8f0; }
        .role-tabs .nav-link {
            color: #64748b;
            font-weight: 600;
            font-size: 14px;
            border: none !important;
            background: transparent;
            padding: 8px 4px;
            margin-right: 18px;
            border-bottom: 2px solid transparent !important;
            border-radius: 0;
        }
        .role-tabs .nav-link.active {
            color: #1a56db !important;
            border-bottom-color: #1a56db !important;
            background: transparent !important;
        }

        .login-right .form-label {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .login-right .form-control-lg {
            background: #f8faff;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            padding: 12px 14px;
            transition: all 0.2s;
        }
        .login-right .form-control-lg:focus {
            background: #fff;
            border-color: #1a56db;
            box-shadow: 0 0 0 3px rgba(26,86,219,0.08);
        }

        .login-right .btn-primary {
            background: #1a56db;
            border-color: #1a56db;
            border-radius: 10px;
            font-weight: 700;
            padding: 13px;
            font-size: 15px;
            transition: all 0.2s;
        }
        .login-right .btn-primary:hover {
            background: #1245b8;
            border-color: #1245b8;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(26,86,219,0.3);
        }

        .demo-credentials {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 12px 14px;
        }
        .demo-credentials h6 {
            color: #92400e !important;
            font-weight: 700;
            font-size: 11px;
        }
        .demo-credentials code {
            font-size: 11px;
            color: #be185d;
            background: transparent;
        }
        .demo-credentials table { margin: 0; }
        .demo-credentials td { padding: 4px 8px; border: none; font-size: 12px; }

        @media (max-width: 900px) {
            .login-left { display: none; }
            .login-right { width: 100%; padding: 40px 28px; }
            .login-shell { max-width: 480px; }
        }
    </style>
</head>
<body>

<div class="bg-shapes">
    <div class="bg-shape"></div>
    <div class="bg-shape"></div>
    <div class="bg-shape"></div>
</div>
<div class="particles" id="authParticles"></div>

<div class="login-shell">
    <div class="login-left">
        <div class="brand">
            <div class="brand-logo">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h1>e-Tawassul</h1>
            <p class="brand-tag">
                Secure Student Crisis &amp; Digital Legacy System &mdash; UTM&rsquo;s compassionate platform for when it matters most.
            </p>
        </div>

        <div class="feature-list">
            <div class="feature-item">
                <div class="feature-item-icon">
                    <i class="bi bi-shield-check" style="color:#6ee7b7;"></i>
                </div>
                <div class="feature-item-text">
                    <h4>Role-Based Access</h4>
                    <p>Students, Administrators, and Next of Kin each have tailored secure access.</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-item-icon">
                    <i class="bi bi-key-fill" style="color:#93c5fd;"></i>
                </div>
                <div class="feature-item-text">
                    <h4>Two-Factor Authentication</h4>
                    <p>Next of Kin logins are protected with OTP sent via Email or SMS.</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-item-icon">
                    <i class="bi bi-lock-fill" style="color:#fcd34d;"></i>
                </div>
                <div class="feature-item-text">
                    <h4>End-to-End Encryption</h4>
                    <p>All legacy messages and sensitive data are encrypted at rest and in transit.</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-item-icon">
                    <i class="bi bi-clock-history" style="color:#f9a8d4;"></i>
                </div>
                <div class="feature-item-text">
                    <h4>Rate-Limited Security</h4>
                    <p>Brute-force protection with automatic lockout after 5 failed attempts.</p>
                </div>
            </div>
        </div>

        <div class="left-footer">
            <i class="bi bi-bank"></i>
            <p>Universiti Teknologi Malaysia &copy; {{ date('Y') }}</p>
        </div>
    </div>

    <div class="login-right">
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
                <label class="form-label small text-uppercase fw-semibold">Student ID or Email</label>
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

<script>
    (function() {
        const container = document.getElementById('authParticles');
        if (!container) return;
        for (let i = 0; i < 20; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            p.style.left = Math.random() * 100 + '%';
            p.style.animationDuration = (Math.random() * 8 + 6) + 's';
            p.style.animationDelay = (Math.random() * 8) + 's';
            const size = (Math.random() * 3 + 1) + 'px';
            p.style.width = size;
            p.style.height = size;
            container.appendChild(p);
        }
    })();
</script>

</body>
</html>
