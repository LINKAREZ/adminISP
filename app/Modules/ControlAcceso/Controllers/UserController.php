<?php

namespace App\Modules\ControlAcceso\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ControlAcceso\Requests\StoreUserRequest;
use App\Modules\ControlAcceso\Requests\UpdateUserRequest;
use App\Modules\ControlAcceso\Models\User;
use App\Modules\ControlAcceso\Services\UserService;
use App\Modules\Sistema\Models\Isp;
use App\Core\Scopes\IspScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador de Gestión de Usuarios
 *
 * Maneja todas las peticiones HTTP relacionadas con la gestión de usuarios.
 * Implementa el patrón RESTful estándar de Laravel con autorización mediante Policies.
 *
 * Responsabilidades:
 * - Manejo de requests HTTP (GET, POST, PUT, DELETE)
 * - Autorización mediante Policies
 * - Delegación de lógica de negocio a UserService
 * - Manejo de errores y respuestas
 * - Redirecciones con mensajes flash
 *
 * @package App\Modules\ControlAcceso\Controllers
 * @author Sistema Admin ISP
 * @version 2.0.0
 * @since 2025-12-05
 *
 * @audit
 * - Patrón: RESTful Controller
 * - Autorización: Policies automáticas mediante authorizeResource()
 * - Lógica: Delegada a UserService
 * - Validación: Form Requests (StoreUserRequest, UpdateUserRequest)
 */
