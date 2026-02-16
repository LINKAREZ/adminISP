<?php

namespace App\Core\Traits;

use App\Core\Services\TenantConnectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;

/**
 * Para controladores que exigen contexto tenant (ISP) y/o que exista una tabla en el tenant.
 * Usar requireIspContext() y redirectIfTenantTableMissing() para reducir código duplicado.
 */
trait RequiresTenantContext
{
    protected function requireIspContext(?string $message = null): ?RedirectResponse
    {
        $user = auth()->user();
        if (!$user || !$user->isp_id) {
            return redirect()->route('dashboard')->with('warning', $message ?? 'Debe usar una cuenta asignada a un ISP.');
        }
        return null;
    }

    protected function redirectIfTenantTableMissing(string $table, string $message): ?RedirectResponse
    {
        $conn = TenantConnectionService::currentTenantConnectionName();
        if (!$conn || !Schema::connection($conn)->hasTable($table)) {
            $ispId = auth()->user()?->isp_id;
            $comando = $ispId ? "php artisan isp:migrate-tenant --isp={$ispId}" : 'php artisan isp:migrate-tenant --isp=ID';
            return redirect()->route('dashboard')->with('warning', $message . ' ' . $comando);
        }
        return null;
    }
}
