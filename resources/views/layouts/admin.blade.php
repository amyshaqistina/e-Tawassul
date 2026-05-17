@extends('layouts.app', ['roleLabel' => 'Administrator', 'bodyClass' => 'theme-admin'])

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <div class="nav-section">Case Management</div>
    <a href="{{ route('admin.crisis.index') }}" class="nav-link {{ request()->routeIs('admin.crisis.*') ? 'active' : '' }}">
        <i class="bi bi-clipboard-pulse"></i> Crisis Reports
    </a>
    <a href="{{ route('admin.death.index') }}" class="nav-link {{ request()->routeIs('admin.death.*') ? 'active' : '' }}">
        <i class="bi bi-file-earmark-medical"></i> Death Confirmations
    </a>
    <a href="{{ route('admin.donations.index') }}" class="nav-link {{ request()->routeIs('admin.donations.*') ? 'active' : '' }}">
        <i class="bi bi-cash-coin"></i> Donations
    </a>
    <div class="nav-section">Records</div>
    <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
        <i class="bi bi-mortarboard"></i> Students
    </a>
    <a href="{{ route('admin.blockchain.index') }}" class="nav-link {{ request()->routeIs('admin.blockchain.*') ? 'active' : '' }}">
        <i class="bi bi-link-45deg"></i> Blockchain Audit
    </a>
@endsection
