<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'e-Tawassul')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/chatbot.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
    @stack('head')
    @stack('styles')

    {{-- ===== Top navigation styles ===== --}}
    <style>
        /* Reset old sidebar layout so .app-shell stacks vertically now */
        .app-shell {
            display: block !important;
        }

        .app-main {
            min-width: 0 !important;
            overflow-x: hidden !important;
        }

        /* ===== Top navbar ===== */
        .app-topnav {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .app-topnav-inner {
            display: flex;
            align-items: center;
            gap: 24px;
            padding: 0 28px;
            height: 64px;
        }

        /* Brand block on the left */
        .topnav-brand {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .topnav-brand .brand-mark {
            font-size: 19px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.3px;
        }
        .topnav-brand .brand-sub {
            font-size: 10.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
        }

        /* Role accent on the brand sub */
        body.theme-student .topnav-brand .brand-sub { color: #1a56db; }
        body.theme-nok     .topnav-brand .brand-sub { color: #ea580c; }
        body.theme-admin   .topnav-brand .brand-sub { color: #7c3aed; }

        /* Nav links */
        .topnav-links {
            display: flex;
            align-items: center;
            gap: 4px;
            flex: 1;
            min-width: 0;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .topnav-links::-webkit-scrollbar { display: none; }

        .topnav-links .nav-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 14px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            color: #64748b;
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.15s;
            border: none;
            background: transparent;
        }
        .topnav-links .nav-link i {
            font-size: 15px;
            line-height: 1;
        }
        .topnav-links .nav-link:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        .topnav-links .nav-link.active {
            background: #eff6ff;
            color: #1a56db;
        }
        body.theme-nok .topnav-links .nav-link.active   { background: #fff7ed; color: #ea580c; }
        body.theme-admin .topnav-links .nav-link.active { background: #f5f3ff; color: #7c3aed; }

        /* Section headers from old sidebar — hide on desktop top nav */
        .topnav-links .nav-section {
            display: none;
        }

        /* Right-side actions */
        .topnav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 10px 5px 5px;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .user-chip .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #1a56db;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            flex-shrink: 0;
        }
        body.theme-nok .user-chip .user-avatar   { background: #ea580c; }
        body.theme-admin .user-chip .user-avatar { background: #7c3aed; }

        .user-chip .user-meta { line-height: 1.15; }
        .user-chip .user-name {
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
        }
        .user-chip .user-role {
            font-size: 10.5px;
            color: #94a3b8;
            font-weight: 500;
        }

        .signout-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            color: #64748b;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
        }
        .signout-btn:hover {
            background: #fef2f2;
            border-color: #fecaca;
            color: #dc2626;
        }

        /* Page title strip below the navbar */
        .app-pagehead {
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            padding: 18px 28px;
        }
        .app-pagehead h5 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
        }
        .app-pagehead small {
            font-size: 12.5px;
            color: #64748b;
        }

        /* Content area */
        /* Content area */
.app-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 12px;
}

/* Also constrain the page title strip so it aligns */
.app-pagehead {
    max-width: 1400px;
    margin: 0 auto;
    background: #ffffff;
    border-bottom: 1px solid #f1f5f9;
    padding: 18px 28px;
    width: 100%;
}

/* And the topnav inner so brand+links align with the content edge */
.app-topnav-inner {
    max-width: 1400px;
    margin: 0 auto;
}

        /* Mobile hamburger */
        .topnav-toggle {
            display: none;
            background: transparent;
            border: none;
            font-size: 22px;
            color: #0f172a;
            padding: 6px 10px;
            cursor: pointer;
        }

        @media (max-width: 991.98px) {
            .app-topnav-inner { padding: 0 16px; gap: 12px; }
            .topnav-toggle { display: inline-flex; }
            .topnav-links {
                position: fixed;
                top: 64px;
                left: 0;
                right: 0;
                bottom: 0;
                background: #fff;
                flex-direction: column;
                align-items: stretch;
                padding: 14px;
                gap: 4px;
                border-top: 1px solid #e2e8f0;
                overflow-y: auto;
                transform: translateX(-100%);
                transition: transform 0.25s ease;
                z-index: 1020;
            }
            .topnav-links.open {
                transform: translateX(0);
            }
            .topnav-links .nav-link {
                padding: 12px 14px;
                font-size: 14.5px;
            }
            .topnav-links .nav-section {
                display: block;
                font-size: 10.5px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 1px;
                color: #94a3b8;
                padding: 14px 14px 6px 14px;
            }
            .user-chip .user-meta { display: none; }
            .app-pagehead { padding: 14px 16px; }
        }
    </style>
</head>

<body class="auth-layout {{ $bodyClass ?? '' }}">

    <div class="app-shell" x-data="{ menuOpen: false }">

        {{-- ===== Top navbar ===== --}}
        <header class="app-topnav">
            <div class="app-topnav-inner">

                {{-- Brand (left) --}}
                <a href="{{ url('/') }}" class="topnav-brand text-decoration-none">
                    <span class="brand-mark">e-Tawassul</span>
                    <small class="brand-sub">{{ $roleLabel ?? '' }}</small>
                </a>

                {{-- Mobile hamburger --}}
                <button class="topnav-toggle ms-auto d-lg-none"
                        @click="menuOpen = !menuOpen"
                        :aria-expanded="menuOpen.toString()">
                    <i class="bi" :class="menuOpen ? 'bi-x' : 'bi-list'"></i>
                </button>

                {{-- Nav links (middle) --}}
                <nav class="topnav-links" :class="{ 'open': menuOpen }">
                    @yield('sidebar')
                </nav>

                {{-- Right-side actions --}}
                <div class="topnav-actions d-none d-lg-flex">
                    @include('components.notification-bell')

                    <div class="user-chip">
                        <div class="user-avatar">
                            @php
                                $name = match (true) {
                                    isset($authUser) &&
                                        method_exists($authUser, 'getAttribute') &&
                                        $authUser->first_name ?? false
                                        => $authUser->first_name,
                                    isset($authUser->admin_name) => $authUser->admin_name,
                                    default => 'U',
                                };
                            @endphp
                            {{ strtoupper(substr($name, 0, 1)) }}
                        </div>
                        <div class="user-meta">
                            <div class="user-name">{{ $name }}</div>
                            <div class="user-role">{{ $roleLabel ?? '' }}</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button class="signout-btn" type="submit">
                            <i class="bi bi-box-arrow-right"></i> Sign out
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- ===== Main content area ===== --}}
        <div class="app-main">

            {{-- Page title strip --}}
            <div class="app-pagehead">
                <h5>@yield('page-title', 'Dashboard')</h5>
                @hasSection('page-subtitle')
                    <small>@yield('page-subtitle')</small>
                @endif
            </div>

            {{-- Flash messages --}}
            @if (session('status'))
                <div class="container-fluid mt-3">
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle me-1"></i> {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="container-fluid mt-3">
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        @foreach ($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            <main class="app-content">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
    <x-chatbot-widget />
</body>

</html>
