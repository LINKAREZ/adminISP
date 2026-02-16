<?php

namespace App\Core\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class IspScope implements Scope
{
    /**
     * Aplicar el scope al query
     */
    public function apply(Builder $builder, Model $model): void
    {
        // No aplicar scope al modelo Isp (es el modelo principal)
        if ($model instanceof \App\Modules\Sistema\Models\Isp) {
            return;
        }

        // No aplicar scope al modelo User
        if ($model instanceof \App\Modules\ControlAcceso\Models\User) {
            return;
        }

        // Roles y permisos son globales: los 5 roles y CRUD se comparten entre ISPs
        if ($model instanceof \App\Modules\ControlAcceso\Models\Role) {
            return;
        }
        if ($model instanceof \App\Modules\ControlAcceso\Models\Permission) {
            return;
        }

        // Modelos con UsesTenantConnection: la BD ya es de un solo ISP, no filtrar por isp_id
        // (evita ocultar filas con isp_id null en tenant y es lógicamente correcto)
        if (in_array(\App\Core\Traits\UsesTenantConnection::class, class_uses_recursive($model), true)) {
            return;
        }

        $ispId = $this->getCurrentIspId();

        if ($ispId) {
            $builder->where($model->qualifyColumn('isp_id'), $ispId);
        }
    }

    /**
     * Obtener el ISP ID actual desde la sesión o usuario autenticado
     */
    protected function getCurrentIspId(): ?int
    {
        // Si hay un usuario autenticado, usar su ISP
        if (auth()->check()) {
            $user = auth()->user();
            if ($user && $user->isp_id) {
                return $user->isp_id;
            }
        }

        // Si hay un ISP en la sesión (para usuarios no autenticados o super admin)
        if (session()->has('current_isp_id')) {
            return session('current_isp_id');
        }

        return null;
    }
}
