<?php

namespace App\Modules\Red\Policies;

use App\Modules\ControlAcceso\Models\User;
use App\Modules\Red\Models\Router;

class RouterPolicy
{
    /**
     * Ver listado de routers
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('red.read');
    }

    public function view(User $user, Router $router): bool
    {
        return $user->hasPermission('red.read');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('red.create');
    }

    public function update(User $user, Router $router): bool
    {
        return $user->hasPermission('red.update');
    }

    /**
     * Eliminar routers
     */
    public function delete(User $user, Router $router): bool
    {
        return $user->hasPermission('red.delete');
    }
}
