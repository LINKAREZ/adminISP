<?php

namespace App\Modules\Clientes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Clientes\Requests\StoreUbicacionRequest;
use App\Modules\Clientes\Requests\UpdateUbicacionRequest;
use App\Modules\Clientes\Models\Ubicacion;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\Red\Models\Router;
use App\Modules\Sistema\Services\PlanLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
    public function store(StoreUbicacionRequest $request, Cliente $cliente, PlanLimitService $planLimitService)
    {
        $this->authorize('update', $cliente);

        $validated = $request->validated();
        if (!empty($validated['router_id'])) {
            $router = Router::find($validated['router_id']);
            if ($router && !$planLimitService->canAddClientToRouter($router)) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Límite de clientes de este router alcanzado.'], 422);
                }
                return redirect()->back()->withInput()->withErrors(['router_id' => 'Límite de clientes de este router alcanzado.']);
            }
        }

        try {
            $validated['cliente_id'] = $cliente->id;

            $files = [];
            foreach (['foto_1', 'foto_2', 'foto_3'] as $key) {
                if ($request->hasFile($key)) {
                    $files[$key] = $request->file($key);
                }
                unset($validated[$key]);
            }

            $ubicacion = Ubicacion::create($validated);

            foreach ($files as $key => $file) {
                $path = $file->store('ubicaciones-fotos/' . $ubicacion->id, 'local');
                $ubicacion->update([$key => $path]);
            }

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

            $updates = [];
            foreach (['foto_1', 'foto_2', 'foto_3'] as $key) {
                if ($request->hasFile($key)) {
                    if ($ubicacion->$key) {
                        Storage::disk('local')->delete($ubicacion->$key);
                        Storage::disk('public')->delete($ubicacion->$key);
                    }
                    $path = $request->file($key)->store('ubicaciones-fotos/' . $ubicacion->id, 'local');
                    $updates[$key] = $path;
                }
                unset($validated[$key]);
            }
            $ubicacion->update(array_merge($validated, $updates));

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

    /**
     * Servir una foto de ubicación (protegida por autenticación).
     * Solo usuarios autenticados del mismo ISP pueden ver las fotos.
     */
    public function showFoto(Ubicacion $ubicacion, int $num)
    {
        if ($num < 1 || $num > 3) {
            abort(404);
        }
        $key = 'foto_' . $num;
        $path = $ubicacion->$key;
        if (empty($path)) {
            abort(404);
        }
        $user = auth()->user();
        if ($user->isp_id && $ubicacion->isp_id && (int) $user->isp_id !== (int) $ubicacion->isp_id) {
            abort(403);
        }
        $disk = Storage::disk('local')->exists($path) ? 'local' : (Storage::disk('public')->exists($path) ? 'public' : null);
        if (! $disk) {
            abort(404);
        }
        $mime = Storage::disk($disk)->mimeType($path) ?: 'image/jpeg';
        return response()->file(Storage::disk($disk)->path($path), [
            'Content-Type' => $mime,
        ]);
    }
}
