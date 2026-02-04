<?php

namespace App\Modules\ControlAcceso\Services;

use App\Modules\ControlAcceso\Events\UserActualizado;
use App\Modules\ControlAcceso\Models\User;
use App\Modules\ControlAcceso\Models\Role;
use App\Modules\ControlAcceso\Repositories\UserRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

/**
 * Servicio de Gestión de Usuarios
 *
 * Este servicio encapsula toda la lógica de negocio relacionada con la gestión de usuarios.
 * Implementa el patrón Service Layer para separar la lógica de negocio de los controladores.
 *
 * Responsabilidades:
 * - Gestión de usuarios (crear, actualizar, eliminar, listar)
 * - Asignación de roles a usuarios
 * - Gestión de contraseñas (hashing)
 * - Caché de consultas frecuentes
 * - Disparo de eventos para invalidación de caché
 *
 * @package App\Modules\ControlAcceso\Services
 * @author Sistema Admin ISP
 * @version 2.0.0
 * @since 2025-12-05
 */
class UserService
{
    /**
     * Constructor del servicio
     *
     * Inyecta el repositorio de usuarios mediante inyección de dependencias.
     * Esto permite desacoplar el acceso a datos de la lógica de negocio.
     *
     * @param UserRepository $userRepository Repositorio para acceso a datos de usuarios
     */
    public function __construct(
        private UserRepository $userRepository
    ) {}

    /**
     * Obtener lista paginada de usuarios con sus roles
     *
     * Retorna una lista paginada de usuarios con sus roles cargados mediante eager loading.
     * Los resultados se almacenan en caché por 1 hora para mejorar el rendimiento.
     *
     * Flujo:
     * 1. Verifica si existe en caché
     * 2. Si no existe, consulta el repositorio
     * 3. Almacena el resultado en caché
     * 4. Retorna los resultados
     *
     * @param int $perPage Número de registros por página (default: 15)
     * @param array $filters Filtros opcionales para búsqueda
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator Lista paginada de usuarios con roles
     *
     * @audit
     * - Cache key: "users.paginated.{perPage}.{page}.{hash_filtros}"
     * - TTL: 3600 segundos (1 hora)
     * - Invalidación: Automática mediante eventos UserActualizado
     * - Búsqueda: Soporta búsqueda en name, email y nombre del rol
     */
    public function getPaginatedUsers(int $perPage = 15, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->userRepository->getPaginatedWithRole($perPage, $filters);
    }

    /**
     * Obtener lista de roles activos
     *
     * Retorna todos los roles que están marcados como activos en el sistema.
     * Los resultados se almacenan en caché por 1 hora.
     *
     * Uso: Se utiliza en formularios de creación/edición de usuarios para
     * mostrar la lista de roles disponibles.
     *
     * @return \Illuminate\Database\Eloquent\Collection Colección de roles activos
     *
     * @audit
     * - Cache key: "roles.active"
     * - TTL: 3600 segundos (1 hora)
     * - Invalidación: Automática cuando se actualiza un rol
     */
    public function getActiveRoles()
    {
        return Cache::remember('roles.active', 3600, function () {
            return \App\Modules\ControlAcceso\Models\Role::where('is_active', true)
                ->orderByRaw("CASE name WHEN 'administrador' THEN 1 WHEN 'supervisor' THEN 2 WHEN 'cobrador' THEN 3 WHEN 'tecnico' THEN 4 WHEN 'ayudante' THEN 5 ELSE 6 END")
                ->get();
        });
    }

    /**
     * Crear un nuevo usuario en el sistema
     *
     * Crea un nuevo usuario con los datos proporcionados. La contraseña se hashea
     * automáticamente antes de almacenarse. Si se proporciona un role_id, se asigna
     * el rol al usuario.
     *
     * Flujo:
     * 1. Hashea la contraseña
     * 2. Crea el usuario mediante el repositorio
     * 3. Asigna el rol si se proporciona
     * 4. Dispara evento UserActualizado para invalidar caché
     * 5. Retorna el usuario creado
     *
     * @param array $data Datos del usuario:
     *   - 'name' (string, requerido): Nombre del usuario
     *   - 'email' (string, requerido): Email único del usuario
     *   - 'password' (string, requerido): Contraseña en texto plano
     *   - 'role_id' (int, opcional): ID del rol a asignar
     *
     * @return User Usuario creado con sus relaciones cargadas
     *
     * @throws \Exception Si hay un error al crear el usuario
     *
     * @audit
     * - Validación: Realizada por StoreUserRequest
     * - Seguridad: Contraseña hasheada con bcrypt
     * - Evento: UserActualizado disparado automáticamente
     * - Cache: Invalidado automáticamente por el listener
     */
    public function createUser(array $data): User
    {
        // Crear usuario con contraseña hasheada
        // NOTA: El modelo User tiene 'password' => 'hashed' en casts,
        // por lo que NO debemos usar Hash::make() aquí - el modelo lo hasheará automáticamente
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // El modelo lo hasheará automáticamente
        ];

        // Si viene isp_id (solo para super admins), asignarlo
        // Si no viene, el trait BelongsToIsp lo asignará automáticamente desde el usuario autenticado
        if (isset($data['isp_id']) && $data['isp_id'] !== '') {
            $userData['isp_id'] = $data['isp_id'];
        }

        $user = $this->userRepository->create($userData);

