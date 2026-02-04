{{-- resources/views/components/info-box.blade.php --}}
@props([
    'icon' => 'fas fa-info',
    'variant' => 'info', // primary, info, success, warning, danger, secondary
    'title' => '',
    'value' => '',
    'description' => null,
    'progress' => null, // 0-100 para mostrar barra de progreso
    'progressVariant' => null, // Color de la barra de progreso
    'link' => null,
    'linkText' => 'Ver más',
    'class' => '',
])

@php
    $bgClasses = [
        'primary' => 'bg-primary',
        'info' => 'bg-info',
        'success' => 'bg-success',
        'warning' => 'bg-warning',
        'danger' => 'bg-danger',
        'secondary' => 'bg-secondary',
    ];
    $bgClass = $bgClasses[$variant] ?? 'bg-info';
@endphp

<div {{ $attributes->merge(['class' => 'info-box ' . $class]) }}>
    <span class="info-box-icon {{ $bgClass }}">
        <i class="{{ $icon }}"></i>
    </span>
    <div class="info-box-content">
        <span class="info-box-text">{{ $title }}</span>
        <span class="info-box-number">{{ $value }}</span>

        @if($description)
            <span class="info-box-text text-muted small">{{ $description }}</span>
        @endif

        @if($progress !== null)
            <div class="progress progress-sm mt-2">
                <div class="progress-bar {{ $progressVariant ? 'bg-' . $progressVariant : $bgClass }}"
                     style="width: {{ $progress }}%"
                     role="progressbar"
                     aria-valuenow="{{ $progress }}"
                     aria-valuemin="0"
                     aria-valuemax="100">
                </div>
            </div>
        @endif

        @if($link)
            <a href="{{ $link }}" class="small-box-footer mt-2 d-inline-block">
                {{ $linkText }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        @endif
    </div>
</div>
