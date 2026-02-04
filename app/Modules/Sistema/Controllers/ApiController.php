<?php

namespace App\Modules\Sistema\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sistema\Models\ApiConfig;
use App\Modules\Sistema\Requests\UpdateApiConfigRequest;
use Illuminate\Support\Facades\Gate;

class ApiController extends Controller
{
    public function index()
    {
        Gate::authorize('sistema.read');
        $apis = \App\Modules\Sistema\Models\ApiConfig::orderBy('nombre')->get();

        return view('sistema.apis.index', compact('apis'));
    }

    public function initDefaults()
    {
        Gate::authorize('sistema.read');
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
        Gate::authorize('sistema.read');
        return view('sistema.apis.edit', compact('api'));
    }

    public function update(UpdateApiConfigRequest $request, ApiConfig $api)
    {
        Gate::authorize('sistema.read');
        $validated = $request->validated();

        $api->update($validated);

        return redirect()
            ->route('sistema.apis.index')
            ->with('success', 'Configuración de API actualizada correctamente.');
    }
}
