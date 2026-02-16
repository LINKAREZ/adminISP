<?php

namespace App\Modules\ControlAcceso\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ControlAcceso\Requests\StoreRoleRequest;
use App\Modules\ControlAcceso\Requests\UpdateRoleRequest;
use App\Modules\ControlAcceso\Models\Role;
use App\Modules\ControlAcceso\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        private RoleService $roleService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Role::class);

        // Obtener filtros de búsqueda (el formulario envía 'buscar')
        $filters = [
            'search' => $request->get('search') ?? $request->get('buscar'),
        ];

        $roles = $this->roleService->getPaginatedRoles(15, $filters);

        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Role::class);

        $permissions = $this->roleService->getPermissionsGroupedByModule();

        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        $this->authorize('create', Role::class);

        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');

            $this->roleService->createRole($data);

            return redirect()->route('roles.index')
                ->with('success', 'Rol creado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $this->authorize('view', $role);

        $role = $this->roleService->getRoleWithPermissions($role);
        $permissions = $this->roleService->getPermissionsGroupedByModule();
        $rolePermissionIds = $this->roleService->getRolePermissionIds($role);

        return view('roles.show', compact('role', 'permissions', 'rolePermissionIds'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $this->authorize('update', $role);

        $role = $this->roleService->getRoleWithPermissions($role);
        $permissions = $this->roleService->getPermissionsGroupedByModule();
        $rolePermissionIds = $this->roleService->getRolePermissionIds($role);

        return view('roles.edit', compact('role', 'permissions', 'rolePermissionIds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $this->authorize('update', $role);

        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');

            $this->roleService->updateRole($role, $data);

            return redirect()->route('roles.edit', $role)
                ->with('success', 'Rol actualizado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);

        try {
            $this->roleService->deleteRole($role);

            return redirect()->route('roles.index')
                ->with('success', 'Rol eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('roles.index')
                ->with('error', $e->getMessage());
        }
    }
}
