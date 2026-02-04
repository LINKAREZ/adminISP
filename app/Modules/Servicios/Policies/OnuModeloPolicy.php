<?php

namespace App\Modules\Servicios\Policies;

use App\Modules\ControlAcceso\Models\User;
use App\Modules\Servicios\Models\OnuModelo;

class OnuModeloPolicy
{
    /**
     * Ver listado de modelos
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('sistema.read');
    }

    public function view(User $user, OnuModelo $modelo): bool
    {
        return $user->hasPermission('sistema.read');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('sistema.create');
    }

    public function update(User $user, OnuModelo $modelo): bool
    {
        return $user->hasPermission('sistema.update');
    }

    /**
     * Eliminar modelos
     */
    public function delete(User $user, OnuModelo $modelo): bool
    {
        return $user->hasPermission('sistema.delete');
    }
}
