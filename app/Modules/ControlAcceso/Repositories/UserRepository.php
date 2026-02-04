<?php

namespace App\Modules\ControlAcceso\Repositories;

use App\Modules\ControlAcceso\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Repositorio de Usuarios
 *
 * Implementa el patrón Repository para abstraer el acceso a datos de usuarios.
 * Encapsula todas las consultas Eloquent relacionadas con usuarios, proporcionando
 * una interfaz limpia y testeable para el acceso a datos.
 *
 * Responsabilidades:
 * - Consultas optimizadas con eager loading
 * - Operaciones CRUD básicas
 * - Gestión de relaciones (roles)
 * - Abstracción de la capa de datos
 *
 * @package App\Modules\ControlAcceso\Repositories
 * @author Sistema Admin ISP
 * @version 2.0.0
 * @since 2025-12-05
 *
 * @audit
 * - Patrón: Repository Pattern
 * - Abstracción: Eloquent ORM
 * - Optimización: Eager loading para evitar N+1 queries
 */
class UserRepository
{
    /**
     * Obtener usuarios paginados con sus roles cargados
     *
     * Retorna una lista paginada de usuarios con sus roles cargados mediante
     * eager loading para evitar el problema N+1 de consultas.
     *
     * @param int $perPage Número de registros por página (default: 15)
     * @param array $filters Filtros opcionales:
     *   - 'search' (string, opcional): Buscar en name, email y nombre del rol
     * @return LengthAwarePaginator Lista paginada de usuarios con roles
     *
     * @audit
     * - Performance: Usa eager loading (with('role'))
     * - Orden: Por fecha de creación descendente (latest())
     * - Paginación: Laravel LengthAwarePaginator
     * - Búsqueda: Busca en name, email y nombre del rol (relación)
     */
    public function getPaginatedWithRole(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = User::with(['role', 'isp']); // Eager loading para evitar N+1

        // Para admin de ISP, limitar usuarios al mismo ISP y excluir super admins
        $currentUser = auth()->user();
        if ($currentUser && !$currentUser->isSuperAdmin()) {
            $query->where('isp_id', $currentUser->isp_id);
        }

        // Búsqueda avanzada en múltiples campos y relaciones
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                // Buscar en nombre y email del usuario
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  // Buscar en nombre del rol (relación)
                  ->orWhereHas('role', function ($roleQuery) use ($search) {
                      $roleQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query->latest() // Ordenar por created_at DESC
            ->paginate($perPage);
    }

    /**
     * Crear un nuevo usuario en la base de datos
     *
     * Crea un nuevo registro de usuario con los datos proporcionados.
     * La validación de datos debe realizarse antes de llamar a este método.
     *
     * @param array $data Datos del usuario:
     *   - 'name' (string, requerido): Nombre del usuario
     *   - 'email' (string, requerido): Email único
     *   - 'password' (string, requerido): Contraseña hasheada
     *   - 'role_id' (int, opcional): ID del rol
     *
     * @return User Usuario creado
     *
     * @audit
     * - Validación: Debe realizarse antes de llamar este método
     * - Seguridad: La contraseña debe estar hasheada
     * - Integridad: El email debe ser único (validado por Form Request)
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Actualizar un usuario existente
     *
     * Actualiza los datos de un usuario. Solo se actualizan los campos
     * proporcionados en el array $data.
     *
     * @param User $user Usuario a actualizar (model binding)
     * @param array $data Datos a actualizar (solo campos a modificar)
     * @return bool true si se actualizó correctamente, false en caso contrario
     *
     * @audit
     * - Actualización parcial: Solo actualiza campos proporcionados
     * - Timestamps: updated_at se actualiza automáticamente
     * - Retorno: Boolean indica éxito/fallo
     */
    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    /**
     * Eliminar un usuario de la base de datos
     *
     * Elimina permanentemente un usuario. Las relaciones se manejan
     * mediante foreign keys con onDelete en la base de datos.
     *
     * @param User $user Usuario a eliminar (model binding)
     * @return bool true si se eliminó correctamente, false en caso contrario
     *
     * @audit
     * - Eliminación permanente: No hay soft deletes
     * - Integridad: Foreign keys manejan relaciones
     * - Retorno: Boolean indica éxito/fallo
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Asignar un rol a un usuario
     *
     * Asigna o remueve el rol de un usuario. Si $roleId es null,
     * se remueve el rol del usuario.
     *
     * @param User $user Usuario al cual asignar el rol (model binding)
     * @param int|null $roleId ID del rol a asignar, o null para remover
     * @return void
     *
     * @audit
     * - Relación: belongsTo (un usuario tiene un solo rol)
     * - Actualización: Guarda el modelo después de asignar
     * - Null: Permite remover el rol asignando null
     */
    public function assignRole(User $user, ?int $roleId): void
    {
        $user->role_id = $roleId;
        $user->save();
    }

    /**
     * Obtener usuario con sus relaciones cargadas
     *
     * Carga un usuario con su rol y los permisos del rol mediante
     * eager loading anidado para evitar múltiples consultas.
     *
     * Uso: Se utiliza cuando se necesita mostrar información completa
     * del usuario incluyendo sus permisos.
     *
     * @param User $user Usuario a cargar (model binding)
     * @return User Usuario con relaciones cargadas (role.permissions)
     *
     * @audit
     * - Eager loading anidado: 'role.permissions'
     * - Performance: Una sola consulta para cargar todo
     * - Uso: Para vistas de detalle que muestran permisos
     */
    public function getWithRoleAndPermissions(User $user): User
    {
        return $user->load('role.permissions');
    }
}
