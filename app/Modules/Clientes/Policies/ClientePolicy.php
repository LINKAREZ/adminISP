<?php

namespace App\Modules\Clientes\Policies;

use App\Modules\Clientes\Models\Cliente;
use App\Modules\ControlAcceso\Models\User;

class ClientePolicy
{
    /**
     * Determine if the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('clientes.read');
    }

    public function view(User $user, Cliente $cliente): bool
    {
        if (!$user->hasPermission('clientes.read')) {
            return false;
        }

        // Super admin puede ver todos
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Verificar que pertenezcan al mismo ISP
        return $user->isp_id === $cliente->isp_id;
    }

    /**
     * Determine if the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('clientes.create');
    }

    /**
     * Determine if the user can update the model.
     */
    public function update(User $user, Cliente $cliente): bool
    {
        if (!$user->hasPermission('clientes.update')) {
            return false;
        }

        // Super admin puede actualizar todos
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Verificar que pertenezcan al mismo ISP
        return $user->isp_id === $cliente->isp_id;
    }

    /**
     * Determine if the user can delete the model.
     */
    public function delete(User $user, Cliente $cliente): bool
    {
        if (!$user->hasPermission('clientes.delete')) {
            return false;
        }

        // Super admin puede eliminar todos
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Verificar que pertenezcan al mismo ISP
        return $user->isp_id === $cliente->isp_id;
    }
}
