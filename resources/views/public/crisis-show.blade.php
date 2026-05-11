@extends('layouts.public')
@section('title', 'Case #' . $crisis->crisis_id . ' - e-Tawassul')

@section('content')
<section class="container py-4">
    <a href="{{ route('home') }}" class="btn btn-link p-0 mb-3"><i class="bi bi-arrow-left"></i> Back to all cases</a>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="content-card">
                <div class="d-flex align-items-center mb-3">
                    <x-priority-badge :level="$crisis->impact_level" />
                    <span class="badge bg-secondary ms-2">{{ ucfirst($crisis->status) }}</span>
                    <small class="text-muted ms-auto"><i class="bi bi-calendar3"></i> {{ $crisis->date_reported?->format('d M Y') }}</small>
                </div>

                <h2 class="mb-1">{{ ucwords(str_replace('_', ' ', $crisis->crisis_type)) }}</h2>
                @if($crisis->location)
                    <p class="text-muted"><i class="bi bi-geo-alt"></i> {{ $crisis->location }}</p>
                @endif

                <hr>

                <h6 class="text-uppercase text-muted small">Description</h6>
                <p>{{ $crisis->crisis_description }}</p>

                @if($crisis->crisis_details)
                    <h6 class="text-uppercase text-muted small mt-4">Additional details</h6>
                    <p>{{ $crisis->crisis_details }}</p>
                @endif

                @php $verified = $crisis->reports->firstWhere('report_status', 'verified'); @endphp
                @if($verified && $verified->blockchain_hash)
                    <div class="mt-4">
                        <h6 class="text-uppercase text-muted small">Blockchain Verification</h6>
                        <x-blockchain-badge :hash="$verified->blockchain_hash" label="Verified by Administrator" />
                    </div>
                @endif

                <div class="d-flex gap-2 mt-4">
                    <a href="{{ route('donate.create', $crisis->crisis_id) }}" class="btn btn-success">
                        <i class="bi bi-heart-fill"></i> Donate to this case
                    </a>
                    <a href="{{ route('pdf.crisis', $crisis->crisis_id) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-file-earmark-pdf"></i> Download Case Receipt
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="content-card">
                <h6 class="text-uppercase text-muted small mb-3">Funding Progress</h6>
                <x-donation-progress :crisis="$crisis" />
                <div class="row text-center mt-3">
                    <div class="col-6 border-end">
                        <div class="fw-bold fs-5">{{ $donorCount }}</div>
                        <small class="text-muted">Supporters</small>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold fs-5">{{ $crisis->donations()->count() }}</div>
                        <small class="text-muted">Donations</small>
                    </div>
                </div>
            </div>

            <div class="content-card mt-3">
                <h6 class="text-uppercase text-muted small mb-3">Recent Donations</h6>
                @forelse($recentDonations as $d)
                    <div class="donation-row">
                        <div>
                            <strong>{{ $d->donor_name }}</strong>
                            @if($d->support_message)
                                <small class="d-block text-muted">"{{ \Illuminate\Support\Str::limit($d->support_message, 60) }}"</small>
                            @endif
                        </div>
                        <div class="text-end">
                            <strong class="text-success">RM {{ number_format($d->donation_amount, 0) }}</strong>
                            <small class="d-block text-muted">{{ $d->donation_date?->diffForHumans() }}</small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Be the first to donate.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
