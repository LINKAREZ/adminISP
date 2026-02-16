@props(['role'])

@php
    $roleConfig = [
        'administrador'    => ['color' => 'badge-danger',  'label' => 'Administrador', 'icon' => 'fa-user-shield'],
        'supervisor'       => ['color' => 'badge-success', 'label' => 'Supervisor',    'icon' => 'fa-user-check'],
        'gerente-finanzas' => ['color' => 'badge-info',    'label' => 'Gerente finanzas', 'icon' => 'fa-user-tie'],
        'cobrador'         => ['color' => 'badge-primary', 'label' => 'Cobrador',      'icon' => 'fa-user'],
        'tecnico'          => ['color' => 'badge-warning', 'label' => 'Técnico',       'icon' => 'fa-user-cog'],
        'soporte'          => ['color' => 'badge-secondary', 'label' => 'Soporte',     'icon' => 'fa-headset'],
        'ayudante'         => ['color' => 'badge-secondary', 'label' => 'Ayudante',     'icon' => 'fa-user-plus'],
    ];

    $roleName = strtolower($role->name ?? (is_string($role) ? $role : ''));
    $config = $roleConfig[$roleName] ?? [
        'color' => 'badge-secondary',
        'label' => is_object($role) ? ($role->name ?? 'Sin rol') : (string) $role,
        'icon' => 'fa-user-tag',
    ];
    $displayLabel = $config['label'] ?? $roleName;
@endphp

<span class="badge role-badge {{ $config['color'] }}" title="{{ $displayLabel }}">
    <i class="fas {{ $config['icon'] }} mr-1"></i>
    {{ $displayLabel }}
</span>
