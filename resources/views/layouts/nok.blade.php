@extends('layouts.app', ['roleLabel' => 'Next of Kin', 'bodyClass' => 'theme-nok'])

@section('nav-items')
    <li>
        <a href="{{ route('nok.dashboard') }}"
           class="et-nav-link {{ request()->routeIs('nok.dashboard') ? 'active' : '' }}">
            <i class="bi bi-house-door"></i>
            <span class="label">Dashboard</span>
        </a>
    </li>

    <li>
        <a href="{{ route('nok.submissions.index') }}"
           class="et-nav-link {{ request()->routeIs('nok.submissions.*') || request()->routeIs('nok.death.show') || request()->routeIs('nok.death.edit') || request()->routeIs('nok.crisis.show') ? 'active' : '' }}">
            <i class="bi bi-folder2-open"></i>
            <span class="label">My Submissions</span>
        </a>
    </li>

    <li>
        <a href="{{ route('nok.death.create') }}"
           class="et-nav-link {{ request()->routeIs('nok.death.create') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-medical"></i>
            <span class="label">Submit Death Confirmation</span>
        </a>
    </li>

    <li>
        <a href="{{ route('notifications.index') }}"
           class="et-nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <i class="bi bi-bell"></i>
            <span class="label">Notifications</span>
        </a>
    </li>
@endsection
