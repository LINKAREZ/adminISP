{{-- resources/views/components/loading.blade.php --}}
@props([
    'text' => 'Cargando...',
    'size' => 'md', // sm, md, lg
    'variant' => 'primary', // primary, secondary, light, dark
    'type' => 'spinner', // spinner, dots, bars
    'overlay' => false, // true para cubrir el contenedor padre
    'inline' => false, // true para mostrar en línea
])

@php
    $spinnerSizes = [
        'sm' => 'spinner-border-sm',
        'md' => '',
        'lg' => 'spinner-border-lg',
    ];
    $spinnerSize = $spinnerSizes[$size] ?? '';

    $textColors = [
        'primary' => 'text-primary',
        'secondary' => 'text-secondary',
        'light' => 'text-light',
        'dark' => 'text-dark',
    ];
    $textColor = $textColors[$variant] ?? 'text-primary';
@endphp

@if($overlay)
<div class="overlay dark" {{ $attributes }}>
    <i class="fas fa-2x fa-sync-alt fa-spin"></i>
    @if($text)
        <div class="text-bold pt-2">{{ $text }}</div>
    @endif
</div>
@elseif($inline)
<span class="d-inline-flex align-items-center {{ $textColor }}" {{ $attributes }}>
    <span class="spinner-border {{ $spinnerSize }} mr-2" role="status" aria-hidden="true"></span>
    @if($text)
        <span>{{ $text }}</span>
    @endif
</span>
@else
<div class="d-flex flex-column align-items-center justify-content-center py-4 {{ $textColor }}" {{ $attributes }}>
    @if($type === 'spinner')
        <div class="spinner-border {{ $spinnerSize }}" role="status">
            <span class="sr-only">{{ $text }}</span>
        </div>
    @elseif($type === 'dots')
        <div class="loading-dots">
            <span></span><span></span><span></span>
        </div>
    @elseif($type === 'bars')
        <div class="loading-bars">
            <span></span><span></span><span></span><span></span>
        </div>
    @endif
    @if($text)
        <div class="mt-2">{{ $text }}</div>
    @endif
</div>
@endif

@once
@push('styles')
<style>
    .loading-dots {
        display: flex;
        gap: 4px;
    }
    .loading-dots span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
        animation: loadingDots 1.4s infinite ease-in-out both;
    }
    .loading-dots span:nth-child(1) { animation-delay: -0.32s; }
    .loading-dots span:nth-child(2) { animation-delay: -0.16s; }

    @keyframes loadingDots {
        0%, 80%, 100% { transform: scale(0); }
        40% { transform: scale(1); }
    }

    .loading-bars {
        display: flex;
        gap: 3px;
        height: 24px;
        align-items: flex-end;
    }
    .loading-bars span {
        width: 4px;
        background: currentColor;
        animation: loadingBars 1s infinite ease-in-out;
    }
    .loading-bars span:nth-child(1) { animation-delay: 0s; }
    .loading-bars span:nth-child(2) { animation-delay: 0.1s; }
    .loading-bars span:nth-child(3) { animation-delay: 0.2s; }
    .loading-bars span:nth-child(4) { animation-delay: 0.3s; }

    @keyframes loadingBars {
        0%, 40%, 100% { height: 8px; }
        20% { height: 24px; }
    }

    .spinner-border-lg {
        width: 3rem;
        height: 3rem;
    }
</style>
@endpush
@endonce
