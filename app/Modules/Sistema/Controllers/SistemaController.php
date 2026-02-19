<?php

namespace App\Modules\Sistema\Controllers;

use App\Core\Services\TenantConnectionService;
use App\Http\Controllers\Controller;
use App\Modules\Sistema\Models\MedioPago;
use App\Modules\Sistema\Models\OnuMarca;
use App\Modules\Sistema\Models\ApiConfig;
use App\Modules\Servicios\Models\OnuModelo;
use App\Modules\Notificaciones\Models\PlantillaWhatsApp;
use Illuminate\Support\Facades\Gate;

class SistemaController extends Controller
{
    /**
     * Mostrar la página principal del módulo Sistema
     */
    public function index()
    {
        Gate::authorize('sistema.read');

        $conn = TenantConnectionService::currentTenantConnectionName();
        if (!$conn) {
            $estadisticas = [
                'medios_pago' => ['total' => 0, 'activos' => 0],
                'equipo' => ['marcas' => 0, 'modelos' => 0],
                'apis' => ['total' => 0, 'activas' => 0],
                'plantillas_whatsapp' => ['total' => 0, 'activas' => 0],
            ];
            return view('sistema.index', compact('estadisticas'));
        }

        $estadisticas = [
            'medios_pago' => [
                'total' => MedioPago::count(),
                'activos' => MedioPago::where('activo', true)->count(),
            ],
            'equipo' => [
                'marcas' => OnuMarca::count(),
                'modelos' => OnuModelo::count(),
            ],
            'apis' => [
                'total' => ApiConfig::count(),
                'activas' => ApiConfig::where('activo', true)->count(),
            ],
            'plantillas_whatsapp' => [
                'total' => PlantillaWhatsApp::count(),
                'activas' => PlantillaWhatsApp::where('activo', true)->count(),
            ],
        ];

        return view('sistema.index', compact('estadisticas'));
    }
}
