<?php

namespace App\Modules\Sistema\Policies;

use App\Modules\ControlAcceso\Models\User;
use App\Modules\Sistema\Models\Moneda;

class MonedaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('sistema.read');
    }

    public function view(User $user, Moneda $moneda): bool
    {
        return $user->hasPermission('sistema.read');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('sistema.create');
    }

    public function update(User $user, Moneda $moneda): bool
    {
        return $user->hasPermission('sistema.update');
    }

    public function delete(User $user, Moneda $moneda): bool
    {
        return $user->hasPermission('sistema.delete');
    }
}
