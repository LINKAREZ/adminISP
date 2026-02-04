<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Traits\LogsContext;
use App\Modules\Dashboard\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use LogsContext;

    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index()
    {
        try {
            $estadisticas = $this->dashboardService->getEstadisticas();
            return view('dashboard', $estadisticas);
        } catch (\Exception $e) {
            $this->logError('Error en DashboardController::index', [
                'action' => 'dashboard_index',
            ], $e);

            return view('dashboard', [
                'totalClientes' => 0,
                'clientesNuevosMes' => 0,
                'clientesAlDia' => 0,
                'totalServicios' => 0,
                'serviciosActivos' => 0,
                'serviciosCortados' => 0,
                'serviciosNuevosMes' => 0,
                'serviciosActivosConRecibosVencidos' => 0,
                'totalRecibos' => 0,
                'recibosPendientes' => 0,
                'recibosVencidos' => 0,
                'saldoTotalPendiente' => 0,
                'montoTotalVencido' => 0,
                'totalPagos' => 0,
                'pagosMes' => 0,
                'pagosHoy' => 0,
                'pagosCountHoy' => 0,
                'pagosCountMes' => 0,
                'totalRouters' => 0,
                'routersActivos' => 0,
                'totalNodos' => 0,
                'totalPlanes' => 0,
                'serviciosRecientes' => collect(),
                'recibosVencidosRecientes' => collect(),
                'pagosRecientes' => collect(),
                'pagosDuplicados' => ['total' => 0, 'pagos' => collect(), 'pagosJson' => []],
                'serviciosPorEstado' => ['activos' => 0, 'cortados' => 0],
                'recibosPorEstado' => ['pendientes' => 0, 'vencidas' => 0, 'pagadas' => 0],
                'ingresosMensuales' => []
            ])->with('error', 'Error al cargar las estadísticas. Por favor, intenta nuevamente.');
        }
    }
}
