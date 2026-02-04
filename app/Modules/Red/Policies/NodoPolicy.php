<?php

namespace App\Modules\Red\Policies;

use App\Modules\ControlAcceso\Models\User;
use App\Modules\Red\Models\Nodo;

class NodoPolicy
{
    /**
     * Ver listado de nodos
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('red.read');
    }

    public function view(User $user, Nodo $nodo): bool
    {
        if (!$user->hasPermission('red.read')) {
            return false;
        }

        // Super admin puede ver todos
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Verificar que pertenezcan al mismo ISP
        return $user->isp_id === $nodo->isp_id;
    }

    /**
     * Crear nodos
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('red.create');
    }

    /**
     * Actualizar nodos
     */
    public function update(User $user, Nodo $nodo): bool
    {
        if (!$user->hasPermission('red.update')) {
            return false;
        }

        // Super admin puede actualizar todos
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Verificar que pertenezcan al mismo ISP
        return $user->isp_id === $nodo->isp_id;
    }

    /**
     * Eliminar nodos
     */
    public function delete(User $user, Nodo $nodo): bool
    {
        if (!$user->hasPermission('red.delete')) {
            return false;
        }

        // Super admin puede eliminar todos
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Verificar que pertenezcan al mismo ISP
        return $user->isp_id === $nodo->isp_id;
    }
}
