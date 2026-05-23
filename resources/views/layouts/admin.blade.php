@extends('layouts.app', ['roleLabel' => 'Administrator', 'bodyClass' => 'theme-admin'])

@section('nav-items')
    <li>
        <a href="{{ route('admin.dashboard') }}"
           class="et-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span class="label">Dashboard</span>
        </a>
    </li>

    {{-- Case Management dropdown --}}
    <li class="et-nav-dropdown {{ request()->routeIs('admin.crisis.*') || request()->routeIs('admin.death.*') || request()->routeIs('admin.ldms.*') || request()->routeIs('admin.donations.*') ? 'open' : '' }}">
        <a href="#"
           class="et-nav-link {{ request()->routeIs('admin.crisis.*') || request()->routeIs('admin.death.*') || request()->routeIs('admin.ldms.*') || request()->routeIs('admin.donations.*') ? 'active' : '' }}">
            <i class="bi bi-clipboard-pulse"></i>
            <span class="label">Cases</span>
            <i class="bi bi-chevron-down chev"></i>
        </a>
        <div class="et-dropdown-panel">
            <a href="{{ route('admin.crisis.index') }}"
               class="et-dropdown-item {{ request()->routeIs('admin.crisis.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-pulse"></i>
                <span>
                    Crisis Reports
                    <span class="et-dropdown-item-sub">Active &amp; pending cases</span>
                </span>
            </a>
            <a href="{{ route('admin.death.index') }}"
               class="et-dropdown-item {{ request()->routeIs('admin.death.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-medical"></i>
                <span>
                    Death Confirmations
                    <span class="et-dropdown-item-sub">Verification queue</span>
                </span>
            </a>
            <a href="{{ route('admin.ldms.index') }}"
               class="et-dropdown-item {{ request()->routeIs('admin.ldms.*') ? 'active' : '' }}">
                <i class="bi bi-envelope-paper"></i>
                <span>
                    Last Digital Messages
                    <span class="et-dropdown-item-sub">Encrypted vault</span>
                </span>
            </a>
            <a href="{{ route('admin.donations.index') }}"
               class="et-dropdown-item {{ request()->routeIs('admin.donations.*') ? 'active' : '' }}">
                <i class="bi bi-cash-coin"></i>
                <span>
                    Donations
                    <span class="et-dropdown-item-sub">Transactions &amp; payouts</span>
                </span>
            </a>
        </div>
    </li>

    {{-- Records dropdown --}}
    <li class="et-nav-dropdown {{ request()->routeIs('admin.students.*') || request()->routeIs('admin.blockchain.*') ? 'open' : '' }}">
        <a href="#"
           class="et-nav-link {{ request()->routeIs('admin.students.*') || request()->routeIs('admin.blockchain.*') ? 'active' : '' }}">
            <i class="bi bi-archive"></i>
            <span class="label">Records</span>
            <i class="bi bi-chevron-down chev"></i>
        </a>
        <div class="et-dropdown-panel">
            <a href="{{ route('admin.students.index') }}"
               class="et-dropdown-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                <i class="bi bi-mortarboard"></i>
                <span>
                    Students
                    <span class="et-dropdown-item-sub">Enrolled IIUM students</span>
                </span>
            </a>
            <a href="{{ route('admin.blockchain.index') }}"
               class="et-dropdown-item {{ request()->routeIs('admin.blockchain.*') ? 'active' : '' }}">
                <i class="bi bi-link-45deg"></i>
                <span>
                    Blockchain Audit
                    <span class="et-dropdown-item-sub">Tamper-proof ledger</span>
                </span>
            </a>
        </div>
    </li>

    <li>
        <a href="{{ route('notifications.index') }}"
           class="et-nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <i class="bi bi-bell"></i>
            <span class="label">Notifications</span>
        </a>
    </li>
@endsection
