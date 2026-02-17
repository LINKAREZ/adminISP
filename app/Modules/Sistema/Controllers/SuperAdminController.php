<?php

namespace App\Modules\Sistema\Controllers;

use App\Core\Scopes\IspScope;
use App\Core\Services\TenantConnectionService;
use App\Http\Controllers\Controller;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\ControlAcceso\Models\User;
use App\Modules\ControlAcceso\Models\Role;
use App\Modules\Sistema\Models\Isp;
use App\Modules\Sistema\Requests\StoreSuperAdminUserRequest;
use App\Modules\Sistema\Services\IspExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminController extends Controller
{
    /**
     * Constructor - Verificar que el usuario sea super admin
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
                abort(403, 'Solo los super administradores pueden acceder.');
            }
            return $next($request);
        });
    }

    /**
     * Verificar si el usuario es super admin
     */
    protected function isSuperAdmin(): bool
    {
        $user = auth()->user();
        return $user && $user->isSuperAdmin();
    }

    /**
     * Dashboard - Gestión de ISPs (estadísticas y accesos rápidos).
     *
     * @return View
     */
    public function dashboard(): View
    {
        // Estadísticas básicas de ISPs
        $totalIsps = Isp::withoutGlobalScope(IspScope::class)->count();
        $ispsActivos = Isp::withoutGlobalScope(IspScope::class)->where('activo', true)->count();
        $ispsInactivos = $totalIsps - $ispsActivos;
        $totalUsuarios = User::withoutGlobalScope(IspScope::class)->count();
        $totalAdminsDefault = User::withoutGlobalScope(IspScope::class)->where('is_default_admin', true)->count();

        // Total clientes: suma en todas las BD tenant (clientes no está en la BD central)
        $totalClientes = 0;
        $ispsConBd = Isp::withoutGlobalScope(IspScope::class)->whereNotNull('database_name')->get();
        foreach ($ispsConBd as $isp) {
            TenantConnectionService::setCurrentIspId($isp->id);
            $totalClientes += Cliente::count();
        }

        $recentIsps = Isp::withoutGlobalScope(IspScope::class)
            ->withCount('users')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // Conteo de clientes por ISP (cada uno en su BD tenant)
        foreach ($recentIsps as $isp) {
            if ($isp->database_name) {
                TenantConnectionService::setCurrentIspId($isp->id);
                $isp->clientes_count = Cliente::count();
            } else {
                $isp->clientes_count = 0;
            }
        }

        $basesDeDatos = Isp::withoutGlobalScope(IspScope::class)
            ->whereNotNull('database_name')
            ->orderBy('id')
            ->get(['id', 'nombre', 'database_name']);

        return view('superadmin.dashboard', compact(
            'totalIsps',
            'ispsActivos',
            'ispsInactivos',
            'totalUsuarios',
            'totalAdminsDefault',
            'recentIsps',
            'totalClientes',
            'basesDeDatos'
        ));
    }

    /**
     * Mostrar formulario para crear usuario administrador por ISP
     */
    public function createAdminUser()
    {
        if (!$this->isSuperAdmin()) {
            abort(403, 'Solo los super administradores pueden acceder.');
        }

        $isps = Isp::withoutGlobalScope(IspScope::class)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $roles = Role::withoutGlobalScope(IspScope::class)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('superadmin.create-admin-user', compact('isps', 'roles'));
    }

    /**
     * Crear usuario administrador por ISP.
     *
     * @param StoreSuperAdminUserRequest $request
     * @return RedirectResponse
     */
    public function storeAdminUser(StoreSuperAdminUserRequest $request): RedirectResponse
    {
        if (!$this->isSuperAdmin()) {
            abort(403, 'Solo los super administradores pueden acceder.');
        }

        $validated = $request->validated();

        // Crear usuario como administrador por defecto
        // Nota: El modelo User tiene 'password' => 'hashed' en casts, así que no necesitamos Hash::make()
        $user = User::withoutGlobalScope(IspScope::class)->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'], // El modelo lo hasheará automáticamente
            'isp_id' => $validated['isp_id'],
            'role_id' => $validated['role_id'],
            'is_default_admin' => true, // Marcar como admin por defecto
        ]);

        return redirect()->route('superadmin.create-admin-user')
            ->with('success', "Usuario administrador creado exitosamente para el ISP. Este usuario no puede ser eliminado.");
    }

    /**
     * Exportar datos: vista de selección o descarga según isp_id y format.
     *
     * @param Request $request
     * @return View|Response|RedirectResponse
     */
    public function export(Request $request): View|Response|RedirectResponse
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }

        $ispId = $request->query('isp_id');
        $format = $request->query('format');

        if ($ispId && in_array($format, ['sql', 'json'], true)) {
            $isp = Isp::withoutGlobalScope(IspScope::class)->find($ispId);

            if (!$isp) {
                return redirect()->route('superadmin.export')
                    ->with('error', 'ISP no encontrado.');
            }

            $service = app(IspExportService::class);
            $filename = 'isp_' . $isp->id . '_' . now()->format('Y-m-d_His') . '.' . $format;

            if ($format === 'sql') {
                $content = $service->exportToSql($isp);
                return response($content, 200, [
                    'Content-Type' => 'application/sql',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]);
            }

            $content = $service->exportToJson($isp);
            return response($content, 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        $isps = Isp::withoutGlobalScope(IspScope::class)
            ->orderBy('nombre')
            ->get();

        return view('superadmin.export', compact('isps'));
    }
}
