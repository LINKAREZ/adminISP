<?php

namespace App\Modules\Instalaciones\Policies;

use App\Modules\ControlAcceso\Models\User;
use App\Modules\Instalaciones\Models\OrdenInstalacion;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrdenInstalacionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('instalaciones.read');
    }

    public function view(User $user, OrdenInstalacion $orden): bool
    {
        return $user->hasPermission('instalaciones.read');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('instalaciones.create');
    }

    public function update(User $user, OrdenInstalacion $orden): bool
    {
        return $user->hasPermission('instalaciones.update');
    }

    public function delete(User $user, OrdenInstalacion $orden): bool
    {
        return $user->hasPermission('instalaciones.delete');
    }
}
