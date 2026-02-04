{{-- resources/views/components/badge.blade.php --}}
@props([
    'variant' => 'secondary', // primary, secondary, success, danger, warning, info, light, dark
    'pill' => false,
    'icon' => null,
    'size' => null, // sm, lg
    'class' => '',
])

@php
    $baseClasses = [
        'badge',
        'badge-' . $variant,
        $pill ? 'badge-pill' : '',
        $size === 'sm' ? 'badge-sm' : '',
        $size === 'lg' ? 'badge-lg' : '',
        $class,
    ];
    $combinedClass = implode(' ', array_filter($baseClasses));
@endphp

<span {{ $attributes->merge(['class' => $combinedClass]) }}>
    @if($icon)<i class="fas {{ $icon }} mr-1"></i>@endif{{ $slot }}
</span>
