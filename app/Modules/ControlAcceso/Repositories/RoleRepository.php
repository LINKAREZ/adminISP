<?php

namespace App\Modules\ControlAcceso\Repositories;

use App\Modules\ControlAcceso\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Repositorio de Roles
 *
 * Implementa el patrón Repository para abstraer el acceso a datos de roles.
 * Encapsula todas las consultas Eloquent relacionadas con roles, incluyendo
 * la gestión de relaciones muchos a muchos con permisos.
 *
 * Responsabilidades:
 * - Consultas optimizadas con eager loading y conteos
 * - Operaciones CRUD básicas
 * - Gestión de relaciones muchos a muchos (permisos)
 * - Validaciones de integridad referencial
 *
 * @package App\Modules\ControlAcceso\Repositories
 * @author Sistema Admin ISP
 * @version 2.0.0
 * @since 2025-12-05
 *
 * @audit
 * - Patrón: Repository Pattern
 * - Abstracción: Eloquent ORM
 * - Relaciones: Muchos a muchos con Permission
 */
class RoleRepository
{
    /**
     * Obtener roles paginados con conteo de usuarios asignados
     *
     * Retorna una lista paginada de roles con el conteo de usuarios que tienen
     * asignado cada rol. Usa withCount() para obtener el conteo sin cargar
     * todos los usuarios, optimizando el rendimiento.
     *
     * @param int $perPage Número de registros por página (default: 15)
     * @param array $filters Filtros opcionales:
     *   - 'search' (string, opcional): Buscar en name y description
     * @return LengthAwarePaginator Lista paginada de roles con conteo
     *
     * @audit
     * - Performance: withCount() evita cargar todos los usuarios
     * - Orden: Por fecha de creación descendente
     * - Uso: Para listados que muestran cuántos usuarios tienen cada rol
     * - Búsqueda: Busca en name y description
     */
    public function getPaginatedWithUserCount(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Role::withCount('users'); // Conteo sin cargar relaciones

        // Búsqueda avanzada en múltiples campos
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $query->orderByRaw(
            "CASE name WHEN 'administrador' THEN 1 WHEN 'supervisor' THEN 2 WHEN 'cobrador' THEN 3 WHEN 'tecnico' THEN 4 WHEN 'ayudante' THEN 5 ELSE 6 END"
        );

        return $query->paginate($perPage);
    }

    /**
     * Crear un nuevo rol en la base de datos
     *
     * @param array $data Datos del rol (name, description, is_active)
     * @return Role Rol creado
     *
     * @audit
     * - Validación: Debe realizarse antes de llamar este método
     * - Unicidad: El nombre debe ser único (validado por Form Request)
     */
    public function create(array $data): Role
    {
        return Role::create($data);
    }

    /**
     * Actualizar un rol existente
     *
     * @param Role $role Rol a actualizar
     * @param array $data Datos a actualizar
     * @return bool true si se actualizó correctamente
     *
     * @audit
     * - Actualización parcial: Solo campos proporcionados
     * - Timestamps: updated_at automático
     */
    public function update(Role $role, array $data): bool
    {
        return $role->update($data);
    }

    /**
     * Eliminar un rol de la base de datos
     *
     * @param Role $role Rol a eliminar
     * @return bool true si se eliminó correctamente
     *
     * @audit
     * - Eliminación permanente: No hay soft deletes
     * - Relaciones: Los permisos se desasocian automáticamente (detach)
     * - Integridad: Debe verificarse que no tenga usuarios antes de eliminar
     */
    public function delete(Role $role): bool
    {
        return $role->delete();
    }

    /**
     * Sincronizar permisos de un rol
     *
     * Sincroniza los permisos de un rol con el array de IDs proporcionado.
     * Reemplaza todos los permisos existentes con los nuevos.
     *
     * @param Role $role Rol al cual sincronizar permisos
     * @param array $permissionIds Array de IDs de permisos
     * @return void
     *
     * @audit
     * - Relación: Muchos a muchos (belongsToMany)
     * - Sincronización: Reemplaza todos los permisos existentes
     * - Tabla pivote: permission_role
     */
    public function syncPermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
    }

    /**
     * Desasociar todos los permisos de un rol
     *
     * Remueve todos los permisos asignados a un rol.
     *
     * @param Role $role Rol del cual remover permisos
     * @return void
     *
     * @audit
     * - Uso: Cuando se quiere dejar un rol sin permisos
     * - Relación: Detach de todos los registros en tabla pivote
     */
    public function detachAllPermissions(Role $role): void
    {
        $role->permissions()->detach();
    }

    /**
     * Verificar si un rol tiene usuarios asignados
     *
     * Verifica si el rol tiene al menos un usuario asignado.
     * Se utiliza para prevenir la eliminación de roles con usuarios.
     *
     * @param Role $role Rol a verificar
     * @return bool true si tiene usuarios, false en caso contrario
     *
     * @audit
     * - Uso: Validación antes de eliminar rol
     * - Performance: count() es eficiente (no carga usuarios)
     * - Integridad: Previene usuarios sin rol
     */
    public function hasUsers(Role $role): bool
    {
        return $role->users()->count() > 0;
    }

    /**
     * Obtener rol con sus permisos cargados
     *
     * Carga un rol con todos sus permisos mediante eager loading.
     *
     * @param Role $role Rol a cargar
     * @return Role Rol con permisos cargados
     *
     * @audit
     * - Eager loading: Evita N+1 queries
     * - Uso: Para vistas de detalle que muestran permisos
     */
    public function getWithPermissions(Role $role): Role
    {
        return $role->load('permissions');
    }

    /**
     * Obtener IDs de permisos asignados a un rol
     *
     * Retorna un array con los IDs de los permisos asignados al rol.
     * Se utiliza en formularios para marcar los permisos seleccionados.
     *
     * @param Role $role Rol del cual obtener los IDs
     * @return array Array de IDs de permisos
     *
     * @audit
     * - Uso: Para formularios de edición
     * - Performance: pluck() es eficiente (solo obtiene IDs)
     */
    public function getPermissionIds(Role $role): array
    {
        return $role->permissions->pluck('id')->toArray();
    }
}
