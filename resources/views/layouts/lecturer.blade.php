@extends('layouts.app', ['roleLabel' => 'Lecturer', 'bodyClass' => 'theme-lecturer'])

@section('sidebar')
    <a href="{{ route('lecturer.dashboard') }}" class="nav-link {{ request()->routeIs('lecturer.dashboard') ? 'active' : '' }}">
        <i class="bi bi-house-door"></i> Dashboard
    </a>
    <div class="nav-section">Notifications</div>
    <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
        <i class="bi bi-bell"></i> All Notifications
    </a>
@endsection
