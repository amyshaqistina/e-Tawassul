@extends('layouts.public')
@section('title', 'Active Crisis Cases - e-Tawassul')

@section('content')
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1 class="hero-title">Supporting our students,<br>through every hardship.</h1>
                    <p class="hero-subtitle">
                        e-Tawassul is a transparent, blockchain-verified crisis response system
                        for the IIUM community. Every case is verified by administrators and every
                        donation is permanently recorded for full accountability.
                    </p>
                    <div class="hero-actions">
                        <a href="#cases" class="btn btn-primary btn-lg">
                            <i class="bi bi-heart-fill"></i> View Active Cases
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-lg ms-2">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <div class="hero-stat-value">{{ $stats['total_active'] }}</div>
                            <div class="hero-stat-label">Active Cases</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-value">{{ $stats['total_resolved'] }}</div>
                            <div class="hero-stat-label">Resolved</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-value">RM {{ number_format($stats['total_raised'], 0) }}</div>
                            <div class="hero-stat-label">Total Raised</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-value">{{ $stats['total_supporters'] }}</div>
                            <div class="hero-stat-label">Supporters</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container py-5" id="cases">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="section-title mb-1">Active cases that need support</h2>
                <p class="text-muted mb-0">Every case has been verified by IIUM administrators.</p>
            </div>
        </div>

        @if($activeCrises->isEmpty())
            <div class="empty-state">
                <i class="bi bi-emoji-smile"></i>
                <h5 class="mt-3">No active cases at this time</h5>
                <p class="text-muted">Alhamdulillah, there are no active crisis cases requiring support right now.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach($activeCrises as $crisis)
                    <div class="col-md-6 col-lg-4">
                        <x-crisis-card :crisis="$crisis" />
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="trust-section" id="about">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="trust-icon"><i class="bi bi-shield-check"></i></div>
                    <h5>Blockchain Verified</h5>
                    <p class="text-muted small">Every verification and donation is permanently recorded on a permissioned audit chain — tamper-evident, transparent, and auditable.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="trust-icon"><i class="bi bi-people"></i></div>
                    <h5>Administrator Verified</h5>
                    <p class="text-muted small">Every case is reviewed and verified by IIUM administrators before being made public. We protect students and donors alike.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="trust-icon"><i class="bi bi-lock"></i></div>
                    <h5>Securely Encrypted</h5>
                    <p class="text-muted small">Legacy messages from students are end-to-end encrypted and only released to verified next-of-kin under strict access controls.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