        // Asignar rol si se proporciona
        // Un usuario puede tener solo un rol (relación belongsTo)
        if (isset($data['role_id']) && $data['role_id']) {
            $this->userRepository->assignRole($user, $data['role_id']);
        }

        // Disparar evento para invalidar caché automáticamente
        // El listener InvalidarCacheControlAcceso se encargará de limpiar el caché
        Event::dispatch(new UserActualizado($user));

        return $user;
    }

    /**
     * Actualizar un usuario existente
     *
     * Actualiza los datos de un usuario. La contraseña solo se actualiza si se
     * proporciona en los datos. Si se proporciona un role_id, se actualiza el rol.
     *
     * Flujo:
     * 1. Prepara los datos a actualizar
     * 2. Hashea la contraseña solo si se proporciona
     * 3. Actualiza el usuario mediante el repositorio
     * 4. Actualiza el rol si se proporciona
     * 5. Dispara evento UserActualizado
     * 6. Retorna el usuario actualizado
     *
     * @param User $user Usuario a actualizar (model binding)
     * @param array $data Datos a actualizar:
     *   - 'name' (string, opcional): Nuevo nombre
     *   - 'email' (string, opcional): Nuevo email
     *   - 'password' (string, opcional): Nueva contraseña (solo si se proporciona)
     *   - 'role_id' (int|null, opcional): Nuevo rol (null para remover)
     *
     * @return User Usuario actualizado con relaciones frescas
     *
     * @throws \Exception Si hay un error al actualizar
     *
     * @audit
     * - Validación: Realizada por UpdateUserRequest
     * - Seguridad: Contraseña solo se actualiza si se proporciona
     * - Evento: UserActualizado disparado automáticamente
     * - Cache: Invalidado automáticamente
     */
    public function updateUser(User $user, array $data): User
    {
        // Preparar datos básicos a actualizar
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        // Actualizar contraseña solo si se proporciona
        // Esto permite actualizar otros campos sin cambiar la contraseña
        // Nota: El modelo User tiene 'password' => 'hashed' en casts, así que no necesitamos Hash::make()
        if (isset($data['password']) && !empty($data['password'])) {
            $updateData['password'] = $data['password']; // El modelo lo hasheará automáticamente
        }

        // Actualizar usuario mediante repositorio
        $this->userRepository->update($user, $updateData);

        // Actualizar rol si se proporciona
        // Si role_id es null, se remueve el rol del usuario
        if (isset($data['role_id'])) {
            $this->userRepository->assignRole($user, $data['role_id'] ?: null);
        }

        // Disparar evento con usuario actualizado (fresh para obtener relaciones)
        Event::dispatch(new UserActualizado($user->fresh()));

        return $user->fresh();
    }

    /**
     * Eliminar un usuario del sistema
     *
     * Elimina un usuario del sistema. Previene la eliminación del usuario actual
     * que está autenticado para evitar bloqueos del sistema.
     * También previene la eliminación de usuarios administradores por defecto
     * (excepto para el super admin root).
     *
     * Flujo:
     * 1. Verifica que no sea el usuario actual
     * 2. Elimina el usuario mediante el repositorio
     * 3. Dispara evento UserActualizado si se eliminó correctamente
     * 4. Retorna el resultado
     *
     * @param User $user Usuario a eliminar (model binding)
     * @return bool true si se eliminó correctamente, false en caso contrario
     *
     * @throws \Exception Si se intenta eliminar el usuario actual
     *
     * @audit
     * - Seguridad: Previene auto-eliminación
     * - Integridad: Las relaciones se manejan mediante foreign keys con onDelete
     * - Evento: UserActualizado disparado si se elimina correctamente
     * - Cache: Invalidado automáticamente
     */
    public function deleteUser(User $user): bool
    {
        // Prevenir eliminación del usuario actual
        // Esto evita que un usuario se elimine a sí mismo y quede bloqueado
        if ($user->id === Auth::id()) {
            throw new \Exception('No puedes eliminar tu propio usuario.');
        }

        // Prevenir eliminación de usuarios administradores por defecto (excepto super admin root)
        if ($user->is_default_admin ?? false) {
            /** @var \App\Modules\ControlAcceso\Models\User|null $currentUser */
            $currentUser = Auth::user();
            if (!$currentUser || !$currentUser->isRootUser()) {
                throw new \Exception('No se puede eliminar un usuario administrador por defecto. Solo el super administrador root puede hacerlo.');
            }
        }

        // Eliminar usuario mediante repositorio
        $deleted = $this->userRepository->delete($user);

        // Disparar evento solo si se eliminó correctamente
        if ($deleted) {
            Event::dispatch(new UserActualizado($user));
        }

        return $deleted;
    }

    /**
     * Obtener usuario con todas sus relaciones cargadas
     *
     * Carga un usuario con sus relaciones (rol y permisos del rol) mediante
     * eager loading para evitar el problema N+1.
     *
     * Uso: Se utiliza en la vista de detalle de usuario para mostrar
     * información completa del usuario y sus permisos.
     *
     * @param User $user Usuario a cargar (model binding)
     * @return User Usuario con relaciones cargadas (role.permissions)
     *
     * @audit
     * - Performance: Usa eager loading para evitar N+1 queries
     * - Relaciones: Carga role y role.permissions en una sola consulta
     */
    public function getUserWithRelations(User $user): User
    {
        return $this->userRepository->getWithRoleAndPermissions($user);
    }
}
