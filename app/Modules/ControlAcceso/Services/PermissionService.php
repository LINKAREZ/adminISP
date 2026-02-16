<?php

namespace App\Modules\ControlAcceso\Services;

use App\Modules\ControlAcceso\Events\PermissionActualizado;
use App\Modules\ControlAcceso\Models\Permission;
use App\Modules\ControlAcceso\Repositories\PermissionRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

/**
 * Servicio de Gestión de Permisos
 *
 * Este servicio encapsula toda la lógica de negocio relacionada con la gestión de permisos.
 * Implementa el patrón Service Layer para separar la lógica de negocio de los controladores.
 *
 * Responsabilidades:
 * - Gestión de permisos (crear, listar, mostrar)
 * - Filtrado de permisos por módulo y búsqueda
 * - Gestión de módulos únicos
 * - Caché de consultas frecuentes
 * - Disparo de eventos para invalidación de caché
 *
 * @package App\Modules\ControlAcceso\Services
 * @author Sistema Admin ISP
 * @version 2.0.0
 * @since 2025-12-05
 */
class PermissionService
{
    /**
     * Constructor del servicio
     *
     * Inyecta el repositorio de permisos mediante inyección de dependencias.
     *
     * @param PermissionRepository $permissionRepository Repositorio para acceso a datos de permisos
     */
    public function __construct(
        private PermissionRepository $permissionRepository
    ) {}

    /**
     * Obtener lista paginada de permisos con filtros aplicados
     *
     * Retorna una lista paginada de permisos con filtros opcionales por módulo
     * y búsqueda por nombre o display_name. Los resultados se almacenan en caché
     * por 1 hora, con una clave única basada en los filtros aplicados.
     *
     * Filtros disponibles:
     * - 'module': Filtrar por módulo específico
     * - 'search': Búsqueda en nombre y display_name
     *
     * @param array $filters Filtros a aplicar:
     *   - 'module' (string, opcional): Módulo para filtrar
     *   - 'search' (string, opcional): Texto para buscar en nombre y display_name
     * @param int $perPage Número de registros por página (default: 15)
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator Lista paginada de permisos
     *
     * @audit
     * - Cache key: "permissions.paginated.{hash_filtros}.{perPage}.{page}"
     * - TTL: 3600 segundos (1 hora)
     * - Invalidación: Automática mediante eventos PermissionActualizado
     * - Performance: Incluye withCount('roles') para evitar consultas adicionales
     */
    public function getPaginatedPermissions(array $filters = [], int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        // Generar clave de caché única basada en los filtros
        // md5(serialize()) asegura una clave única para cada combinación de filtros
        $cacheKey = 'permissions.paginated.' . md5(serialize($filters)) . ".{$perPage}." . request()->get('page', 1);

        return Cache::remember(
            $cacheKey,
            3600,
            fn() => $this->permissionRepository->getPaginatedWithFilters($filters, $perPage)
        );
    }

    /**
     * Obtener todos los permisos como colección con filtros aplicados
     *
     * Retorna una colección de todos los permisos (sin paginación) con filtros opcionales.
     * Se utiliza cuando se necesita agrupar los permisos por módulo en la vista.
     *
     * Filtros disponibles:
     * - 'module': Filtrar por módulo específico
     * - 'search': Búsqueda en nombre y display_name
     *
     * @param array $filters Filtros a aplicar:
     *   - 'module' (string, opcional): Módulo para filtrar
     *   - 'search' (string, opcional): Texto para buscar en nombre y display_name
     *
     * @return \Illuminate\Support\Collection Colección de permisos con conteo de roles
     *
     * @audit
     * - Cache key: "permissions.all.{hash_filtros}"
     * - TTL: 3600 segundos (1 hora)
     * - Invalidación: Automática mediante eventos PermissionActualizado
     * - Performance: Incluye withCount('roles') para evitar consultas adicionales
     */
    public function getAllPermissions(array $filters = []): \Illuminate\Support\Collection
    {
        $cacheKey = 'permissions.all.' . md5(serialize($filters));
        $result = Cache::get($cacheKey);
        if ($result !== null) {
            return $result;
        }
        $result = $this->permissionRepository->getAllWithFilters($filters);
        if ($result->isNotEmpty()) {
            Cache::put($cacheKey, $result, 3600);
        }
        return $result;
    }

