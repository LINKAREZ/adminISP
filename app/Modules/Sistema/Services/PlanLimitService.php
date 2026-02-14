<?php

namespace App\Modules\Sistema\Services;

use App\Modules\Sistema\Models\Isp;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\ControlAcceso\Models\User;

class PlanLimitService
{
    public function canAddClient(Isp $isp): bool
    {
        $plan = $isp->plan;
        if (!$plan || $plan->max_clientes === null) {
            return true;
        }
        return $this->clientCount($isp) < $plan->max_clientes;
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
            \App\Core\Services\TenantConnectionService::registerConnectionForIspId($isp->id);
            return Cliente::count();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
