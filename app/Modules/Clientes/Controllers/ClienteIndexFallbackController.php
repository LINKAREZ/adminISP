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
                return response('<html><body><h1>ISP no configurado</h1><p>El ISP actual no tiene base de datos.</p></body></html>', 200, ['Content-Type' => 'text/html']);
            }
            return app(ClienteController::class)->index($request);
        } catch (\Throwable $e) {
            return response('<html><body><h1>ISP no configurado</h1><p>Configure la base de datos del ISP.</p></body></html>', 200, ['Content-Type' => 'text/html']);
        }
    }

    public function create(Request $request)
    {
        try {
            if (!TenantConnectionService::currentTenantConnectionName()) {
                return response('<html><body><h1>ISP no configurado</h1></body></html>', 200, ['Content-Type' => 'text/html']);
            }
            return app(ClienteController::class)->create();
        } catch (\Throwable $e) {
            return response('<html><body><h1>ISP no configurado</h1></body></html>', 200, ['Content-Type' => 'text/html']);
        }
    }
}
