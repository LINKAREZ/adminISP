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
        if (!$user->hasPermission('servicios.read')) {
            return false;
        }
        if ($user->isSuperAdmin()) {
            return true;
        }
        return (int) $user->isp_id === (int) $servicio->isp_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('servicios.create');
    }

    public function update(User $user, Servicio $servicio): bool
    {
        if (!$user->hasPermission('servicios.update')) {
            return false;
        }
        if ($user->isSuperAdmin()) {
            return true;
        }
        return (int) $user->isp_id === (int) $servicio->isp_id;
    }

    public function delete(User $user, Servicio $servicio): bool
    {
        if (!$user->hasPermission('servicios.delete')) {
            return false;
        }
        if ($user->isSuperAdmin()) {
            return true;
        }
        return (int) $user->isp_id === (int) $servicio->isp_id;
    }
}
