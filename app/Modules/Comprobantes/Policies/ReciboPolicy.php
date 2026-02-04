<?php

namespace App\Modules\Comprobantes\Policies;

use App\Modules\Comprobantes\Models\Recibo;
use App\Modules\ControlAcceso\Models\User;

class ReciboPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('comprobantes.read');
    }

    public function view(User $user, Recibo $recibo): bool
    {
        return $user->hasPermission('comprobantes.read');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('comprobantes.create');
    }

    public function update(User $user, Recibo $recibo): bool
    {
        return $user->hasPermission('comprobantes.update');
    }

    public function delete(User $user, Recibo $recibo): bool
    {
        return $user->hasPermission('comprobantes.delete');
    }
}
