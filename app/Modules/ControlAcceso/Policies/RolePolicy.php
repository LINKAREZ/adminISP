<?php

namespace App\Modules\ControlAcceso\Policies;

use App\Modules\ControlAcceso\Models\Role;
use App\Modules\ControlAcceso\Models\User;

class RolePolicy
{
    /**
     * Determinar si el usuario puede ver cualquier modelo.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('control-acceso.read');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermission('control-acceso.read');
    }

    /**
     * Determinar si el usuario puede crear modelos.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('control-acceso.create');
    }

    /**
     * Determinar si el usuario puede actualizar el modelo.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->hasPermission('control-acceso.update');
    }

    /**
     * Determinar si el usuario puede eliminar el modelo.
     */
    public function delete(User $user, Role $role): bool
    {
        // No permitir eliminar si tiene usuarios asignados
        if ($role->users()->count() > 0) {
            return false;
        }

        return $user->hasPermission('control-acceso.delete');
    }
}
