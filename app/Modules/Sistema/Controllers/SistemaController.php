<?php

namespace App\Modules\Sistema\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sistema\Models\MedioPago;
use App\Modules\Sistema\Models\OnuMarca;
use App\Modules\Sistema\Models\ApiConfig;
use App\Modules\Servicios\Models\OnuModelo;
use App\Modules\Notificaciones\Models\PlantillaWhatsApp;

class SistemaController extends Controller
{
    /**
     * Mostrar la página principal del módulo Sistema
     */
    public function index()
    {
        $user = auth()->user();
        
        // Estadísticas para cada sección
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
