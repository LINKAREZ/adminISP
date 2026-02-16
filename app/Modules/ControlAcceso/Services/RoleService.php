<?php

namespace App\Modules\ControlAcceso\Services;

use App\Modules\ControlAcceso\Events\RoleActualizado;
use App\Modules\ControlAcceso\Models\Role;
use App\Modules\ControlAcceso\Models\Permission;
use App\Modules\ControlAcceso\Repositories\RoleRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

/**
 * Servicio de Gestión de Roles
 *
 * Este servicio encapsula toda la lógica de negocio relacionada con la gestión de roles.
 * Implementa el patrón Service Layer para separar la lógica de negocio de los controladores.
 *
 * Responsabilidades:
 * - Gestión de roles (crear, actualizar, eliminar, listar)
 * - Asignación de permisos a roles (relación muchos a muchos)
 * - Gestión de permisos agrupados por módulo
 * - Caché de consultas frecuentes
 * - Disparo de eventos para invalidación de caché
 *
 * @package App\Modules\ControlAcceso\Services
 * @author Sistema Admin ISP
 * @version 2.0.0
 * @since 2025-12-05
 */
class RoleService
{
    /**
     * Constructor del servicio
     *
     * Inyecta el repositorio de roles mediante inyección de dependencias.
     *
     * @param RoleRepository $roleRepository Repositorio para acceso a datos de roles
     */
    public function __construct(
        private RoleRepository $roleRepository
    ) {}

    /**
     * Obtener lista paginada de roles con conteo de usuarios
     *
     * Retorna una lista paginada de roles con el conteo de usuarios asignados.
     * Los resultados se almacenan en caché por 1 hora.
     *
     * @param int $perPage Número de registros por página (default: 15)
     * @param array $filters Filtros opcionales para búsqueda
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator Lista paginada de roles
     *
     * @audit
     * - Cache key: "roles.paginated.{perPage}.{page}.{hash_filtros}"
     * - TTL: 3600 segundos (1 hora)
     * - Invalidación: Automática mediante eventos RoleActualizado
     * - Performance: Incluye withCount('users') para evitar consultas adicionales
     * - Búsqueda: Soporta búsqueda en name y description
     */
    public function getPaginatedRoles(int $perPage = 15, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $cacheKey = 'roles.paginated.' . md5(serialize($filters)) . ".{$perPage}." . request()->get('page', 1);

        $result = Cache::get($cacheKey);
        if ($result !== null) {
            return $result;
        }

        $result = $this->roleRepository->getPaginatedWithUserCount($perPage, $filters);
        // Solo cachear cuando hay datos; evita que una lista vacía cacheada oculte roles tras ejecutar el seeder
        if ($result->total() > 0) {
            Cache::put($cacheKey, $result, 3600);
        }

        return $result;
    }

    /**
     * Obtener permisos agrupados por módulo
     *
     * Retorna todos los permisos del sistema agrupados por su módulo.
     * Se utiliza en formularios de creación/edición de roles para mostrar
     * los permisos organizados por módulo.
     *
     * NOTA: Retorna TODOS los permisos, incluso los ocultos, ya que al crear
     * o editar un rol es necesario poder asignar cualquier permiso disponible.
     *
     * Los resultados se almacenan en caché por 1 hora.
     *
     * @return \Illuminate\Support\Collection Colección agrupada de permisos por módulo
     *
     * @audit
     * - Cache key: "permissions.grouped.by.module"
     * - TTL: 3600 segundos (1 hora)
     * - Invalidación: Automática cuando se actualiza un permiso o rol
     * - Orden: Por módulo y luego por nombre
     * - Scope: NO usa visible() para mostrar todos los permisos en formularios de roles
     */
    public function getPermissionsGroupedByModule()
    {
        return Cache::remember('permissions.grouped.by.module', 3600, function () {
            // Retornar TODOS los permisos sin filtrar por is_hidden
            // Al crear/editar roles necesitamos ver todos los permisos disponibles
            return Permission::query()
                ->orderBy('module')
                ->orderBy('name')
                ->get()
                ->groupBy('module');
        });
    }

