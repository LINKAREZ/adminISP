<?php

namespace App\Modules\Comprobantes\Policies;

use App\Modules\Comprobantes\Models\Pago;
use App\Modules\ControlAcceso\Models\User;

class PagoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('comprobantes.read');
    }

    public function view(User $user, Pago $pago): bool
    {
        return $user->hasPermission('comprobantes.read');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('comprobantes.create');
    }

    public function update(User $user, Pago $pago): bool
    {
        return $user->hasPermission('comprobantes.update');
    }

    public function delete(User $user, Pago $pago): bool
    {
        return $user->hasPermission('comprobantes.delete');
    }
}
