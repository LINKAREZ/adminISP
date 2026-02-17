<?php

namespace App\Modules\Infraestructura\Policies;

use App\Modules\ControlAcceso\Models\User;
use App\Modules\Infraestructura\Models\Mufa;

class MufaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('infraestructura.read');
    }

    public function view(User $user, Mufa $mufa): bool
    {
        if (!$user->hasPermission('infraestructura.read')) return false;
        if ($user->isSuperAdmin()) return true;
        return $user->isp_id === $mufa->isp_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('infraestructura.create');
    }

    public function update(User $user, Mufa $mufa): bool
    {
        if (!$user->hasPermission('infraestructura.update')) return false;
        if ($user->isSuperAdmin()) return true;
        return $user->isp_id === $mufa->isp_id;
    }

    public function delete(User $user, Mufa $mufa): bool
    {
        if (!$user->hasPermission('infraestructura.delete')) return false;
        if ($user->isSuperAdmin()) return true;
        return $user->isp_id === $mufa->isp_id;
    }
}
