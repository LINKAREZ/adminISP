{{--
    Componente info-box estilo AdminLTE.
    Uso: <x-info-box title="Total" :value="100" description="Descripción" icon="fa-users" variant="primary" :link="route('...')" linkText="Ver más" />
--}}
@props([
    'title' => '',
    'value' => '',
    'description' => null,
    'icon' => 'fa-chart-bar',
    'variant' => 'info', // info, success, warning, danger, primary, secondary
    'link' => null,
    'linkText' => 'Ver más',
])

@php
    $variantClass = 'bg-' . $variant;
    $iconClass = str_contains($icon ?? '', ' ') ? ($icon ?? 'fa-chart-bar') : 'fas ' . ($icon ?? 'fa-chart-bar');
@endphp

<div {{ $attributes->merge(['class' => 'info-box ' . $variantClass]) }}>
    <span class="info-box-icon"><i class="{{ $iconClass }}"></i></span>
    <div class="info-box-content">
        <span class="info-box-text">{{ $title }}</span>
        <span class="info-box-number">{{ $value }}</span>
        @if($description)
            <span class="progress-description">{{ $description }}</span>
        @endif
    </div>
    @if($link)
        <div class="small-box-footer">
            <a href="{{ $link }}" class="d-block text-white text-center py-2">
                {{ $linkText }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    @endif
</div>
