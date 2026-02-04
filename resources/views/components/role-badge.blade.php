@props(['role'])

@php
    $roleColors = [
        'administrador' => 'badge-danger',
        'supervisor' => 'badge-success',
        'cobrador' => 'badge-info',
        'tecnico' => 'badge-primary',
        'ayudante' => 'badge-secondary',
    ];

    $roleName = strtolower($role->name ?? $role);
    $colorClass = $roleColors[$roleName] ?? 'badge-secondary';

    $displayName = is_object($role) ? $role->name : $role;
@endphp

<span class="badge {{ $colorClass }}">
    {{ $displayName }}
</span>
