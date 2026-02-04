<?php

namespace App\Modules\Sistema\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sistema\Requests\StoreOnuModeloRequest;
use App\Modules\Sistema\Requests\UpdateOnuModeloRequest;
use App\Modules\Servicios\Models\OnuModelo;
use Illuminate\Http\Request;

class OnuModeloController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Modules\Servicios\Models\OnuModelo::with('marca');

        // Filtro por marca
        $marcaId = $request->input('marca_id');
        if (!empty($marcaId)) {
            $query->where('marca_id', $marcaId);
        }

        $modelos = $query->orderBy('marca_id')
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString(); // Usar withQueryString() en lugar de appends()

        // Obtener todas las marcas para el filtro
        $marcas = \App\Modules\Sistema\Models\OnuMarca::where('estado', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        // Si la ruta es la nueva (equipo/modelos), usar la vista con sub-pestañas
        if (request()->is('sistema/equipo/modelos*')) {
            return view('sistema.modelos-onu.index', compact('modelos', 'marcas'));
        }

        // Mantener compatibilidad con ruta antigua
        return view('sistema.modelos-onu.index', compact('modelos', 'marcas'));
    }

    public function create()
    {
        $marcas = \App\Modules\Sistema\Models\OnuMarca::where('estado', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        return view('sistema.modelos-onu.create', compact('marcas'));
    }

    public function store(StoreOnuModeloRequest $request)
    {
        $data = $request->validated();
        $data['estado'] = $data['estado'] ?? true;
        $data['orden'] = $data['orden'] ?? 0;
        $data['requiere_transformacion'] = $data['requiere_transformacion'] ?? false;

        \App\Modules\Servicios\Models\OnuModelo::create($data);

        return redirect()
            ->route('sistema.equipo.modelos.index')
            ->with('success', 'Modelo ONU creado correctamente.');
    }

    public function show(OnuModelo $modelo)
    {
        $modelo->load('marca');
        // Si la ruta es la nueva, incluir sub-pestañas
        if (request()->is('sistema/equipo/modelos*')) {
            return view('sistema.modelos-onu.show', compact('modelo'));
        }
        return view('sistema.modelos-onu.show', compact('modelo'));
    }

    public function edit(OnuModelo $modelo)
    {
        $modelo->load('marca');
        return view('sistema.modelos-onu.edit', compact('modelo'));
    }

    public function update(UpdateOnuModeloRequest $request, OnuModelo $modelo)
    {
        $modelo->update($request->validated());

        // Redirigir según la ruta de origen
        $redirectRoute = request()->is('sistema/equipo/modelos*')
            ? 'sistema.equipo.modelos.index'
            : 'sistema.modelos-onu.index';

        return redirect()
            ->route($redirectRoute)
            ->with('success', 'Credenciales por defecto actualizadas correctamente.');
    }

    public function destroy(OnuModelo $modelo)
    {
        $redirectRoute = request()->is('sistema/equipo/modelos*')
            ? 'sistema.equipo.modelos.index'
            : 'sistema.modelos-onu.index';

        return redirect()
            ->route($redirectRoute)
            ->with('error', 'Los modelos ONU se eliminan desde el módulo de equipos.');
    }
}
