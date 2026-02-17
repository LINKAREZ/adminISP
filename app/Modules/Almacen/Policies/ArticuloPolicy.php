<?php

namespace App\Modules\Almacen\Policies;

use App\Modules\Almacen\Models\Articulo;
use App\Modules\ControlAcceso\Models\User;

class ArticuloPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('almacen.read');
    }

    public function view(User $user, Articulo $articulo): bool
    {
        return $user->hasPermission('almacen.read');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('almacen.create');
    }

    public function update(User $user, Articulo $articulo): bool
    {
        return $user->hasPermission('almacen.update');
    }

    public function delete(User $user, Articulo $articulo): bool
    {
        return $user->hasPermission('almacen.delete');
    }
}
