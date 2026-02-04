<?php

namespace App\Modules\Sistema\Policies;

use App\Modules\ControlAcceso\Models\User;
use App\Modules\Sistema\Models\OnuMarca;

class OnuMarcaPolicy
{
    /**
     * Ver listado de marcas
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('sistema.read');
    }

    public function view(User $user, OnuMarca $marca): bool
    {
        return $user->hasPermission('sistema.read');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('sistema.create');
    }

    public function update(User $user, OnuMarca $marca): bool
    {
        return $user->hasPermission('sistema.update');
    }

    /**
     * Eliminar marcas
     */
    public function delete(User $user, OnuMarca $marca): bool
    {
        return $user->hasPermission('sistema.delete');
    }
}
