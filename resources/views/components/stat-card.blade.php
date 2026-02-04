{{-- resources/views/components/stat-card.blade.php --}}
@props([
    'title',
    'value',
    'icon' => null,
    'variant' => 'primary', // primary, success, danger, warning, info
    'description' => null,
    'trend' => null, // 'up', 'down', 'neutral'
    'trendValue' => null, // '+15%', '-5%', etc.
    'link' => null,
    'linkText' => 'Ver detalles',
])

@php
    $bgClasses = [
        'primary' => 'bg-primary-light',
        'success' => 'bg-success-light',
        'danger' => 'bg-danger-light',
        'warning' => 'bg-warning-light',
        'info' => 'bg-info-light',
    ];
    $bgClass = $bgClasses[$variant] ?? 'bg-primary-light';

    $trendColors = [
        'up' => 'text-success',
        'down' => 'text-danger',
        'neutral' => 'text-secondary',
    ];
    $trendColor = $trendColors[$trend] ?? 'text-secondary';

    $trendIcons = [
        'up' => 'fa-arrow-up',
        'down' => 'fa-arrow-down',
        'neutral' => 'fa-minus',
    ];
    $trendIcon = $trendIcons[$trend] ?? 'fa-minus';
@endphp

<div {{ $attributes->merge(['class' => 'stat-card ' . $bgClass]) }}>
    @if($icon)
        <div class="stat-icon mb-2">
            <i class="fas {{ $icon }} fa-2x opacity-50"></i>
        </div>
    @endif

    <div class="stat-value">{{ $value }}</div>
    <div class="stat-label">{{ $title }}</div>

    @if($description || $trendValue)
        <div class="mt-2 small">
            @if($trendValue)
                <span class="{{ $trendColor }}">
                    <i class="fas {{ $trendIcon }}"></i>
                    {{ $trendValue }}
                </span>
            @endif
            @if($description)
                <span class="text-muted ml-1">{{ $description }}</span>
            @endif
        </div>
    @endif

    @if($link)
        <a href="{{ $link }}" class="stretched-link"></a>
    @endif
</div>
