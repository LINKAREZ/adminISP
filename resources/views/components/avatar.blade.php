{{-- resources/views/components/avatar.blade.php --}}
@props([
    'name' => null,
    'src' => null,
    'size' => 'md', // xs, sm, md, lg, xl
    'variant' => 'primary', // primary, secondary, success, danger, warning, info
    'rounded' => 'circle', // circle, rounded, none
    'status' => null, // online, offline, away, busy
    'showInitials' => true,
])

@php
    use App\Core\Helpers\FormatHelper;

    $sizes = [
        'xs' => ['width' => '24px', 'height' => '24px', 'font' => '0.6rem'],
        'sm' => ['width' => '32px', 'height' => '32px', 'font' => '0.75rem'],
        'md' => ['width' => '40px', 'height' => '40px', 'font' => '0.875rem'],
        'lg' => ['width' => '56px', 'height' => '56px', 'font' => '1rem'],
        'xl' => ['width' => '80px', 'height' => '80px', 'font' => '1.5rem'],
    ];
    $sizeStyle = $sizes[$size] ?? $sizes['md'];

    $roundedClasses = [
        'circle' => 'rounded-circle',
        'rounded' => 'rounded',
        'none' => '',
    ];
    $roundedClass = $roundedClasses[$rounded] ?? 'rounded-circle';

    $bgClasses = [
        'primary' => 'bg-primary',
        'secondary' => 'bg-secondary',
        'success' => 'bg-success',
        'danger' => 'bg-danger',
        'warning' => 'bg-warning',
        'info' => 'bg-info',
    ];
    $bgClass = $bgClasses[$variant] ?? 'bg-primary';

    $initials = $name && $showInitials ? FormatHelper::iniciales($name) : '?';
@endphp

<div class="avatar-wrapper position-relative d-inline-block" {{ $attributes }}>
    @if($src)
        <img
            src="{{ $src }}"
            alt="{{ $name ?? 'Avatar' }}"
            class="{{ $roundedClass }}"
            style="width: {{ $sizeStyle['width'] }}; height: {{ $sizeStyle['height'] }}; object-fit: cover;"
        >
    @else
        <div
            class="d-flex align-items-center justify-content-center {{ $roundedClass }} {{ $bgClass }} text-white"
            style="width: {{ $sizeStyle['width'] }}; height: {{ $sizeStyle['height'] }}; font-size: {{ $sizeStyle['font'] }}; font-weight: 600;"
            title="{{ $name }}"
        >
            {{ $initials }}
        </div>
    @endif

    @if($status)
        @php
            $statusColors = [
                'online' => 'bg-success',
                'offline' => 'bg-secondary',
                'away' => 'bg-warning',
                'busy' => 'bg-danger',
            ];
            $statusColor = $statusColors[$status] ?? 'bg-secondary';
            $dotSize = $size === 'xs' || $size === 'sm' ? '8px' : '12px';
        @endphp
        <span
            class="position-absolute {{ $statusColor }} rounded-circle border border-white"
            style="width: {{ $dotSize }}; height: {{ $dotSize }}; bottom: 0; right: 0;"
            title="{{ ucfirst($status) }}"
        ></span>
    @endif
</div>
