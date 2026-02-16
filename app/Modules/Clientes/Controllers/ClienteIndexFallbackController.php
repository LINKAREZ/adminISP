<?php

namespace App\Modules\Clientes\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Services\TenantConnectionService;
use Illuminate\Http\Request;

/**
 * Controlador minimal para /clientes y /clientes/create.
 * Devuelve 200: mensaje amigable si no hay tenant, o delega a ClienteController.
 */
class ClienteIndexFallbackController extends Controller
{
    public function index(Request $request)
    {
        try {
            if (!TenantConnectionService::currentTenantConnectionName()) {
                return view('tenant-sin-configurar');
            }
            return app(ClienteController::class)->index($request);
        } catch (\Throwable $e) {
            return view('tenant-sin-configurar');
        }
    }

    public function create(Request $request)
    {
        try {
            if (!TenantConnectionService::currentTenantConnectionName()) {
                return view('tenant-sin-configurar');
            }
            return app(ClienteController::class)->create();
        } catch (\Throwable $e) {
            return view('tenant-sin-configurar');
        }
    }
}