    /**
     * Obtener lista de módulos únicos
     *
     * Retorna una colección ordenada de todos los módulos únicos que tienen
     * permisos asignados. Se utiliza en filtros y navegación.
     *
     * Los resultados se almacenan en caché por 1 hora.
     *
     * @return \Illuminate\Support\Collection Colección ordenada de módulos únicos
     *
     * @audit
     * - Cache key: "permissions.modules"
     * - TTL: 3600 segundos (1 hora)
     * - Invalidación: Automática cuando se crea/actualiza un permiso
     * - Orden: Alfabético ascendente
     */
    public function getModules(): \Illuminate\Support\Collection
    {
        return Cache::remember('permissions.modules', 3600, function () {
            // Verificar si la columna is_hidden existe antes de usar el scope
            $hasIsHiddenColumn = \Schema::hasColumn('permissions', 'is_hidden');
            $query = $hasIsHiddenColumn
                ? \App\Modules\ControlAcceso\Models\Permission::visible() // Solo módulos con permisos visibles
                : \App\Modules\ControlAcceso\Models\Permission::query();

            return $query
                ->distinct()
                ->pluck('module')
                ->sort()
                ->values();
        });
    }

    /**
     * Crear un nuevo permiso en el sistema
     *
     * Crea un nuevo permiso con los datos proporcionados. Los permisos se organizan
     * por módulo para facilitar su gestión y visualización.
     *
     * Flujo:
     * 1. Crea el permiso mediante el repositorio
     * 2. Dispara evento PermissionActualizado para invalidar caché
     * 3. Retorna el permiso creado
     *
     * @param array $data Datos del permiso:
     *   - 'name' (string, requerido): Nombre único del permiso (formato: modulo.accion)
     *   - 'display_name' (string, requerido): Nombre para mostrar
     *   - 'module' (string, requerido): Módulo al que pertenece el permiso
     *   - 'description' (string, opcional): Descripción del permiso
     *
     * @return Permission Permiso creado
     *
     * @throws \Exception Si hay un error al crear el permiso
     *
     * @audit
     * - Validación: Realizada por StorePermissionRequest
     * - Convención: Nombre debe seguir formato "modulo.accion" (ej: "users.index")
     * - Evento: PermissionActualizado disparado automáticamente
     * - Cache: Invalidado automáticamente por el listener
     */
    public function createPermission(array $data): Permission
    {
        // Crear permiso mediante repositorio
        $permissionData = [
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'module' => $data['module'],
            'description' => $data['description'] ?? null,
        ];

        // Solo agregar is_hidden si la columna existe
        if (Schema::hasColumn('permissions', 'is_hidden')) {
            $permissionData['is_hidden'] = $data['is_hidden'] ?? true; // Oculto por defecto
        }

        $permission = $this->permissionRepository->create($permissionData);

        // Disparar evento para invalidar caché automáticamente
        // Esto actualiza los cachés de módulos y listados de permisos
        Event::dispatch(new PermissionActualizado($permission));

        return $permission;
    }

    /**
     * Crear múltiples permisos para un recurso
     *
     * Crea automáticamente cuatro permisos para un recurso: crear, ver, editar, eliminar.
     * Los permisos se organizan por módulo para facilitar su gestión.
     *
     * Permisos creados (en orden):
     * - [recurso].create - Crear nuevo recurso
     * - [recurso].read - Ver detalle del recurso
     * - [recurso].edit - Editar el recurso
     * - [recurso].delete - Eliminar el recurso
     *
     * @param array $data Datos del recurso:
     *   - 'resource' (string, requerido): Nombre del recurso (ej: "usuarios", "clientes")
     *   - 'module' (string, requerido): Módulo al que pertenecen los permisos
     *   - 'description' (string, opcional): Descripción que se aplicará a todos los permisos
     *
     * @return \Illuminate\Support\Collection Colección de permisos creados
     *
     * @throws \Exception Si hay un error al crear los permisos
     *
     * @audit
     * - Validación: Realizada por StorePermissionRequest
     * - Convención: Recurso debe estar en minúsculas y sin espacios
     * - Evento: PermissionActualizado disparado para cada permiso creado
     * - Cache: Invalidado automáticamente por los listeners
     */
    public function createResourcePermissions(array $data): \Illuminate\Support\Collection
    {
        $resource = strtolower(trim($data['resource']));
        $module = $data['module'];
        $description = $data['description'] ?? null;
        $isHidden = $data['is_hidden'] ?? true;

        // Capitalizar primera letra del recurso para display_name
        $resourceDisplay = ucfirst(str_replace(['_', '-'], ' ', $resource));

        // Mapeo de acciones a nombres y descripciones (orden: create, read, update, delete)
        $actions = [
            'create' => [
                'display_name' => 'Crear ' . $resourceDisplay,
                'description' => $description ? "Crear nuevos {$description}" : "Crear nuevos {$resourceDisplay}",
            ],
            'read' => [
                'display_name' => 'Ver ' . $resourceDisplay,
                'description' => $description ? "Ver detalle de {$description}" : "Ver detalle de {$resourceDisplay}",
            ],
            'update' => [
                'display_name' => 'Editar ' . $resourceDisplay,
                'description' => $description ? "Editar {$description}" : "Editar {$resourceDisplay}",
            ],
            'delete' => [
                'display_name' => 'Eliminar ' . $resourceDisplay,
                'description' => $description ? "Eliminar {$description}" : "Eliminar {$resourceDisplay}",
            ],
        ];

        $createdPermissions = collect();

        foreach ($actions as $action => $config) {
            $name = "{$resource}.{$action}";

            // Verificar si el permiso ya existe
            $existingPermission = $this->permissionRepository->findByName($name);
            if ($existingPermission) {
                continue; // Saltar si ya existe
            }

            // Crear permiso mediante repositorio
            $permissionData = [
                'name' => $name,
                'display_name' => $config['display_name'],
                'module' => $module,
                'description' => $config['description'],
            ];

            // Solo agregar is_hidden si la columna existe
            if (Schema::hasColumn('permissions', 'is_hidden')) {
                $permissionData['is_hidden'] = $isHidden;
            }

            $permission = $this->permissionRepository->create($permissionData);
            $createdPermissions->push($permission);

            // Disparar evento para invalidar caché automáticamente
            Event::dispatch(new PermissionActualizado($permission));
        }

        return $createdPermissions;
    }