class UserController extends Controller
{
    /**
     * Constructor del controlador
     *
     * Inyecta UserService mediante inyección de dependencias y configura
     * la autorización automática para todos los métodos del recurso.
     *
     * @param UserService $userService Servicio de gestión de usuarios
     *
     * @audit
     * - Inyección: UserService inyectado automáticamente por Laravel
     * - Autorización: authorizeResource() aplica policies automáticamente:
     *   * index() → viewAny()
     *   * show() → view()
     *   * create() → create()
     *   * store() → create()
     *   * edit() → update()
     *   * update() → update()
     *   * destroy() → delete()
     */
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Mostrar lista paginada de usuarios
     *
     * Retorna la vista con la lista de usuarios paginada y los roles activos
     * para usar en filtros o formularios.
     *
     * Ruta: GET /users
     * Autorización: Requiere permiso 'users.read'
     *
     * @return \Illuminate\View\View Vista con lista de usuarios
     *
     * @audit
     * - Autorización: UserPolicy::viewAny() verifica 'users.read'
     * - Caché: Los usuarios se obtienen del servicio con caché
     * - Datos: Incluye roles activos para filtros
     */
    public function index(Request $request)
    {
        // El middleware 'auth' garantiza que el usuario está autenticado
        /** @var \App\Modules\ControlAcceso\Models\User|null $currentUser */
        $currentUser = Auth::user();

        // Verificación explícita del usuario root
        $isRoot = $currentUser && $currentUser->isRootUser();

        if (!$isRoot) {
            // Solo verificar política si NO es root
            $this->authorize('viewAny', User::class);
        }

        // Obtener filtros de búsqueda
        $filters = [
            'search' => $request->get('search'),
        ];

        $users = $this->userService->getPaginatedUsers(15, $filters);
        $roles = $this->userService->getActiveRoles();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Mostrar formulario de creación de usuario
     *
     * Retorna la vista con el formulario para crear un nuevo usuario.
     *
     * Ruta: GET /users/create
     * Autorización: Requiere permiso 'users.create'
     *
     * @return \Illuminate\View\View Vista con formulario de creación
     *
     * @audit
     * - Autorización: UserPolicy::create() verifica 'users.create'
     * - Datos: Incluye roles activos para el select
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $roles = $this->userService->getActiveRoles();

        // Si es super admin, pasar lista de ISPs para que pueda asignar usuario a cualquier ISP
        $isps = null;
        /** @var \App\Modules\ControlAcceso\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->isSuperAdmin()) {
            $isps = \App\Modules\Sistema\Models\Isp::withoutGlobalScope(\App\Core\Scopes\IspScope::class)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();

            $roles = $roles->where('name', 'administrador')->values();
        } else {
            $roles = $roles->where('name', '!=', 'administrador')->values();
        }

        return view('users.create', compact('roles', 'isps'));
    }

    /**
     * Almacenar un nuevo usuario
     *
     * Procesa el request para crear un nuevo usuario. La validación se realiza
     * automáticamente mediante StoreUserRequest. Si hay un error, se redirige
     * de vuelta con los datos y mensaje de error.
     *
     * Ruta: POST /users
     * Autorización: Requiere permiso 'users.create'
     * Validación: StoreUserRequest
     *
     * @param StoreUserRequest $request Request validado con datos del usuario
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje flash
     *
     * @audit
     * - Autorización: UserPolicy::create() verifica 'users.create'
     * - Validación: StoreUserRequest valida datos antes de procesar
     * - Manejo de errores: Try-catch captura excepciones del servicio
     * - Mensajes: Flash messages para feedback al usuario
     * - Evento: UserActualizado disparado por el servicio
     */
    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        try {
            $this->userService->createUser($request->validated());

            return redirect()->route('users.index')
                ->with('success', 'Usuario creado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Mostrar detalle de un usuario
     *
     * Retorna la vista con los detalles completos de un usuario, incluyendo
     * su rol y permisos.
     *
     * Ruta: GET /users/{user}
     * Autorización: Requiere permiso 'users.read'
     *
     * @param User $user Usuario a mostrar (route model binding)
     * @return \Illuminate\View\View Vista con detalle del usuario
     *
     * @audit
     * - Autorización: UserPolicy::view() verifica 'users.read'
     * - Route Model Binding: Laravel resuelve automáticamente el usuario
     * - Relaciones: Se cargan role y role.permissions
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user = $this->userService->getUserWithRelations($user);
        return view('users.show', compact('user'));
    }

    /**
     * Mostrar formulario de edición de usuario
     *
     * Retorna la vista con el formulario para editar un usuario existente.
     *
     * Ruta: GET /users/{user}/edit
     * Autorización: Requiere permiso 'users.update'
     *
     * @param User $user Usuario a editar (route model binding)
     * @return \Illuminate\View\View Vista con formulario de edición
     *
     * @audit
     * - Autorización: UserPolicy::update() verifica 'users.update'
     * - Datos: Incluye usuario y roles activos
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = $this->userService->getActiveRoles();
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Actualizar un usuario existente
     *
     * Procesa el request para actualizar un usuario. La validación se realiza
     * automáticamente mediante UpdateUserRequest.
     *
     * Ruta: PUT/PATCH /users/{user}
     * Autorización: Requiere permiso 'users.update'
     * Validación: UpdateUserRequest
     *
     * @param UpdateUserRequest $request Request validado con datos a actualizar
     * @param User $user Usuario a actualizar (route model binding)
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje flash
     *
     * @audit
     * - Autorización: UserPolicy::update() verifica 'users.update'
     * - Validación: UpdateUserRequest valida datos
     * - Manejo de errores: Try-catch captura excepciones
     * - Evento: UserActualizado disparado por el servicio
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        try {
            $this->userService->updateUser($user, $request->validated());

            return redirect()->route('users.index')
                ->with('success', 'Usuario actualizado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Eliminar un usuario
     *
     * Elimina un usuario del sistema. Previene la auto-eliminación.
     *
     * Ruta: DELETE /users/{user}
     * Autorización: Requiere permiso 'users.delete'
     *
     * @param User $user Usuario a eliminar (route model binding)
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje flash
     *
     * @audit
     * - Autorización: UserPolicy::delete() verifica 'users.delete' y previene auto-eliminación
     * - Validación: El servicio previene auto-eliminación adicional
     * - Manejo de errores: Try-catch captura excepciones
     * - Evento: UserActualizado disparado por el servicio
     * - Integridad: Foreign keys manejan relaciones
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        try {
            $this->userService->deleteUser($user);

            return redirect()->route('users.index')
                ->with('success', 'Usuario eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        }
    }
}
