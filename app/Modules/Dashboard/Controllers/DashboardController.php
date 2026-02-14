<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Traits\LogsContext;
use App\Modules\Dashboard\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    use LogsContext;

    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('dashboard.read');

        if ($request->query('actualizar')) {
            $ispId = session('current_isp_id') ?? (app()->has('current_isp_id') ? app('current_isp_id') : null);
            Cache::forget('dashboard_stats_' . ($ispId ?? 'global'));
            return redirect()->route('dashboard');
        }

        try {
            $estadisticas = $this->dashboardService->getEstadisticas();
            $checklist = $this->dashboardService->getChecklistPrimerosPasos($estadisticas);
            $user = auth()->user();
            $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
            $mostrarOnboardingWizard = !$isSuperAdmin
                && !session('onboarding_completed')
                && ($estadisticas['totalClientes'] ?? 0) == 0
                && ($estadisticas['totalRouters'] ?? 0) == 0;
            return view('dashboard', array_merge($estadisticas, $checklist, [
                'mostrarOnboardingWizard' => $mostrarOnboardingWizard,
            ]));
        } catch (\Exception $e) {
            $this->logError('Error en DashboardController::index', [
                'action' => 'dashboard_index',
            ], $e);

            $fallback = [
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
            ];
            $checklist = $this->dashboardService->getChecklistPrimerosPasos($fallback);
            return view('dashboard', array_merge($fallback, $checklist, ['mostrarOnboardingWizard' => false]))->with('error', 'Error al cargar las estadísticas. Por favor, intenta nuevamente.');
        }
    }
}
