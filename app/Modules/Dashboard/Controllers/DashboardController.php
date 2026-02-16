<?php

namespace App\Modules\Dashboard\Controllers;

use App\Core\Contracts\Repositories\DashboardRepositoryInterface;
use App\Core\Services\TenantConnectionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

        return view('dashboard', $estadisticas);
    }
}
