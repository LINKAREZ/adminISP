<?php

namespace App\Core\Policies;

use App\Modules\ControlAcceso\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;

/**
 * Clase base para Policies
 */
abstract class BasePolicy
{
    use HandlesAuthorization;

    /**
     * Prefijo del permiso (ej: 'clientes', 'servicios')
     */
    protected string $permissionPrefix = '';

    /**
     * Verificar antes de cualquier acción
     */
    public function before(User $user, string $ability): ?bool
    {
        // Usuario root puede hacer todo
        if ($user->isRootUser()) {
            return true;
        }

        return null;
    }

    /**
     * Ver listado
     */
    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read');
    }

    /**
     * Ver detalle
     */
    public function view(User $user, Model $model): bool
    {
        return $this->canAccessModel($user, $model)
            && $this->checkPermission($user, 'read');
    }

    /**
     * Crear
     */
    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create');
    }

    /**
     * Actualizar
     */
    public function update(User $user, Model $model): bool
    {
        return $this->canAccessModel($user, $model)
            && $this->checkPermission($user, 'update');
    }

    /**
     * Eliminar
     */
    public function delete(User $user, Model $model): bool
    {
        return $this->canAccessModel($user, $model)
            && $this->checkPermission($user, 'delete');
    }

    /**
     * Restaurar (para SoftDeletes)
     */
    public function restore(User $user, Model $model): bool
    {
        return $this->canAccessModel($user, $model)
            && $this->checkPermission($user, 'update');
    }

    /**
     * Eliminar permanentemente
     */
    public function forceDelete(User $user, Model $model): bool
    {
        return $this->canAccessModel($user, $model)
            && $this->checkPermission($user, 'delete');
    }

    /**
     * Verificar permiso
     */
    protected function checkPermission(User $user, string $action): bool
    {
        $permission = $this->getPermissionName($action);
        return $user->can($permission);
    }

    /**
     * Obtener nombre del permiso
     */
    protected function getPermissionName(string $action): string
    {
        if (empty($this->permissionPrefix)) {
            return $action;
        }

        return "{$this->permissionPrefix}.{$action}";
    }

    /**
     * Verificar si el usuario es propietario del recurso
     */
    protected function isOwner(User $user, Model $model): bool
    {
        if (isset($model->user_id)) {
            return $model->user_id === $user->id;
        }

        if (isset($model->created_by)) {
            return $model->created_by === $user->id;
        }

        return false;
    }

    /**
     * Verificar si el usuario puede ver sus propios recursos
     */
    protected function canViewOwn(User $user, Model $model): bool
    {
        if (!$this->canAccessModel($user, $model)) {
            return false;
        }

        return $this->isOwner($user, $model) || $this->checkPermission($user, 'read');
    }

    /**
     * Verificar pertenencia al mismo ISP (multi-tenant)
     */
    protected function canAccessModel(User $user, Model $model): bool
    {
        if (!isset($model->isp_id)) {
            return true;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return (int) $model->isp_id === (int) $user->isp_id;
    }
}
