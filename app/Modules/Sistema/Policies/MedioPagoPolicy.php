<?php

namespace App\Modules\Sistema\Policies;

use App\Modules\ControlAcceso\Models\User;
use App\Modules\Sistema\Models\MedioPago;

class MedioPagoPolicy
{
    /**
     * Ver listado de medios de pago
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('sistema.read');
    }

    public function view(User $user, MedioPago $medioPago): bool
    {
        return $user->hasPermission('sistema.read');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('sistema.create');
    }

    public function update(User $user, MedioPago $medioPago): bool
    {
        return $user->hasPermission('sistema.update');
    }

    /**
     * Eliminar medios de pago
     */
    public function delete(User $user, MedioPago $medioPago): bool
    {
        return $user->hasPermission('sistema.delete');
    }
}
