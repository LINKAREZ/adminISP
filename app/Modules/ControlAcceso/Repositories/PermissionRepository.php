<?php

namespace App\Modules\ControlAcceso\Repositories;

use App\Core\Services\TenantConnectionService;
use App\Modules\ControlAcceso\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

/**
 * Repositorio de Permisos
 *
 * Implementa el patrón Repository para abstraer el acceso a datos de permisos.
 * Encapsula todas las consultas Eloquent relacionadas con permisos, incluyendo
 * filtrado y búsqueda avanzada.
 *
 * Responsabilidades:
 * - Consultas con filtros y búsqueda
 * - Operaciones CRUD básicas
 * - Gestión de relaciones (roles)
 * - Optimización de consultas
 *
 * @package App\Modules\ControlAcceso\Repositories
 * @author Sistema Admin ISP
 * @version 2.0.0
 * @since 2025-12-05
 *
 * @audit
 * - Patrón: Repository Pattern
 * - Abstracción: Eloquent ORM
 * - Filtrado: Por módulo y búsqueda de texto
 */
class PermissionRepository
{
    /**
     * Obtener permisos paginados con filtros aplicados
     *
     * Retorna una lista paginada de permisos con filtros opcionales:
     * - Filtro por módulo
     * - Búsqueda en nombre y display_name
     *
     * Los filtros se aplican a nivel de base de datos para optimizar el rendimiento.
     *
     * @param array $filters Filtros a aplicar:
     *   - 'module' (string, opcional): Filtrar por módulo específico
     *   - 'search' (string, opcional): Buscar en name y display_name
     * @param int $perPage Número de registros por página (default: 15)
     * @return LengthAwarePaginator Lista paginada de permisos con conteo de roles
     *
     * @audit
     * - Filtros: Aplicados a nivel de base de datos (eficiente)
     * - Búsqueda: LIKE en name y display_name (case-insensitive)
     * - Conteo: withCount('roles') para evitar cargar relaciones
     * - Orden: Por fecha de creación descendente
     */
    public function getPaginatedWithFilters(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $conn = TenantConnectionService::centralConnection();
        $hasIsHiddenColumn = Schema::connection($conn)->hasColumn('permissions', 'is_hidden');
        $query = $hasIsHiddenColumn ? Permission::on($conn)->visible() : Permission::on($conn);

        // Filtro por módulo (exacto)
        if (isset($filters['module']) && !empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }

        // Búsqueda en nombre y display_name usando el trait Searchable
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->search($filters['search'], ['name', 'display_name']);
        }

        return $query->withCount('roles') // Conteo sin cargar relaciones
            ->latest() // Ordenar por created_at DESC
            ->paginate($perPage);
    }

    /**
     * Obtener todos los permisos como colección con filtros aplicados
     *
     * Retorna una colección de todos los permisos (sin paginación) con filtros opcionales.
     * Se utiliza cuando se necesita agrupar los permisos por módulo en la vista.
     *
     * NOTA: En la vista de administración de permisos se muestran TODOS los permisos,
     * independientemente de si están ocultos o no, para que el administrador pueda gestionarlos.
     *
     * @param array $filters Filtros a aplicar:
     *   - 'module' (string, opcional): Filtrar por módulo específico
     *   - 'search' (string, opcional): Buscar en name y display_name
     * @return \Illuminate\Support\Collection Colección de permisos con conteo de roles
     *
     * @audit
     * - Filtros: Aplicados a nivel de base de datos (eficiente)
     * - Búsqueda: LIKE en name y display_name (case-insensitive)
     * - Conteo: withCount('roles') para evitar cargar relaciones
     * - Orden: Por módulo y luego por nombre
     * - Scope: NO usa visible() para mostrar todos los permisos en la administración
     */
    public function getAllWithFilters(array $filters = []): \Illuminate\Support\Collection
    {
        $conn = TenantConnectionService::centralConnection();
        $query = Permission::on($conn);

        // Filtro por módulo (exacto)
        if (isset($filters['module']) && !empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }

        // Búsqueda en nombre y display_name usando el trait Searchable
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->search($filters['search'], ['name', 'display_name']);
        }

        return $query->withCount('roles') // Conteo sin cargar relaciones
            ->orderBy('module') // Ordenar por módulo
            ->orderBy('name') // Luego por nombre
            ->get();
    }

    /**
     * Crear un nuevo permiso en la base de datos
     *
     * @param array $data Datos del permiso (name, display_name, module, description)
     * @return Permission Permiso creado
     *
     * @audit
     * - Validación: Debe realizarse antes de llamar este método
     * - Unicidad: El nombre debe ser único (validado por Form Request)
     * - Convención: Nombre debe seguir formato "modulo.accion"
     */
    public function create(array $data): Permission
    {
        return Permission::create($data);
    }

    /**
     * Obtener permiso con sus roles cargados
     *
     * Carga un permiso con todos los roles que lo tienen asignado mediante
     * eager loading para evitar el problema N+1.
     *
     * @param Permission $permission Permiso a cargar
     * @return Permission Permiso con roles cargados
     *
     * @audit
     * - Eager loading: Evita N+1 queries
     * - Uso: Para vistas de detalle que muestran qué roles tienen el permiso
     */
    public function getWithRoles(Permission $permission): Permission
    {
        return $permission->load('roles');
    }

    /**
     * Buscar un permiso por su nombre
     *
     * @param string $name Nombre del permiso (formato: recurso.accion)
     * @return Permission|null Permiso encontrado o null si no existe
     *
     * @audit
     * - Búsqueda: Por nombre exacto
     * - Uso: Para verificar si un permiso ya existe antes de crearlo
     */
    public function findByName(string $name): ?Permission
    {
        return Permission::where('name', $name)->first();
    }
}
