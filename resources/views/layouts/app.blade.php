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
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
    @stack('head')
    @stack('styles')

    {{-- Global sidebar fixes for every page that uses this layout --}}
    <style>
        /* ===== 1. Fixed sidebar width — stops content from squeezing it ===== */
        @media (min-width: 992px) {
            .app-sidebar {
                width: 240px !important;
                min-width: 240px !important;
                max-width: 240px !important;
                flex-shrink: 0 !important;
            }

            /* Make sure the main column takes whatever's left and can shrink internally */
            .app-main {
                flex: 1 1 0 !important;
                min-width: 0 !important;
                overflow-x: hidden !important;
            }

            /* The flex parent must actually be flex for the above to work */
            .app-shell {
                display: flex !important;
                align-items: stretch !important;
            }
        }

        /* ===== 2. Hide sidebar scrollbar in every browser ===== */
        .app-sidebar {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        .app-sidebar::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
    </style>
</head>

<body class="auth-layout {{ $bodyClass ?? '' }}">

    <div class="app-shell">
        {{-- Sidebar --}}
        <aside class="app-sidebar" x-data="{ open: false }" :class="{ 'open': open }">
            <div class="sidebar-brand">
                <span class="brand-mark">e-Tawassul</span>
                <small class="brand-sub">{{ $roleLabel ?? '' }}</small>
            </div>

            <nav class="sidebar-nav">
                @yield('sidebar')
            </nav>

            <div class="sidebar-footer">
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button class="btn btn-outline-light btn-sm w-100">
                        <i class="bi bi-box-arrow-right"></i> Sign out
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main content area --}}
        <div class="app-main">
            {{-- Top bar --}}
            <header class="app-topbar">
                <button class="btn btn-link d-lg-none topbar-toggle"
                    @click="document.querySelector('.app-sidebar').classList.toggle('open')">
                    <i class="bi bi-list"></i>
                </button>

                <div class="topbar-title">
                    <h5 class="m-0">@yield('page-title', 'Dashboard')</h5>
                    @hasSection('page-subtitle')
                        <small class="text-muted">@yield('page-subtitle')</small>
                    @endif
                </div>

                <div class="topbar-actions ms-auto">
                    @include('components.notification-bell')
                    <div class="user-chip ms-2">
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
                        <div class="user-meta d-none d-md-block">
                            <div class="user-name">{{ $name }}</div>
                            <div class="user-role">{{ $roleLabel ?? '' }}</div>
                        </div>
                    </div>
                </div>
            </header>

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
</body>

</html>
