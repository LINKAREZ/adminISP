<?php

namespace App\Modules\Notificaciones\Policies;

use App\Modules\ControlAcceso\Models\User;
use App\Modules\Notificaciones\Models\PlantillaWhatsApp;

class PlantillaWhatsAppPolicy
{
    /**
     * Ver listado de plantillas
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('sistema.read');
    }

    public function view(User $user, PlantillaWhatsApp $plantilla): bool
    {
        return $user->hasPermission('sistema.read');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('sistema.create');
    }

    public function update(User $user, PlantillaWhatsApp $plantilla): bool
    {
        return $user->hasPermission('sistema.update');
    }

    /**
     * Eliminar plantillas
     */
    public function delete(User $user, PlantillaWhatsApp $plantilla): bool
    {
        return $user->hasPermission('sistema.delete');
    }
}
