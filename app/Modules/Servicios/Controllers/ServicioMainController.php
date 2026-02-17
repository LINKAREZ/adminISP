<?php

namespace App\Modules\Servicios\Controllers;

use App\Http\Controllers\Controller;

/**
 * Controlador para la página principal del módulo Servicios
 *
 * Muestra la página principal con los tipos de servicio: Internet, IPTV, CATV.
 */
class ServicioMainController extends Controller
{
    /**
     * Mostrar la página principal del módulo Servicios (tipos: Internet, IPTV, CATV)
     */
    public function index()
    {
        return view('servicios.index');
    }

    /**
     * Internet Fibra Óptica: Planes y Servicios PPPoE
     */
    public function internet()
    {
        return view('servicios.internet.index');
    }

    /**
     * IPTV - Placeholder (próximamente)
     */
    public function iptv()
    {
        return view('servicios.iptv.index');
    }

    /**
     * CATV - Placeholder (próximamente)
     */
    public function catv()
    {
        return view('servicios.catv.index');
    }
}
