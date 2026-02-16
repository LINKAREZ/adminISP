<?php

namespace App\Modules\ControlAcceso\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ControlAcceso\Requests\StorePermissionRequest;
use App\Modules\ControlAcceso\Requests\UpdatePermissionResourceRequest;
use App\Modules\ControlAcceso\Requests\DestroyPermissionResourceRequest;
use App\Modules\ControlAcceso\Models\Permission;
use App\Modules\ControlAcceso\Services\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(
        private PermissionService $permissionService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Permission::class);

        $filters = [
            'module' => $request->get('module'),
            'search' => $request->get('search') ?? $request->get('buscar'),
        ];

        // Obtener todos los permisos como colección (no paginado) para poder agruparlos por módulo
        $permissions = $this->permissionService->getAllPermissions($filters);
        $modules = $this->permissionService->getModules();

        return view('permissions.index', compact('permissions', 'modules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Permission::class);

        return view('permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRequest $request)
    {
        $this->authorize('create', Permission::class);

        try {
            $permissions = $this->permissionService->createResourcePermissions($request->validated());

            if ($permissions->isEmpty()) {
                return redirect()->back()
                    ->withInput()
                    ->with('warning', 'Los permisos para este submódulo ya existen.');
            }

            $count = $permissions->count();
            $message = $count === 4
                ? "Se crearon {$count} permisos correctamente (Crear, Ver, Editar, Eliminar)."
                : "Se crearon {$count} permiso(s) correctamente.";

            return redirect()->route('permissions.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear los permisos: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        $this->authorize('view', $permission);

        $permission = $this->permissionService->getPermissionWithRoles($permission);
        return view('permissions.show', compact('permission'));
    }

    /**
     * Mostrar todos los permisos de un submódulo
     */
    public function showResource(Request $request)
    {
        $this->authorize('viewAny', Permission::class);

        $resource = $request->get('resource');
        $module = $request->get('module');

        if (!$resource || !$module) {
            return redirect()->route('permissions.index')
                ->with('error', 'Submódulo y módulo son requeridos.');
        }

        $permissions = $this->permissionService->getResourcePermissions($resource);

        return view('permissions.show-resource', compact('permissions', 'resource', 'module'));
    }

    /**
     * Mostrar formulario para editar un submódulo
     */
    public function editResource(Request $request)
    {
        // Verificar permiso - usar permissions.index o permissions.create para gestionar submódulos
        $this->authorize('viewAny', Permission::class);

        $resource = $request->get('resource');
        $module = $request->get('module');

        if (!$resource || !$module) {
            return redirect()->route('permissions.index')
                ->with('error', 'Submódulo y módulo son requeridos.');
        }

        $permissions = $this->permissionService->getResourcePermissions($resource);

        return view('permissions.edit-resource', compact('resource', 'module', 'permissions'));
    }

    /**
     * Actualizar el nombre de un submódulo
     */
    public function updateResource(UpdatePermissionResourceRequest $request)
    {
        // Verificar permiso - usar permissions.index o permissions.create para gestionar submódulos
        $this->authorize('viewAny', Permission::class);

        try {
            $validated = $request->validated();
            $updated = $this->permissionService->updateResourceName(
                $validated['resource'],
                $validated['new_resource'],
                $validated['module']
            );

            return redirect()->route('permissions.index')
                ->with('success', "Se actualizó el nombre del submódulo. {$updated} permiso(s) actualizado(s).");
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el submódulo: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar todos los permisos de un submódulo
     */
    public function destroyResource(DestroyPermissionResourceRequest $request)
    {
        // Verificar permiso - usar permissions.index o permissions.create para gestionar submódulos
        $this->authorize('viewAny', Permission::class);

        try {
            $validated = $request->validated();
            $deleted = $this->permissionService->deleteResourcePermissions(
                $validated['resource'],
                $validated['module']
            );

            return redirect()->route('permissions.index')
                ->with('success', "Se eliminó el submódulo. {$deleted} permiso(s) eliminado(s).");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar el submódulo: ' . $e->getMessage());
        }
    }
}
