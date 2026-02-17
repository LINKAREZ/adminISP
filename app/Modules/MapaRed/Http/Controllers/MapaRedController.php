<?php

namespace App\Modules\MapaRed\Http\Controllers;

use App\Core\Services\TenantConnectionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;

class MapaRedController extends Controller
{
    /**
     * Exige que el usuario tenga isp_id (contexto tenant). Si no, redirige al dashboard.
     */
    private function requireIspContext(): ?RedirectResponse
    {
        $user = auth()->user();
        if (!$user || !$user->isp_id) {
            return redirect()->route('dashboard')
                ->with('warning', 'Para usar Mapa de Red debe iniciar sesión con un usuario asignado a un ISP.');
        }
        return null;
    }

    /**
     * Comprueba que la tabla mapa_red_proyectos exista en la conexión tenant. Si no, redirige con mensaje.
     */
    private function ensureMapaRedTablesExist(): ?RedirectResponse
    {
        $ispId = session('current_isp_id') ?? auth()->user()?->isp_id;
        $conn = TenantConnectionService::currentTenantConnectionName();
        if (!$conn || !Schema::connection($conn)->hasTable('mapa_red_proyectos')) {
            $comando = $ispId
                ? 'php artisan isp:migrate-tenant --isp=' . $ispId
                : 'php artisan isp:migrate-tenant --isp=ID';
            return redirect()->route('dashboard')
                ->with('warning', 'Para usar Mapa de Red ejecute las migraciones del ISP. En el servidor ejecute: ' . $comando);
        }
        return null;
    }

    public function index(): RedirectResponse|\Illuminate\View\View
    {
        if (!auth()->user()->hasPermission('mapa-red.read') && !auth()->user()->hasPermission('infraestructura.read')) {
            abort(403);
        }
        $redirect = $this->requireIspContext();
        if ($redirect) {
            return $redirect;
        }
        $redirect = $this->ensureMapaRedTablesExist();
        if ($redirect) {
            return $redirect;
        }
        return view('mapa-red.index');
    }
}
