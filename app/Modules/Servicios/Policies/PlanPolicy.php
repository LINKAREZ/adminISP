<?php

namespace App\Modules\Servicios\Policies;

use App\Modules\Servicios\Models\Plan;
use App\Modules\ControlAcceso\Models\User;

class PlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('servicios.read');
    }

    public function view(User $user, Plan $plan): bool
    {
        return $user->hasPermission('servicios.read');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('servicios.create');
    }

    public function update(User $user, Plan $plan): bool
    {
        return $user->hasPermission('servicios.update');
    }

    public function delete(User $user, Plan $plan): bool
    {
        return $user->hasPermission('servicios.delete');
    }
}
