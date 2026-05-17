@extends('layouts.app', ['roleLabel' => 'Next of Kin', 'bodyClass' => 'theme-nok'])

@section('sidebar')
    <a href="{{ route('nok.dashboard') }}" class="nav-link {{ request()->routeIs('nok.dashboard') ? 'active' : '' }}">
        <i class="bi bi-house-door"></i> Dashboard
    </a>
    <div class="nav-section">Submissions</div>
    <a href="{{ route('nok.death.create') }}" class="nav-link {{ request()->routeIs('nok.death.*') ? 'active' : '' }}">
        <i class="bi bi-file-earmark-medical"></i> Submit Death Confirmation
    </a>
    <a href="{{ route('nok.crisis.create') }}" class="nav-link {{ request()->routeIs('nok.crisis.*') ? 'active' : '' }}">
        <i class="bi bi-exclamation-triangle"></i> Report a Crisis
    </a>
@endsection
