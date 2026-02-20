<?php

namespace App\Modules\Sistema\Services;

use App\Modules\Sistema\Models\Isp;
use App\Modules\Sistema\Models\Licencia;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\ControlAcceso\Models\User;
use App\Modules\Red\Models\Router;
use App\Core\Services\TenantConnectionService;

class PlanLimitService
{
    /** Licencia gratuita (trial): 1 router, 50 clientes total. */
    public function isGratuitaLicencia(?Licencia $licencia): bool
    {
        return $licencia && $licencia->max_routers !== null && $licencia->max_routers >= 0;
    }

    /**
     * ¿El ISP puede añadir otro router? En licencia gratuita solo 1; de pago ilimitado.
     */
    public function canAddRouter(Isp $isp): bool
    {
        $licencia = $isp->licencia;
        if (!$licencia || $licencia->max_routers === null) {
            return true;
        }
        try {
            TenantConnectionService::registerConnectionForIspId($isp->id);
            $count = Router::count();
            return $count < $licencia->max_routers;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * ¿Se puede añadir un cliente? Gratuita: total clientes < 50. De pago: por router (usar canAddClientToRouter).
     */
    public function canAddClient(Isp $isp, ?Router $router = null): bool
    {
        $licencia = $isp->licencia;
        if ($this->isGratuitaLicencia($licencia)) {
            return $this->clientCount($isp) < (int) $licencia->max_clientes;
        }
        if ($router && $router->licencia_id) {
            return $this->canAddClientToRouter($router);
        }
        return true;
    }

    /** ¿Se puede añadir un cliente a este router? (límite por router). */
    public function canAddClientToRouter(Router $router): bool
    {
        $licencia = $router->licencia();
        if (!$licencia || $licencia->max_clientes === null) {
            return true;
        }
        return $this->clientCountForRouter($router) < $licencia->max_clientes;
    }

    /** Cuenta de clientes con al menos una ubicación en este router. */
    public function clientCountForRouter(Router $router): int
    {
        return Cliente::whereHas('ubicaciones', function ($q) use ($router) {
            $q->where('router_id', $router->id);
        })->count();
    }

    public function canAddUser(Isp $isp): bool
    {
        $licencia = $isp->licencia;
        if (!$licencia || $licencia->max_usuarios === null) {
            return true;
        }
        $count = User::on('mysql')->where('isp_id', $isp->id)->count();
        return $count < $licencia->max_usuarios;
    }

    public function clientCount(Isp $isp): int
    {
        if (!$isp->database_name) {
            return 0;
        }
        try {
            TenantConnectionService::registerConnectionForIspId($isp->id);
            return Cliente::count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /** Cuántos routers tiene el tenant del ISP (para mensajes de límite). */
    public function routerCount(Isp $isp): int
    {
        try {
            TenantConnectionService::registerConnectionForIspId($isp->id);
            return Router::count();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
