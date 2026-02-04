<?php

namespace App\Modules\ControlAcceso\Policies;

use App\Modules\ControlAcceso\Models\User;

class UserPolicy
{
    /**
     * Super administrador puede hacer todo en usuarios, excepto eliminar: se evalúa en delete().
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($ability === 'delete') {
            return null; // Evaluar en delete() (no auto-eliminación, solo root puede eliminar is_default_admin)
        }
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }
        return null;
    }

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
        // Nadie puede eliminarse a sí mismo
        if ($user->id === $model->id) {
            return false;
        }

        // Admins por defecto: solo root puede eliminarlos
        if ($model->is_default_admin ?? false) {
            return $user->isRootUser();
        }

        // Super administrador puede eliminar a otros (excepto is_default_admin)
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        return $user->hasPermission('control-acceso.delete');
    }
}
