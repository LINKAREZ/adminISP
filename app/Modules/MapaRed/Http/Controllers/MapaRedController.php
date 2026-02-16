<?php

namespace App\Modules\MapaRed\Http\Controllers;

use App\Core\Traits\RequiresTenantContext;
use App\Http\Controllers\Controller;

class MapaRedController extends Controller
{
    use RequiresTenantContext;

    public function index(): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
    {
        if (!auth()->user()->hasPermission('mapa-red.read') && !auth()->user()->hasPermission('infraestructura.read')) {
            abort(403);
        }
        if ($redirect = $this->requireIspContext('Para usar Mapa de Red debe iniciar sesión con un usuario asignado a un ISP.')) {
            return $redirect;
        }
        if ($redirect = $this->redirectIfTenantTableMissing('mapa_red_proyectos', 'Para usar Mapa de Red ejecute las migraciones del ISP. En el servidor ejecute:')) {
            return $redirect;
        }
        return view('mapa-red.index');
    }
}
