<?php

namespace App\Modules\Sistema\Controllers;

use App\Core\Scopes\IspScope;
use App\Core\Services\TenantConnectionService;
use App\Http\Controllers\Controller;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\ControlAcceso\Models\User;
use App\Modules\ControlAcceso\Models\Role;
use App\Modules\Sistema\Models\Isp;
use App\Modules\Sistema\Models\Licencia;
use App\Modules\Sistema\Models\TenantRequest;
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
     * @return View|\Illuminate\Http\Response
     */
    public function dashboard()
    {
        try {
            return $this->renderDashboard();
        } catch (\Throwable $e) {
            report($e);
            return response()->view('superadmin.error-dashboard-raw', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Renderizado del dashboard completo (estadísticas, ISPs recientes, bases de datos).
     */
    private function renderDashboard(): View
    {
        // Estadísticas básicas de ISPs (activo puede no existir en BD antiguas; migración 2026_02_16_200000 la añade)
        $query = Isp::withoutGlobalScope(IspScope::class);
        $totalIsps = $query->count();
        $ispsActivos = \Illuminate\Support\Facades\Schema::connection(\App\Core\Services\TenantConnectionService::centralConnection())->hasColumn('isps', 'activo')
            ? (clone $query)->where('activo', true)->count()
            : $totalIsps;
        $ispsInactivos = $totalIsps - $ispsActivos;
        $totalUsuarios = User::withoutGlobalScope(IspScope::class)->count();
        $totalAdminsDefault = User::withoutGlobalScope(IspScope::class)->where('is_default_admin', true)->count();

        // Total clientes: suma en todas las BD tenant (clientes no está en la BD central)
        $totalClientes = 0;
        $previousIspId = session('current_isp_id');
        $ispsConBd = Isp::withoutGlobalScope(IspScope::class)->whereNotNull('database_name')->get();
        foreach ($ispsConBd as $isp) {
            try {
                TenantConnectionService::setCurrentIspId($isp->id);
                $totalClientes += Cliente::count();
            } catch (\Throwable $e) {
                // Tenant sin BD migrada o conexión fallida: no sumar y seguir
                report($e);
            }
        }
        if ($previousIspId !== null) {
            try {
                TenantConnectionService::setCurrentIspId((int) $previousIspId);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $centralConn = TenantConnectionService::centralConnection();
        $columnsRecent = ['id', 'nombre', 'database_name', 'created_at'];
        if (\Illuminate\Support\Facades\Schema::connection($centralConn)->hasColumn('isps', 'activo')) {
            $columnsRecent[] = 'activo';
        }
        $recentIsps = Isp::withoutGlobalScope(IspScope::class)
            ->select($columnsRecent)
            ->withCount('users')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
        if (!\Illuminate\Support\Facades\Schema::connection($centralConn)->hasColumn('isps', 'activo')) {
            $recentIsps->each(fn ($isp) => $isp->setAttribute('activo', true));
        }

        // Conteo de clientes por ISP (cada uno en su BD tenant)
        $previousIspId = session('current_isp_id');
        foreach ($recentIsps as $isp) {
            if ($isp->database_name) {
                try {
                    TenantConnectionService::setCurrentIspId($isp->id);
                    $isp->clientes_count = Cliente::count();
                } catch (\Throwable $e) {
                    report($e);
                    $isp->clientes_count = 0;
                }
            } else {
                $isp->clientes_count = 0;
            }
        }
        if ($previousIspId !== null) {
            try {
                TenantConnectionService::setCurrentIspId((int) $previousIspId);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $basesDeDatos = Isp::withoutGlobalScope(IspScope::class)
            ->whereNotNull('database_name')
            ->orderBy('id')
            ->get(['id', 'nombre', 'database_name']);

        $databaseCentral = $this->getCentralDatabaseInfo();

        return view('superadmin.dashboard', compact(
            'totalIsps',
            'ispsActivos',
            'ispsInactivos',
            'totalUsuarios',
            'totalAdminsDefault',
            'recentIsps',
            'totalClientes',
            'basesDeDatos',
            'databaseCentral'
        ));
    }

    /**
     * Información de la base de datos central (principal): isps, users, roles, permissions.
     */
    private function getCentralDatabaseInfo(): array
    {
        $connName = TenantConnectionService::centralConnection();
        try {
            $database = \Illuminate\Support\Facades\DB::connection($connName)->getConfig('database');
            $driver = \Illuminate\Support\Facades\DB::connection($connName)->getDriverName();
            $tables = [];
            if ($driver === 'mysql') {
                $rows = \Illuminate\Support\Facades\DB::connection($connName)->select('SHOW TABLES');
                foreach ($rows as $row) {
                    $arr = (array) $row;
                    $tables[] = reset($arr) ?: '';
                }
                $tables = array_values(array_filter($tables));
                sort($tables);
            }
            return [
                'connection' => $connName,
                'database' => $database,
                'tables' => $tables,
                'tables_count' => count($tables),
                'driver' => $driver,
            ];
        } catch (\Throwable $e) {
            return [
                'connection' => $connName,
                'database' => null,
                'tables' => [],
                'tables_count' => 0,
                'error' => $e->getMessage(),
            ];
        }
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

    /**
     * Listado de licencias SaaS (central).
     */
    public function licencias(): View
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }

        $licencias = collect();
        $conn = TenantConnectionService::centralConnection();
        if (\Illuminate\Support\Facades\Schema::connection($conn)->hasTable('licencias')) {
            $licencias = Licencia::withCount('isps')->orderBy('sort_order')->orderBy('name')->get();
        }

        return view('superadmin.licencias.index', compact('licencias'));
    }

    /**
     * Listado de solicitudes de onboarding (tenant_requests).
     */
    public function solicitudes(): View
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }

        $conn = TenantConnectionService::centralConnection();
        if (!\Illuminate\Support\Facades\Schema::connection($conn)->hasTable('tenant_requests')) {
            $solicitudes = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);
            return view('superadmin.solicitudes.index', compact('solicitudes'));
        }

        $query = TenantRequest::with('isp')->orderByDesc('created_at');

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        $solicitudes = $query->paginate(25)->withQueryString();

        return view('superadmin.solicitudes.index', compact('solicitudes'));
    }
}
