<?php

namespace App\Core\Traits;

/**
 * Para FormRequests que autorizan por un único permiso (o varios en OR).
 * Reducir duplicación de authorize(): return auth()->check() && auth()->user()->hasPermission('modulo.accion').
 *
 * Uso permiso único: en authorize() devolver $this->authorizePermission('modulo.accion').
 * Uso varios permisos (OR): devolver $this->authorizeAnyPermission(['modulo.create', 'modulo.update']).
 */
trait AuthorizesWithPermission
{
    protected function authorizePermission(string $permission): bool
    {
        return auth()->check() && auth()->user()->hasPermission($permission);
    }

    protected function authorizeAnyPermission(array $permissions): bool
    {
        if (! auth()->check()) {
            return false;
        }
        $user = auth()->user();
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }
}
