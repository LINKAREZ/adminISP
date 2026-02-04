<?php

namespace App\Modules\Servicios\Controllers;

use App\Http\Controllers\Controller;

/**
 * Controlador para la página principal del módulo Servicios
 *
 * Muestra la página principal con las opciones disponibles del módulo.
 */
class ServicioMainController extends Controller
{
    /**
     * Mostrar la página principal del módulo Servicios
     */
    public function index()
    {
        return view('servicios.index');
    }
}
