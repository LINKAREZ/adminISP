<?php

namespace App\Modules\Dashboard\Controllers;

use App\Core\Contracts\Repositories\DashboardRepositoryInterface;
use App\Core\Services\TenantConnectionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardRepositoryInterface $dashboardRepository
    ) {}

    /**
     * Muestra el dashboard principal del panel (usuarios con isp_id).
     * Super admins son redirigidos al panel superadmin.
     */
    public function index(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard');
        }

        if (!TenantConnectionService::currentTenantConnectionName()) {
            return view('tenant-sin-configurar');
        }

        $estadisticas = $this->dashboardRepository->getEstadisticas();

        $databaseInfo = null;
        if ($user && method_exists($user, 'hasPermission') && $user->hasPermission('sistema.read')) {
            $databaseInfo = $this->getDatabaseInfo();
        }

        return view('dashboard', array_merge($estadisticas, ['databaseInfo' => $databaseInfo]));
    }

    /**
     * Información de la base de datos actual (tenant) para mostrar en el dashboard.
     * Solo se llama cuando el usuario tiene sistema.read.
     */
    private function getDatabaseInfo(): array
    {
        $connName = TenantConnectionService::currentTenantConnectionName();
        if (!$connName) {
            return ['connection' => null, 'database' => null, 'tables' => [], 'tables_count' => 0];
        }

        try {
            $database = DB::connection($connName)->getConfig('database');
            $driver = DB::connection($connName)->getDriverName();
            $tables = [];

            if ($driver === 'mysql') {
                $rows = DB::connection($connName)->select('SHOW TABLES');
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
}
