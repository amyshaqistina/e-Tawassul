<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Confirm Your Email — e-Tawassul</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; min-height: 100vh; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: linear-gradient(135deg, #0f1b4c 0%, #1a3a6e 50%, #0c2d5a 100%);
            display: flex; align-items: center; justify-content: center;
            padding: 24px; color: #0f172a;
        }
        .shell {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.35);
            max-width: 520px; width: 100%;
            padding: 36px 36px 30px;
        }
        .avatar-wrap {
            display: flex; align-items: center; gap: 16px;
            padding: 16px;
            background: #f8faff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 22px;
        }
        .avatar-wrap img {
            width: 64px; height: 64px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .avatar-wrap .avatar-fallback {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: #1a56db;
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; font-weight: 700;
        }
        .avatar-wrap .name { font-size: 16px; font-weight: 700; color: #0f172a; }
        .avatar-wrap .matric { font-size: 12.5px; color: #64748b; }
        h1 {
            font-size: 22px; font-weight: 800;
            color: #0f172a; margin: 0 0 6px;
            letter-spacing: -0.02em;
        }
        .lede {
            font-size: 13.5px; color: #475569;
            line-height: 1.55; margin-bottom: 22px;
        }
        .form-label {
            font-size: 10px; font-weight: 700;
            color: #0f172a; letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .form-control-lg {
            background: #f8faff;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            font-size: 14px;
            padding: 11px 14px;
        }
        .form-control-lg:focus {
            border-color: #1a56db;
            box-shadow: 0 0 0 3px rgba(26,86,219,0.15);
        }
        .helper {
            font-size: 11.5px; color: #64748b;
            margin-top: 6px;
        }
        .helper i { color: #1a56db; margin-right: 3px; }
        .btn-primary {
            background: #1a56db; border-color: #1a56db;
            border-radius: 9px; font-weight: 700;
            padding: 11px; font-size: 14px;
            transition: all 0.15s;
        }
        .btn-primary:hover {
            background: #1245b8; border-color: #1245b8;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(26,86,219,0.3);
        }
        .footer-note {
            margin-top: 20px;
            font-size: 11.5px; color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="shell">
    <div class="avatar-wrap">
        @if($student->image_url)
            <img src="{{ $student->image_url }}" alt="{{ $student->full_name }}"
                 onerror="this.outerHTML='<div class=\'avatar-fallback\'>{{ strtoupper(substr($student->first_name, 0, 1)) }}</div>'">
        @else
            <div class="avatar-fallback">{{ strtoupper(substr($student->first_name, 0, 1)) }}</div>
        @endif
        <div>
            <div class="name">{{ $student->full_name }}</div>
            <div class="matric">{{ $student->student_id }} &middot; {{ $student->kulliyyah ? Str::limit($student->kulliyyah, 40) : '—' }}</div>
        </div>
    </div>

    <h1>One quick thing</h1>
    <p class="lede">
        We'll send you crisis-report updates and other important notifications
        at the email below. Please confirm it's correct, or update it now.
        You only have to do this <strong>once</strong>.
    </p>

    <form method="POST" action="{{ route('student.confirm-email.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label text-uppercase">Your Email Address</label>
            <input type="email" name="email"
                   value="{{ old('email', $student->email) }}"
                   class="form-control form-control-lg @error('email') is-invalid @enderror"
                   required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="helper">
                <i class="bi bi-info-circle-fill"></i>
                We auto-filled this based on your name. If it's wrong, just type your real IIUM email here.
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-check-circle"></i> Confirm and Continue
        </button>
    </form>

    <div class="footer-note">
        <i class="bi bi-shield-lock"></i> Your email is kept private and only used for system notifications.
    </div>
</div>

</body>
</html>
