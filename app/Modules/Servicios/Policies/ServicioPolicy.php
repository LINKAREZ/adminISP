<?php

namespace App\Modules\Servicios\Policies;

use App\Modules\Servicios\Models\Servicio;
use App\Modules\ControlAcceso\Models\User;

class ServicioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('servicios.read');
    }

    public function view(User $user, Servicio $servicio): bool
    {
        return $user->hasPermission('servicios.read');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('servicios.create');
    }

    public function update(User $user, Servicio $servicio): bool
    {
        return $user->hasPermission('servicios.update');
    }

    public function delete(User $user, Servicio $servicio): bool
    {
        return $user->hasPermission('servicios.delete');
    }
}
