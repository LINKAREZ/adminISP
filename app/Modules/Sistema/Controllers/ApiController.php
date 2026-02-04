<?php

namespace App\Modules\Sistema\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sistema\Models\ApiConfig;
use App\Modules\Sistema\Requests\UpdateApiConfigRequest;

class ApiController extends Controller
{
    public function index()
    {
        $apis = \App\Modules\Sistema\Models\ApiConfig::orderBy('nombre')->get();

        return view('sistema.apis.index', compact('apis'));
    }

    public function initDefaults()
    {
        \App\Modules\Sistema\Models\ApiConfig::firstOrCreate(
            ['nombre' => 'apisperu'],
            [
                'descripcion' => 'API APISPERU para consulta de DNI y RUC',
                'token' => '',
                'activo' => true,
            ]
        );

        return redirect()
            ->route('sistema.apis.index')
            ->with('success', 'API APISPERU creada. Ahora puedes agregar tu token.');
    }

    public function edit(ApiConfig $api)
    {
        return view('sistema.apis.edit', compact('api'));
    }

    public function update(UpdateApiConfigRequest $request, ApiConfig $api)
    {
        $validated = $request->validated();

        $api->update($validated);

        return redirect()
            ->route('sistema.apis.index')
            ->with('success', 'Configuración de API actualizada correctamente.');
    }
}
