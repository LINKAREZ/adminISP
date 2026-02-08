<?php

namespace App\Modules\Sistema\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sistema\Models\OnuMarca;
use App\Modules\Sistema\Requests\StoreOnuMarcaRequest;
use App\Modules\Sistema\Requests\UpdateOnuMarcaRequest;

class OnuMarcaController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(OnuMarca::class, 'marca');
    }

    public function index()
    {
        $marcas = OnuMarca::with('modelosActivos')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        return view('sistema.equipo.marcas.index', compact('marcas'));
    }

    public function create()
    {
        return view('sistema.equipo.marcas.create');
    }

    public function store(StoreOnuMarcaRequest $request)
    {
        $validated = $request->validated();
        unset($validated['return_url']);

        OnuMarca::create($validated);

        $returnUrl = $request->input('return_url');
        if ($returnUrl && \Illuminate\Support\Facades\URL::isValidUrl($returnUrl)) {
            return redirect()->to($returnUrl)->with('success', 'Marca creada correctamente.');
        }

        return redirect()
            ->route('sistema.equipo.marcas.index')
            ->with('success', 'Marca creada correctamente.');
    }

    public function show(OnuMarca $marca)
    {
        $marca->load('modelosActivos');
        return view('sistema.equipo.marcas.show', compact('marca'));
    }

    public function edit(OnuMarca $marca)
    {
        return view('sistema.equipo.marcas.edit', compact('marca'));
    }

    public function update(UpdateOnuMarcaRequest $request, OnuMarca $marca)
    {
        $validated = $request->validated();

        $marca->update($validated);

        return redirect()
            ->route('sistema.equipo.marcas.index')
            ->with('success', 'Marca actualizada correctamente.');
    }

    public function destroy(OnuMarca $marca)
    {
        // Verificar si tiene modelos asociados
        if ($marca->modelos()->count() > 0) {
            return redirect()
                ->route('sistema.equipo.marcas.index')
                ->with('error', 'No se puede eliminar la marca porque tiene modelos asociados.');
        }

        $marca->delete();

        return redirect()
            ->route('sistema.equipo.marcas.index')
            ->with('success', 'Marca eliminada correctamente.');
    }
}
