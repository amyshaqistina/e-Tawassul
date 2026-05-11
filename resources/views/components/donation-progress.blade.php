{{--
    Donation progress bar with live polling via Alpine.js
    Expected vars: $crisis (App\Models\Crisis)
--}}
@props(['crisis', 'poll' => true])

@php
    /** @var \App\Models\Crisis $crisis */
    $crisisId = $crisis->crisis_id;
    $initialRaised = (float) $crisis->donation_raised;
    $initialTarget = (float) $crisis->donation_target;
    $initialPercent = $crisis->progress_percent;
    $progressUrl = route('donate.progress', $crisisId);
@endphp

<div class="donation-progress"
     x-data="donationProgress({
         crisisId: {{ $crisisId }},
         raised: {{ $initialRaised }},
         target: {{ $initialTarget }},
         percent: {{ $initialPercent }},
         url: '{{ $progressUrl }}',
         poll: {{ $poll ? 'true' : 'false' }}
     })"
     x-init="init()">

    <div class="d-flex justify-content-between align-items-baseline mb-1">
        <span class="small text-muted">Raised</span>
        <span class="small">
            <strong>RM <span x-text="formatMoney(raised)">{{ number_format($initialRaised, 2) }}</span></strong>
            of
            RM <span x-text="formatMoney(target)">{{ number_format($initialTarget, 2) }}</span>
        </span>
    </div>

    <div class="progress" style="height:10px;">
        <div class="progress-bar bg-success"
             role="progressbar"
             :style="`width:${percent}%`"
             style="width: {{ $initialPercent }}%"
             :aria-valuenow="percent" aria-valuemin="0" aria-valuemax="100">
        </div>
    </div>

    <div class="d-flex justify-content-between mt-1">
        <small class="text-muted"><span x-text="percent">{{ $initialPercent }}</span>% funded</small>
        <small class="text-muted" x-show="lastUpdate" x-cloak>
            <i class="bi bi-arrow-clockwise"></i> updated
        </small>
    </div>
</div>
