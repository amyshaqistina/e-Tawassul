{{--
    Crisis card component for the public dashboard grid.
    Expected vars: $crisis (App\Models\Crisis)
--}}
@php
    /** @var \App\Models\Crisis $crisis */
    $impactColor = $crisis->priority_color;
    $progress = $crisis->progress_percent;
@endphp

<div class="crisis-card h-100">
    <div class="crisis-card-header">
        <x-priority-badge :level="$crisis->impact_level" />
        <small class="text-muted ms-auto">
            <i class="bi bi-calendar3"></i>
            {{ $crisis->date_reported?->diffForHumans() }}
        </small>
    </div>

    <h5 class="crisis-card-title">
        {{ ucwords(str_replace('_', ' ', $crisis->crisis_type)) }}
        @if($crisis->location)
            <small class="text-muted d-block fs-6 fw-normal mt-1">
                <i class="bi bi-geo-alt"></i> {{ $crisis->location }}
            </small>
        @endif
    </h5>

    <p class="crisis-card-body">
        {{ \Illuminate\Support\Str::limit($crisis->crisis_description, 160) }}
    </p>

    <x-donation-progress :crisis="$crisis" />

    <div class="crisis-card-actions mt-3">
        <a href="{{ route('crisis.show', $crisis->crisis_id) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-eye"></i> Details
        </a>
        <a href="{{ route('donate.create', $crisis->crisis_id) }}" class="btn btn-success btn-sm">
            <i class="bi bi-heart"></i> Donate
        </a>
    </div>
</div>