    /**
     * Crear un nuevo rol en el sistema
     *
     * Crea un nuevo rol con los datos proporcionados. Si se proporcionan permisos,
     * se sincronizan automáticamente con el rol (relación muchos a muchos).
     *
     * Flujo:
     * 1. Crea el rol mediante el repositorio
     * 2. Sincroniza permisos si se proporcionan
     * 3. Dispara evento RoleActualizado para invalidar caché
     * 4. Retorna el rol creado
     *
     * @param array $data Datos del rol:
     *   - 'name' (string, requerido): Nombre único del rol
     *   - 'description' (string, opcional): Descripción del rol
     *   - 'is_active' (bool, opcional): Si el rol está activo (default: true)
     *   - 'permissions' (array, opcional): Array de IDs de permisos a asignar
     *
     * @return Role Rol creado con sus relaciones
     *
     * @throws \Exception Si hay un error al crear el rol
     *
     * @audit
     * - Validación: Realizada por StoreRoleRequest
     * - Relaciones: Permisos sincronizados mediante sync() (muchos a muchos)
     * - Evento: RoleActualizado disparado automáticamente
     * - Cache: Invalidado automáticamente por el listener
     */
    public function createRole(array $data): Role
    {
        // Crear rol mediante repositorio
        $role = $this->roleRepository->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : true, // Por defecto activo
        ]);

        // Sincronizar permisos si se proporcionan
        // sync() reemplaza todos los permisos existentes con los nuevos
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $this->roleRepository->syncPermissions($role, $data['permissions']);
        }

        // Disparar evento para invalidar caché automáticamente
        Event::dispatch(new RoleActualizado($role));

        return $role;
    }

    /**
     * Actualizar un rol existente
     *
     * Actualiza los datos de un rol. Si se proporcionan permisos, se sincronizan
     * con el rol. Si se proporciona un array vacío, se remueven todos los permisos.
     *
     * Flujo:
     * 1. Actualiza el rol mediante el repositorio
     * 2. Sincroniza o remueve permisos según se proporcione
     * 3. Dispara evento RoleActualizado
     * 4. Retorna el rol actualizado
     *
     * @param Role $role Rol a actualizar (model binding)
     * @param array $data Datos a actualizar:
     *   - 'name' (string, opcional): Nuevo nombre
     *   - 'description' (string, opcional): Nueva descripción
     *   - 'is_active' (bool, opcional): Nuevo estado activo
     *   - 'permissions' (array|null, opcional): Array de IDs de permisos (null para no cambiar)
     *
     * @return Role Rol actualizado con relaciones frescas
     *
     * @throws \Exception Si hay un error al actualizar
     *
     * @audit
     * - Validación: Realizada por UpdateRoleRequest
     * - Relaciones: Permisos sincronizados o removidos según se proporcione
     * - Evento: RoleActualizado disparado automáticamente
     * - Cache: Invalidado automáticamente
     */
    public function updateRole(Role $role, array $data): Role
    {
        // Actualizar datos básicos del rol
        $this->roleRepository->update($role, [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : true,
        ]);

        // Sincronizar permisos
        // Si se proporciona el array permissions:
        // - Si tiene elementos: sincroniza esos permisos
        // - Si está vacío: remueve todos los permisos
        // - Si no se proporciona: no cambia los permisos existentes
        if (isset($data['permissions'])) {
            if (is_array($data['permissions']) && count($data['permissions']) > 0) {
                $this->roleRepository->syncPermissions($role, $data['permissions']);
            } else {
                // Remover todos los permisos si el array está vacío
                $this->roleRepository->detachAllPermissions($role);
            }
        }

        // Disparar evento con rol actualizado
        Event::dispatch(new RoleActualizado($role->fresh()));

        return $role->fresh();
    }

    /**
     * Eliminar un rol del sistema
     *
     * Elimina un rol del sistema. Previene la eliminación si el rol tiene
     * usuarios asignados para mantener la integridad referencial.
     *
     * Flujo:
     * 1. Verifica que no tenga usuarios asignados
     * 2. Elimina el rol mediante el repositorio
     * 3. Dispara evento RoleActualizado si se eliminó correctamente
     * 4. Retorna el resultado
     *
     * @param Role $role Rol a eliminar (model binding)
     * @return bool true si se eliminó correctamente, false en caso contrario
     *
     * @throws \Exception Si el rol tiene usuarios asignados
     *
     * @audit
     * - Integridad: Previene eliminación si tiene usuarios asignados
     * - Relaciones: Los permisos se desasocian automáticamente (detach)
     * - Evento: RoleActualizado disparado si se elimina correctamente
     * - Cache: Invalidado automáticamente
     */
    public function deleteRole(Role $role): bool
    {
        // Prevenir eliminación si tiene usuarios asignados
        // Esto mantiene la integridad referencial y evita usuarios sin rol
        if ($this->roleRepository->hasUsers($role)) {
            throw new \Exception('No se puede eliminar un rol que tiene usuarios asignados.');
        }

        // Eliminar rol mediante repositorio
        $deleted = $this->roleRepository->delete($role);

        // Disparar evento solo si se eliminó correctamente
        if ($deleted) {
            Event::dispatch(new RoleActualizado($role));
        }

        return $deleted;
    }

    /**
     * Obtener rol con sus permisos cargados
     *
     * Carga un rol con sus permisos mediante eager loading.
     *
     * @param Role $role Rol a cargar (model binding)
     * @return Role Rol con permisos cargados
     *
     * @audit
     * - Performance: Usa eager loading para evitar N+1 queries
     */
    public function getRoleWithPermissions(Role $role): Role
    {
        return $this->roleRepository->getWithPermissions($role);
    }

    /**
     * Obtener IDs de permisos asignados a un rol
     *
     * Retorna un array con los IDs de los permisos asignados al rol.
     * Se utiliza en formularios para marcar los permisos seleccionados.
     *
     * @param Role $role Rol del cual obtener los permisos (model binding)
     * @return array Array de IDs de permisos
     *
     * @audit
     * - Uso: Para formularios de edición de roles
     * - Performance: Usa pluck() para obtener solo los IDs
     */
    public function getRolePermissionIds(Role $role): array
    {
        return $this->roleRepository->getPermissionIds($role);
    }
}
