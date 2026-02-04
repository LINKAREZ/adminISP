<?php

namespace App\Modules\ControlAcceso\Policies;

use App\Modules\ControlAcceso\Models\User;

class UserPolicy
{
    /**
     * Determinar si el usuario puede ver cualquier modelo.
     */
    public function viewAny(User $user): bool
    {
        // Usuario root - acceso completo siempre
        if ($user->isRootUser()) {
            return true;
        }
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        return $user->hasPermission('control-acceso.read');
    }

    /**
     * Determinar si el usuario puede ver el modelo.
     */
    public function view(User $user, User $model): bool
    {
        // Usuario root - acceso completo siempre
        if ($user->isRootUser()) {
            return true;
        }

        return $user->hasPermission('control-acceso.read');
    }

    /**
     * Determinar si el usuario puede crear modelos.
     */
    public function create(User $user): bool
    {
        // Usuario root - acceso completo siempre
        if ($user->isRootUser()) {
            return true;
        }

        return $user->hasPermission('control-acceso.create');
    }

    /**
     * Determinar si el usuario puede actualizar el modelo.
     */
    public function update(User $user, User $model): bool
    {
        // Usuario root - acceso completo siempre
        if ($user->isRootUser()) {
            return true;
        }

        return $user->hasPermission('control-acceso.update');
    }

    /**
     * Determinar si el usuario puede eliminar el modelo.
     */
    public function delete(User $user, User $model): bool
    {
        // No permitir eliminar usuarios administradores por defecto (excepto super admin root)
        if ($model->is_default_admin ?? false) {
            // Solo el usuario root puede eliminar admins por defecto
            if (!$user->isRootUser()) {
                return false;
            }
        }

        // Usuario root - acceso completo siempre (excepto auto-eliminación)
        if ($user->isRootUser()) {
            // No permitir eliminar el propio usuario
            if ($user->id === $model->id) {
                return false;
            }
            return true;
        }

        // No permitir eliminar el propio usuario
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasPermission('control-acceso.delete');
    }
}
