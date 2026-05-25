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

    {{-- Top navbar styles for all logged-in pages --}}
    <style>
        /* ===== Override: hide the legacy sidebar, use top nav instead ===== */
        .app-sidebar { display: none !important; }
        .app-shell { display: block !important; }
        .app-main { width: 100% !important; }

        /* ===== TOP NAVBAR ===== */
        .et-topnav {
            position: sticky;
            top: 0;
            z-index: 1030;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }

        .et-topnav-inner {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 24px;
            padding: 12px 24px;
        }

        /* Brand */
        .et-brand {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0px;
            text-decoration: none;
            color: #1a6fa8;
            flex-shrink: 0;
        }
        .et-brand:hover { color: #14567f; }
        .et-brand-mark {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            line-height: 1;
            color: #1a6fa8;
        }
        .et-brand-sub {
            font-size: 0.65rem;
            font-weight: 500;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0px;
            background: transparent;
            border-radius: 0px;
            line-height: 1.2;
        }

        /* Nav items wrapper */
        .et-nav-items {
            display: flex;
            align-items: center;
            gap: 4px;
            flex: 1;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .et-nav-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            color: #475569;
            text-decoration: none;
            transition: all .2s ease;
            white-space: nowrap;
            border: 1px solid transparent;
            background: transparent;
            cursor: pointer;
        }
        .et-nav-link i {
            font-size: 14px;
            color: #64748b;
            transition: color .2s, transform .25s cubic-bezier(.34, 1.56, .64, 1);
        }
        .et-nav-link:hover {
            color: #1d4ed8;
            background: #eff6ff;
        }
        .et-nav-link:hover i {
            color: #1d4ed8;
            transform: translateY(-1px) scale(1.15);
        }
        .et-nav-link.active {
            color: #1d4ed8;
            background: linear-gradient(135deg, #eff6ff, #ede9fe);
            border-color: #c7d2fe;
        }
        .et-nav-link.active i { color: #1d4ed8; }

        /* Dropdown */
        .et-nav-dropdown { position: relative; }
        .et-nav-dropdown .chev {
            font-size: 10px;
            margin-left: 2px;
            transition: transform .2s;
            color: #94a3b8;
        }
        .et-nav-dropdown.open .chev { transform: rotate(180deg); }
        .et-nav-dropdown.open > .et-nav-link {
            color: #1d4ed8;
            background: #eff6ff;
        }

        .et-dropdown-panel {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            min-width: 240px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 20px 50px -12px rgba(15, 23, 42, 0.18),
                        0 4px 8px rgba(15, 23, 42, 0.05);
            border: 1px solid #e2e8f0;
            padding: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px);
            transition: opacity .18s, transform .18s, visibility .18s;
        }
        .et-nav-dropdown.open .et-dropdown-panel {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .et-dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            text-decoration: none;
            transition: background .15s, color .15s;
        }
        .et-dropdown-item i {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: #f1f5f9;
            color: #1d4ed8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            transition: background .15s, color .15s, transform .25s cubic-bezier(.34, 1.56, .64, 1);
            flex-shrink: 0;
        }
        .et-dropdown-item:hover {
            background: #f8fafc;
            color: #1d4ed8;
        }
        .et-dropdown-item:hover i {
            background: #dbeafe;
            transform: rotate(-6deg) scale(1.08);
        }
        .et-dropdown-item.active {
            background: linear-gradient(135deg, #eff6ff, #ede9fe);
            color: #1d4ed8;
        }
        .et-dropdown-item.active i {
            background: #1d4ed8;
            color: white;
        }
        .et-dropdown-item-sub {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 500;
            display: block;
            margin-top: 2px;
        }

        /* Right side: notifications + user */
        .et-nav-right {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
            flex-shrink: 0;
        }

        /* User menu (replaces standalone chip + sign-out button) */
        .et-user-menu {
            position: relative;
        }
        .et-user-trigger {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 12px 5px 5px;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: background .2s, border-color .2s, box-shadow .2s;
        }
        .et-user-trigger:hover {
            background: white;
            border-color: #c7d2fe;
            box-shadow: 0 4px 12px -4px rgba(29, 78, 216, 0.18);
        }
        .et-user-menu.open .et-user-trigger {
            background: white;
            border-color: #1d4ed8;
            box-shadow: 0 4px 12px -4px rgba(29, 78, 216, 0.25);
        }
        .et-user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1d4ed8, #7c3aed);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            flex-shrink: 0;
        }
        .et-user-meta { line-height: 1.2; text-align: left; }
        .et-user-name { font-size: 13px; font-weight: 700; color: #0f172a; }
        .et-user-role { font-size: 10.5px; color: #64748b; }
        .et-user-trigger .chev {
            font-size: 10px;
            color: #94a3b8;
            margin-left: 2px;
            transition: transform .2s;
        }
        .et-user-menu.open .et-user-trigger .chev { transform: rotate(180deg); }

        .et-user-panel {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 240px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 20px 50px -12px rgba(15, 23, 42, 0.18),
                        0 4px 8px rgba(15, 23, 42, 0.05);
            border: 1px solid #e2e8f0;
            padding: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px);
            transition: opacity .18s, transform .18s, visibility .18s;
        }
        .et-user-menu.open .et-user-panel {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .et-user-panel-header {
            padding: 12px 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 6px;
        }
        .et-user-panel-name {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        .et-user-panel-role {
            font-size: 11.5px;
            color: #64748b;
            margin-top: 2px;
        }
        .et-user-panel-role .pill {
            display: inline-block;
            padding: 2px 8px;
            background: linear-gradient(135deg, #eff6ff, #ede9fe);
            color: #1d4ed8;
            border-radius: 999px;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .et-user-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            text-decoration: none;
            transition: background .15s, color .15s;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }
        .et-user-item i {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: #f1f5f9;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            transition: background .15s, color .15s, transform .25s cubic-bezier(.34, 1.56, .64, 1);
            flex-shrink: 0;
        }
        .et-user-item:hover {
            background: #f8fafc;
            color: #1d4ed8;
        }
        .et-user-item:hover i {
            background: #dbeafe;
            color: #1d4ed8;
            transform: rotate(-6deg) scale(1.08);
        }
        .et-user-item--danger { color: #475569; }
        .et-user-item--danger:hover {
            background: #fef2f2;
            color: #dc2626;
        }
        .et-user-item--danger:hover i {
            background: #fee2e2;
            color: #dc2626;
        }
        .et-user-panel hr.et-divider {
            margin: 6px 4px;
            border: none;
            border-top: 1px solid #f1f5f9;
        }

        /* Notification bell pulse for unread */
        .et-bell-wrap { position: relative; }
        .et-bell-wrap .badge[data-unread]:not([data-unread="0"]):not([data-unread=""])::after,
        .et-bell-wrap .notification-badge:not(:empty)::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 2px solid #ef4444;
            animation: et-bell-pulse 2s ease-out infinite;
            pointer-events: none;
        }
        @keyframes et-bell-pulse {
            0% { transform: scale(.6); opacity: 1; }
            100% { transform: scale(1.6); opacity: 0; }
        }

        /* Mobile toggle */
        .et-mobile-toggle {
            display: none;
            background: transparent;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px 12px;
            color: #475569;
            cursor: pointer;
            font-size: 18px;
        }

        @media (max-width: 1024px) {
            .et-nav-link span.label,
            .et-nav-link span.full { display: none; }
        }

        @media (max-width: 880px) {
            .et-mobile-toggle { display: inline-flex; align-items: center; }
            .et-nav-items {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                flex-direction: column;
                gap: 4px;
                padding: 14px;
                border-bottom: 1px solid #e2e8f0;
                box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
            }
            .et-nav-items.open { display: flex; }
            .et-nav-items .et-nav-link { width: 100%; justify-content: flex-start; }
            .et-nav-items .et-nav-link span.label,
            .et-nav-items .et-nav-link span.full { display: inline; }
            .et-nav-dropdown { width: 100%; }
            .et-dropdown-panel {
                position: static;
                opacity: 1;
                visibility: visible;
                transform: none;
                box-shadow: none;
                border: none;
                background: #f8fafc;
                padding-left: 38px;
                margin-top: 2px;
                display: none;
            }
            .et-nav-dropdown.open .et-dropdown-panel { display: block; }
            .et-user-meta { display: none !important; }
        }

        /* Make app-content full-width once sidebar is gone */
        .app-content { max-width: 1400px; margin: 0 auto; padding: 24px; }
        .app-topbar { display: none !important; } /* hide old topbar */

        /* Page header (replaces the old topbar's page-title section) */
        .et-page-head {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px 24px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .et-page-head h1 {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.01em;
        }
        .et-page-head .sub {
            font-size: 13px;
            color: #64748b;
            margin-top: 2px;
        }
    </style>
</head>

<body class="auth-layout {{ $bodyClass ?? '' }}" x-data="{ mobileNavOpen: false, openDrop: null }">

    {{-- TOP NAVBAR --}}
    <nav class="et-topnav">
        <div class="et-topnav-inner">
            {{-- Brand --}}
            <a href="{{ route('public.dashboard') }}" class="et-brand">
                <span class="et-brand-mark">e-Tawassul</span>
                <span class="et-brand-sub">{{ $roleLabel ?? 'User' }}</span>
            </a>

            {{-- Nav items (provided per role layout) --}}
            <ul class="et-nav-items" :class="{ 'open': mobileNavOpen }">
                @yield('nav-items')
            </ul>

            {{-- Right side: notifications + user menu --}}
            <div class="et-nav-right">
                <div class="et-bell-wrap">
                    @include('components.notification-bell')
                </div>

                @php
                    $name = match (true) {
                        isset($authUser) &&
                            method_exists($authUser, 'getAttribute') &&
                            $authUser->first_name ?? false
                            => $authUser->first_name,
                        isset($authUser->admin_name) => $authUser->admin_name,
                        default => 'U',
                    };
                    $role = $roleLabel ?? '';
                @endphp

                <div class="et-user-menu" data-user-menu>
                    <button type="button" class="et-user-trigger" data-user-toggle>
                        <div class="et-user-avatar">{{ strtoupper(substr($name, 0, 1)) }}</div>
                        <div class="et-user-meta d-none d-md-block">
                            <div class="et-user-name">{{ $name }}</div>
                            <div class="et-user-role">{{ $role }}</div>
                        </div>
                        <i class="bi bi-chevron-down chev d-none d-md-inline"></i>
                    </button>

                    <div class="et-user-panel">
                        <div class="et-user-panel-header">
                            <div class="et-user-panel-name">{{ $name }}</div>
                            <div class="et-user-panel-role">
                                <span class="pill">{{ $role }}</span>
                            </div>
                        </div>

                        @if (Route::has('student.profile') && $role === 'Student')
                            <a href="{{ route('student.profile') }}" class="et-user-item">
                                <i class="bi bi-person-badge"></i>
                                <span>My Profile</span>
                            </a>
                        @endif

                        <a href="{{ route('notifications.index') }}" class="et-user-item">
                            <i class="bi bi-bell"></i>
                            <span>Notifications</span>
                        </a>

                        <hr class="et-divider">

                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="et-user-item et-user-item--danger">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Sign out</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Mobile toggle --}}
            <button class="et-mobile-toggle" type="button" @click="mobileNavOpen = !mobileNavOpen">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </nav>

    {{-- Optional page header (per page) --}}
    @hasSection('page-title')
    <div class="et-page-head">
        <div>
            <h1>@yield('page-title')</h1>
            @hasSection('page-subtitle')
                <div class="sub">@yield('page-subtitle')</div>
            @endif
        </div>
        @hasSection('page-actions')
            <div>@yield('page-actions')</div>
        @endif
    </div>
    @endif

    {{-- Flash messages --}}
    @if (session('status'))
        <div class="container-fluid mt-3" style="max-width:1400px;margin:0 auto;">
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-1"></i> {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="container-fluid mt-3" style="max-width:1400px;margin:0 auto;">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    {{-- Top-nav dropdown behavior + user menu --}}
    <script>
        document.addEventListener('click', function (e) {
            var navToggle = e.target.closest('.et-nav-dropdown > .et-nav-link');
            var userToggle = e.target.closest('[data-user-toggle]');
            var navDropdowns = document.querySelectorAll('.et-nav-dropdown');
            var userMenu = document.querySelector('[data-user-menu]');

            // Nav dropdown toggle
            if (navToggle) {
                e.preventDefault();
                var parent = navToggle.parentElement;
                var wasOpen = parent.classList.contains('open');
                navDropdowns.forEach(function (d) { d.classList.remove('open'); });
                if (userMenu) userMenu.classList.remove('open');
                if (!wasOpen) parent.classList.add('open');
                return;
            }

            // User menu toggle
            if (userToggle && userMenu) {
                e.preventDefault();
                var wasOpen = userMenu.classList.contains('open');
                navDropdowns.forEach(function (d) { d.classList.remove('open'); });
                userMenu.classList.toggle('open', !wasOpen);
                return;
            }

            // Click outside closes everything (except inside open panels)
            if (!e.target.closest('.et-dropdown-panel') && !e.target.closest('.et-user-panel')) {
                navDropdowns.forEach(function (d) { d.classList.remove('open'); });
                if (userMenu) userMenu.classList.remove('open');
            }
        });

        // ESC closes all menus
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.et-nav-dropdown.open').forEach(function (d) { d.classList.remove('open'); });
                var userMenu = document.querySelector('[data-user-menu].open');
                if (userMenu) userMenu.classList.remove('open');
            }
        });
    </script>

    @stack('scripts')
    <x-chatbot-widget />
</body>

</html>
