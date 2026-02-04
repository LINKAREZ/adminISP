<?php

namespace App\Modules\Notificaciones\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notificaciones\Models\PlantillaWhatsApp;
use App\Modules\Notificaciones\Requests\UpdatePlantillaWhatsAppRequest;

class PlantillaWhatsAppController extends Controller
{
    /**
     * Mostrar lista de plantillas
     */
    public function index()
    {
        $plantillas = PlantillaWhatsApp::orderBy('tipo')->get();

        // Determinar si estamos en el contexto de Sistema o Notificaciones
        $isSistema = request()->is('sistema/plantillas/whatsapp*');
        $viewPath = $isSistema ? 'sistema.plantillas-whatsapp.index' : 'notificaciones.plantillas.index';

        return view($viewPath, compact('plantillas'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(PlantillaWhatsApp $plantillaWhatsApp)
    {
        // Determinar si estamos en el contexto de Sistema o Notificaciones
        $isSistema = request()->is('sistema/plantillas/whatsapp*');
        $viewPath = $isSistema ? 'sistema.plantillas-whatsapp.edit' : 'notificaciones.plantillas.edit';

        return view($viewPath, compact('plantillaWhatsApp'));
    }

    /**
     * Actualizar plantilla
     */
    public function update(UpdatePlantillaWhatsAppRequest $request, PlantillaWhatsApp $plantillaWhatsApp)
    {
        $plantillaWhatsApp->update($request->validated());

        // Determinar la ruta de redirección según el contexto
        $isSistema = request()->is('sistema/plantillas/whatsapp*');
        $redirectRoute = $isSistema ? 'sistema.plantillas-whatsapp.index' : 'notificaciones.plantillas.index';

        return redirect()
            ->route($redirectRoute)
            ->with('success', 'Plantilla actualizada correctamente');
    }
}
