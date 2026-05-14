@extends('layouts.app', ['roleLabel' => 'Student', 'bodyClass' => 'theme-student'])

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
