<?php

namespace App\Modules\ControlAcceso\Policies;

use App\Modules\ControlAcceso\Models\Permission;
use App\Modules\ControlAcceso\Models\User;

class PermissionPolicy
{
    /**
     * Determinar si el usuario puede ver cualquier modelo.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('control-acceso.read');
    }

    public function view(User $user, Permission $permission): bool
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
    public function update(User $user, Permission $permission = null): bool
    {
        return $user->hasPermission('control-acceso.update');
    }

    /**
     * Determinar si el usuario puede eliminar el modelo.
     */
    public function delete(User $user, Permission $permission = null): bool
    {
        return $user->hasPermission('control-acceso.delete');
    }
}
