{{--
    Priority/impact badge — colored according to impact_level.
    Props: $level (critical|high|medium|low)
--}}
@props(['level' => 'medium'])

@php
    $map = [
        'critical' => ['bg' => 'danger',    'icon' => 'exclamation-octagon-fill', 'label' => 'Critical'],
        'high'     => ['bg' => 'warning',   'icon' => 'exclamation-triangle-fill','label' => 'High'],
        'medium'   => ['bg' => 'info',      'icon' => 'info-circle-fill',         'label' => 'Medium'],
        'low'      => ['bg' => 'primary',   'icon' => 'circle-fill',              'label' => 'Low'],
    ];
    $info = $map[$level] ?? ['bg' => 'secondary', 'icon' => 'question-circle', 'label' => ucfirst($level)];
@endphp

<span class="priority-badge priority-{{ $level }}">
    <i class="bi bi-{{ $info['icon'] }}"></i> {{ $info['label'] }}
</span>
