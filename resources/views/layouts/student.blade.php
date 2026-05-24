@extends('layouts.app', ['roleLabel' => 'Student', 'bodyClass' => 'theme-student'])

@section('nav-items')
    <li>
        <a href="{{ route('student.dashboard') }}"
           class="et-nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
            <i class="bi bi-house-door"></i>
            <span class="label">Dashboard</span>
        </a>
    </li>

    <li>
        <a href="{{ route('student.profile') }}"
           class="et-nav-link {{ request()->routeIs('student.profile') ? 'active' : '' }}">
            <i class="bi bi-person-badge"></i>
            <span class="label">My Profile</span>
        </a>
    </li>

    {{-- Reports dropdown (Submit a Report + My Reports) --}}
    <li class="et-nav-dropdown {{ request()->routeIs('student.crisis.*') || request()->routeIs('student.reports.*') ? 'open' : '' }}">
        <a href="#"
           class="et-nav-link {{ request()->routeIs('student.crisis.*') || request()->routeIs('student.reports.*') ? 'active' : '' }}">
            <i class="bi bi-plus-circle"></i>
            <span class="label">Submit a Report</span>
            <i class="bi bi-chevron-down chev"></i>
        </a>
        <div class="et-dropdown-panel">
            <a href="{{ route('student.crisis.create') }}"
               class="et-dropdown-item {{ request()->routeIs('student.crisis.create') ? 'active' : '' }}">
                <i class="bi bi-plus-square"></i>
                <span>
                    New Report
                    <span class="et-dropdown-item-sub">Submit a new crisis report</span>
                </span>
            </a>
            <a href="{{ route('student.reports.index') }}"
               class="et-dropdown-item {{ request()->routeIs('student.reports.*') || request()->routeIs('student.crisis.show') || request()->routeIs('student.crisis.edit') ? 'active' : '' }}">
                <i class="bi bi-folder2-open"></i>
                <span>
                    My Reports
                    <span class="et-dropdown-item-sub">View, edit, and track your reports</span>
                </span>
            </a>
        </div>
    </li>

    {{-- Legacy Messages dropdown --}}
    <li class="et-nav-dropdown {{ request()->routeIs('student.ldms.*') ? 'open' : '' }}">
        <a href="#"
           class="et-nav-link {{ request()->routeIs('student.ldms.*') ? 'active' : '' }}">
            <i class="bi bi-envelope-paper"></i>
            <span class="label">Legacy Messages</span>
            <i class="bi bi-chevron-down chev"></i>
        </a>
        <div class="et-dropdown-panel">
            <a href="{{ route('student.ldms.index') }}"
               class="et-dropdown-item {{ request()->routeIs('student.ldms.index') ? 'active' : '' }}">
                <i class="bi bi-inbox"></i>
                <span>
                    My Messages
                    <span class="et-dropdown-item-sub">View your saved messages</span>
                </span>
            </a>
            <a href="{{ route('student.ldms.create') }}"
               class="et-dropdown-item {{ request()->routeIs('student.ldms.create') ? 'active' : '' }}">
                <i class="bi bi-plus-square"></i>
                <span>
                    New Message
                    <span class="et-dropdown-item-sub">Create a new digital legacy</span>
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
