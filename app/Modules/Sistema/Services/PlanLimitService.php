<?php

namespace App\Modules\Sistema\Services;

use App\Modules\Sistema\Models\Isp;
use App\Modules\Sistema\Models\Plan;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\ControlAcceso\Models\User;
use App\Modules\Red\Models\Router;
use App\Core\Services\TenantConnectionService;

class PlanLimitService
{
    /** Plan Gratuito (trial): 1 router, 50 clientes total. */
    public function isGratuitoPlan(?Plan $plan): bool
    {
        return $plan && $plan->max_routers !== null && $plan->max_routers >= 0;
    }

    /**
     * ¿El ISP puede añadir otro router? En plan Gratuito solo 1; de pago ilimitado.
     */
    public function canAddRouter(Isp $isp): bool
    {
        $plan = $isp->plan;
        if (!$plan || $plan->max_routers === null) {
            return true;
        }
        try {
            TenantConnectionService::registerConnectionForIspId($isp->id);
            $count = Router::count();
            return $count < $plan->max_routers;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * ¿Se puede añadir un cliente? Gratuito: total clientes < 50. De pago: por router (usar canAddClientToRouter).
     */
    public function canAddClient(Isp $isp, ?Router $router = null): bool
    {
        $plan = $isp->plan;
        if ($this->isGratuitoPlan($plan)) {
            return $this->clientCount($isp) < (int) $plan->max_clientes;
        }
        if ($router && $router->plan_id) {
            return $this->canAddClientToRouter($router);
        }
        return true;
    }

    /** ¿Se puede añadir un cliente a este router? (límite por router). */
    public function canAddClientToRouter(Router $router): bool
    {
        $plan = $router->saasPlan();
        if (!$plan || $plan->max_clientes === null) {
            return true;
        }
        return $this->clientCountForRouter($router) < $plan->max_clientes;
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
        $plan = $isp->plan;
        if (!$plan || $plan->max_usuarios === null) {
            return true;
        }
        $count = User::on('mysql')->where('isp_id', $isp->id)->count();
        return $count < $plan->max_usuarios;
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
