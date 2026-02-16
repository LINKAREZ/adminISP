{{-- resources/views/components/status-badge.blade.php --}}
@props([
    'status',
    'type' => 'servicio', // servicio, recibo, promesa, usuario, general
    'showIcon' => true,
    'size' => null, // sm, lg
    'pill' => false,
])

@php
    use App\Core\Helpers\FormatHelper;

    $statusInfo = match($type) {
        'servicio' => FormatHelper::estadoServicio($status),
        'recibo' => FormatHelper::estadoRecibo($status),
        'promesa' => match($status) {
            'pendiente' => ['label' => 'Pendiente', 'color' => 'info', 'icon' => 'fa-clock'],
            'vencida' => ['label' => 'Vencida', 'color' => 'danger', 'icon' => 'fa-exclamation-circle'],
            'cumplida' => ['label' => 'Cumplida', 'color' => 'success', 'icon' => 'fa-check-circle'],
            'cancelada' => ['label' => 'Cancelada', 'color' => 'secondary', 'icon' => 'fa-times-circle'],
            default => ['label' => ucfirst($status), 'color' => 'secondary', 'icon' => 'fa-question-circle'],
        },
        'usuario' => match($status) {
            'activo', 'active' => ['label' => 'Activo', 'color' => 'success', 'icon' => 'fa-check'],
            'inactivo', 'inactive' => ['label' => 'Inactivo', 'color' => 'danger', 'icon' => 'fa-times-circle'],
            'pendiente', 'pending' => ['label' => 'Pendiente', 'color' => 'warning', 'icon' => 'fa-clock'],
            default => ['label' => ucfirst($status), 'color' => 'secondary', 'icon' => 'fa-question-circle'],
        },
        'conexion' => match($status) {
            'online', 'conectado' => ['label' => 'Conectado', 'color' => 'success', 'icon' => 'fa-wifi'],
            'offline', 'desconectado' => ['label' => 'Desconectado', 'color' => 'danger', 'icon' => 'fa-times'],
            default => ['label' => ucfirst($status), 'color' => 'secondary', 'icon' => 'fa-question-circle'],
        },
        default => match($status) {
            'activo', 'active', 'success', 'aprobado', 'completado' => ['label' => ucfirst($status), 'color' => 'success', 'icon' => 'fa-check-circle'],
            'pendiente', 'pending', 'warning', 'en_proceso' => ['label' => ucfirst($status), 'color' => 'warning', 'icon' => 'fa-clock'],
            'inactivo', 'inactive', 'danger', 'error', 'rechazado', 'vencido' => ['label' => ucfirst($status), 'color' => 'danger', 'icon' => 'fa-times-circle'],
            'info', 'nuevo' => ['label' => ucfirst($status), 'color' => 'info', 'icon' => 'fa-info-circle'],
            default => ['label' => ucfirst($status), 'color' => 'secondary', 'icon' => 'fa-question-circle'],
        },
    };

    $badgeClasses = [
        'badge',
        'badge-' . $statusInfo['color'],
        $pill ? 'badge-pill' : '',
        $size === 'sm' ? 'badge-sm' : '',
        $size === 'lg' ? 'badge-lg' : '',
    ];
    if ($type === 'usuario' && in_array($status, ['activo', 'active'], true)) {
        $badgeClasses[] = 'status-activo';
    }
@endphp

<span {{ $attributes->merge(['class' => implode(' ', array_filter($badgeClasses))]) }}>
    @if($showIcon)
        <i class="fas {{ $statusInfo['icon'] }} mr-1"></i>
    @endif
    {{ $statusInfo['label'] }}
</span>
