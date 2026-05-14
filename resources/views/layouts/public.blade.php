<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'e-Tawassul - IIUM Crisis Response')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
    @stack('head')
</head>

<body class="public-layout">

    <nav class="navbar navbar-expand-lg navbar-public sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <span class="brand-mark">e-Tawassul</span>
                <small class="brand-sub d-none d-md-inline">IIUM Crisis Response</small>
            </a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#topnav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="topnav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Active Cases</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-outline-primary btn-sm" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="public-main">
        @if (session('status'))
            <div class="container mt-3">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="public-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="fw-bold mb-2">e-Tawassul</h6>
                    <p class="small text-muted mb-0">
                        Secure, blockchain-verified crisis response system for the IIUM community.
                        Supporting students and families during times of hardship.
                    </p>
                </div>
                <div class="col-md-3">
                    <h6 class="fw-bold mb-2">System</h6>
                    <ul class="list-unstyled small">
                        <li><a href="{{ route('home') }}" class="text-decoration-none text-muted">Active cases</a></li>
                        <li><a href="{{ route('login') }}" class="text-decoration-none text-muted">Login</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="fw-bold mb-2">Institution</h6>
                    <ul class="list-unstyled small">
                        <li class="text-muted">IIUM Gombak</li>
                        <li class="text-muted">Student Welfare Office</li>
                    </ul>
                </div>
            </div>
            <hr>
            <p class="small text-muted text-center mb-0">
                &copy; {{ date('Y') }} International Islamic University Malaysia — e-Tawassul System
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>

</html>
