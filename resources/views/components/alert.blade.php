{{-- resources/views/components/alert.blade.php --}}
@props([
    'type' => 'info', // info, success, warning, danger, primary, secondary
    'title' => null,
    'icon' => null,
    'dismissible' => false,
    'outlined' => false,
])

@php
    $icons = [
        'info' => 'fa-info-circle',
        'success' => 'fa-check-circle',
        'warning' => 'fa-exclamation-triangle',
        'danger' => 'fa-times-circle',
        'primary' => 'fa-info-circle',
        'secondary' => 'fa-info-circle',
    ];
    $alertIcon = $icon ?? ($icons[$type] ?? 'fa-info-circle');

    $alertClass = 'alert alert-' . $type;
    if ($dismissible) {
        $alertClass .= ' alert-dismissible fade show';
    }
    if ($outlined) {
        $alertClass .= ' alert-outline';
    }
@endphp

<div {{ $attributes->merge(['class' => $alertClass, 'role' => 'alert']) }}>
    <div class="d-flex align-items-start">
        <i class="fas {{ $alertIcon }} mr-2 mt-1"></i>
        <div class="flex-grow-1">
            @if($title)
                <h5 class="alert-heading mb-1">{{ $title }}</h5>
            @endif
            {{ $slot }}
        </div>
        @if($dismissible)
            <button type="button" class="close ml-2" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        @endif
    </div>
</div>
