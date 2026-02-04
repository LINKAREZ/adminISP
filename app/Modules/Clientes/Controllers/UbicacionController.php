<?php

namespace App\Modules\Clientes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Clientes\Requests\StoreUbicacionRequest;
use App\Modules\Clientes\Requests\UpdateUbicacionRequest;
use App\Modules\Clientes\Models\Ubicacion;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\Red\Models\Router;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Cliente $cliente)
    {
        $this->authorize('update', $cliente);

        $routers = \App\Modules\Red\Models\Router::where('estado', true)->get();

        // Si es petición AJAX, devolver solo el formulario
        if (request()->wantsJson() || request()->ajax()) {
            return view('clientes._form-ubicacion', [
                'cliente' => $cliente,
                'ubicacion' => null
            ])->render();
        }

        return view('clientes._form-ubicacion', [
            'cliente' => $cliente,
            'ubicacion' => null,
            'routers' => $routers
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente, Ubicacion $ubicacion)
    {
        $this->authorize('update', $cliente);

        $routers = \App\Modules\Red\Models\Router::where('estado', true)->get();

        // Si es petición AJAX, devolver solo el formulario
        if (request()->wantsJson() || request()->ajax()) {
            return view('clientes._form-ubicacion', [
                'cliente' => $cliente,
                'ubicacion' => $ubicacion,
                'routers' => $routers
            ])->render();
        }

        return view('clientes._form-ubicacion', [
            'cliente' => $cliente,
            'ubicacion' => $ubicacion,
            'routers' => $routers
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUbicacionRequest $request, Cliente $cliente)
    {
        $this->authorize('update', $cliente);

        try {
            $validated = $request->validated();
            $validated['cliente_id'] = $cliente->id;

            Ubicacion::create($validated);

            // Si es petición AJAX, devolver JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ubicación agregada correctamente.'
                ]);
            }

            return redirect()
                ->route('clientes.show', $cliente)
                ->with('success', 'Ubicación agregada correctamente.')
                ->with('active_tab', 'servicios');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Si es petición AJAX, devolver JSON con errores
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUbicacionRequest $request, Cliente $cliente, Ubicacion $ubicacion)
    {
        $this->authorize('update', $cliente);

        try {
            $validated = $request->validated();

            $ubicacion->update($validated);

            // Si es petición AJAX, devolver JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ubicación actualizada correctamente.'
                ]);
            }

            return redirect()
                ->route('clientes.show', $ubicacion->cliente_id)
                ->with('success', 'Ubicación actualizada correctamente.')
                ->with('active_tab', 'servicios');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Si es petición AJAX, devolver JSON con errores
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente, Ubicacion $ubicacion)
    {
        $this->authorize('update', $cliente);

        // Verificar si tiene servicios
        if ($ubicacion->servicios()->exists()) {
            return back()
                ->with('error', 'No se puede eliminar la ubicación porque tiene servicios asociados.');
        }

        $ubicacion->delete();

        return redirect()
            ->route('clientes.show', $cliente)
            ->with('success', 'Ubicación eliminada correctamente.')
            ->with('active_tab', 'servicios');
    }
}