    /**
     * Obtener permiso con sus roles cargados
     *
     * Carga un permiso con todos los roles que lo tienen asignado mediante
     * eager loading para evitar el problema N+1.
     *
     * Uso: Se utiliza en la vista de detalle de permiso para mostrar
     * qué roles tienen asignado ese permiso.
     *
     * @param Permission $permission Permiso a cargar (model binding)
     * @return Permission Permiso con roles cargados
     *
     * @audit
     * - Performance: Usa eager loading para evitar N+1 queries
     * - Relaciones: Carga roles en una sola consulta
     */
    public function getPermissionWithRoles(Permission $permission): Permission
    {
        return $this->permissionRepository->getWithRoles($permission);
    }

    /**
     * Obtener todos los permisos de un recurso
     *
     * Retorna todos los permisos que pertenecen a un recurso específico,
     * agrupados por acción.
     *
     * @param string $resource Nombre del recurso (ej: "usuarios", "clientes")
     * @return \Illuminate\Support\Collection Colección de permisos del recurso
     */
    public function getResourcePermissions(string $resource): \Illuminate\Support\Collection
    {
        return Permission::where('name', 'like', $resource . '.%')
            ->withCount('roles')
            ->orderBy('name')
            ->get();
    }

    /**
     * Actualizar el nombre del recurso en todos sus permisos
     *
     * Actualiza el display_name y description de todos los permisos
     * de un recurso cuando se cambia el nombre del recurso.
     *
     * @param string $oldResource Nombre actual del recurso
     * @param string $newResource Nuevo nombre del recurso
     * @param string $module Módulo al que pertenece el recurso
     * @return int Número de permisos actualizados
     */
    public function updateResourceName(string $oldResource, string $newResource, string $module): int
    {
        $permissions = Permission::where('name', 'like', $oldResource . '.%')
            ->where('module', $module)
            ->get();

        $updated = 0;
        $newResourceDisplay = ucfirst(str_replace(['_', '-'], ' ', $newResource));

        foreach ($permissions as $permission) {
            $action = explode('.', $permission->name)[1] ?? '';

            $actions = [
                'create' => 'Crear ' . $newResourceDisplay,
                'read' => 'Ver ' . $newResourceDisplay,
                'update' => 'Editar ' . $newResourceDisplay,
                'delete' => 'Eliminar ' . $newResourceDisplay,
            ];

            if (isset($actions[$action])) {
                $permission->name = $newResource . '.' . $action;
                $permission->display_name = $actions[$action];
                $permission->save();

                Event::dispatch(new PermissionActualizado($permission));
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Eliminar todos los permisos de un recurso
     *
     * Elimina todos los permisos que pertenecen a un recurso específico.
     *
     * @param string $resource Nombre del recurso
     * @param string $module Módulo al que pertenece el recurso
     * @return int Número de permisos eliminados
     */
    public function deleteResourcePermissions(string $resource, string $module): int
    {
        $permissions = Permission::where('name', 'like', $resource . '.%')
            ->where('module', $module)
            ->get();

        $deleted = 0;
        foreach ($permissions as $permission) {
            Event::dispatch(new PermissionActualizado($permission));
            $permission->delete();
            $deleted++;
        }

        return $deleted;
    }
}
