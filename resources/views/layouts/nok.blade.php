@extends('layouts.app', ['roleLabel' => 'Next of Kin', 'bodyClass' => 'theme-nok'])

@section('sidebar')
    <a href="{{ route('nok.dashboard') }}" class="nav-link {{ request()->routeIs('nok.dashboard') ? 'active' : '' }}">
        <i class="bi bi-house-door"></i> Dashboard
    </a>
    <div class="nav-section">Submissions</div>
    <a href="{{ route('nok.death.create') }}" class="nav-link {{ request()->routeIs('nok.death.*') ? 'active' : '' }}">
        <i class="bi bi-file-earmark-medical"></i> Submit Death Confirmation
    </a>
    <div class="nav-section">Notifications</div>
    <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
        <i class="bi bi-bell"></i> All Notifications
    </a>
@endsection
