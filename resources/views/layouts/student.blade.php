@extends('layouts.app', ['roleLabel' => 'Student', 'bodyClass' => 'theme-student'])

@push('styles')
<style>
    /* ============================================================
       Hide sidebar scrollbar — universal selectors so it works
       regardless of what your sidebar class is actually named.
       ============================================================ */
    .theme-student aside,
    .theme-student .sidebar,
    .theme-student [class*="sidebar" i],
    .theme-student [class*="side-nav" i],
    .theme-student nav.app-nav {
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }

    .theme-student aside::-webkit-scrollbar,
    .theme-student .sidebar::-webkit-scrollbar,
    .theme-student [class*="sidebar" i]::-webkit-scrollbar,
    .theme-student [class*="side-nav" i]::-webkit-scrollbar,
    .theme-student nav.app-nav::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
</style>
@endpush

@section('sidebar')
    <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
        <i class="bi bi-house-door"></i> Dashboard
    </a>
    <a href="{{ route('student.profile') }}" class="nav-link {{ request()->routeIs('student.profile') ? 'active' : '' }}">
        <i class="bi bi-person-badge"></i> My Profile
    </a>
    <div class="nav-section">Crisis Reports</div>
    <a href="{{ route('student.crisis.create') }}"
        class="nav-link {{ request()->routeIs('student.crisis.create') ? 'active' : '' }}">
        <i class="bi bi-plus-circle"></i> Submit a Report
    </a>
    <div class="nav-section">Legacy Messages</div>
    <a href="{{ route('student.ldms.index') }}"
        class="nav-link {{ request()->routeIs('student.ldms.*') && !request()->routeIs('student.ldms.create') ? 'active' : '' }}">
        <i class="bi bi-envelope-paper"></i> My Messages
    </a>
    <a href="{{ route('student.ldms.create') }}"
        class="nav-link {{ request()->routeIs('student.ldms.create') ? 'active' : '' }}">
        <i class="bi bi-plus-square"></i> New Message
    </a>
    <div class="nav-section">Notifications</div>
    <a href="{{ route('notifications.index') }}"
        class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
        <i class="bi bi-bell"></i> All Notifications
    </a>
@endsection
