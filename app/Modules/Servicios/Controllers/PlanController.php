<?php

namespace App\Modules\Servicios\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Servicios\Requests\StorePlanRequest;
use App\Modules\Servicios\Requests\UpdatePlanRequest;
use App\Modules\Servicios\Models\Plan;
use App\Modules\Servicios\Services\PlanService;
use App\Modules\Red\Models\Router;
use App\Modules\Servicios\Requests\ImportarPerfilesRequest;
use App\Modules\Servicios\Requests\GuardarPerfilesImportadosRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlanController extends Controller
{
    public function __construct(
        private PlanService $planService
    ) {}

    public function index(Request $request)
    {
        $routerSeleccionado = $request->input('router_id');
        $routers = Router::where('estado', true)->orderBy('nombre')->get();

        if ($routerSeleccionado) {
            $planes = Plan::where('router_id', $routerSeleccionado)
                ->with('router')
                ->orderBy('nombre')
                ->paginate(20);
        } else {
            $planes = Plan::with('router')
                ->orderBy('nombre')
                ->paginate(20);
        }

        return view('servicios.planes.index', compact('planes', 'routers', 'routerSeleccionado'));
    }

    public function create(Request $request)
    {
        $routerId = $request->input('router_id');
        $routers = Router::where('estado', true)->orderBy('nombre')->get();

        return view('servicios.planes.create', compact('routers', 'routerId'));
    }

    public function store(StorePlanRequest $request)
    {
        try {
            Plan::create($request->validated());

            return redirect()
                ->route('servicios.planes.index', ['router_id' => $request->router_id])
                ->with('success', 'Plan creado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear plan: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al crear el plan: ' . $e->getMessage());
        }
    }

    public function show(Plan $plan)
    {
        $plan->load('router');
        return view('servicios.planes.show', compact('plan'));
    }

    public function edit(Plan $plan)
    {
        $plan->load('router');
        $routers = Router::where('estado', true)->orderBy('nombre')->get();

        return view('servicios.planes.edit', compact('plan', 'routers'));
    }

    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        try {
            $plan->update($request->validated());

            return redirect()
                ->route('servicios.planes.index', ['router_id' => $plan->router_id])
                ->with('success', 'Plan actualizado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar plan: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el plan: ' . $e->getMessage());
        }
    }

    public function destroy(Plan $plan)
    {
        if ($plan->servicios()->exists()) {
            return back()
                ->with('error', 'No se puede eliminar el plan porque tiene servicios asociados.');
        }

        $routerId = $plan->router_id;
        $plan->delete();

        return redirect()
            ->route('servicios.planes.index', ['router_id' => $routerId])
            ->with('success', 'Plan eliminado correctamente.');
    }

    public function importarPerfiles(ImportarPerfilesRequest $request, \App\Modules\Red\Services\RouterOSPppoeService $pppoeService)
    {
        try {
            $router = Router::findOrFail($request->router_id);

            // Obtener perfiles del router
            $perfiles = $pppoeService->getProfiles($router) ?? [];

            // Procesar perfiles
            $perfilesProcesados = $this->planService->procesarPerfilesRouterOS($router, $perfiles);

            return response()->json([
                'success' => true,
                'router' => [
                    'id' => $router->id,
                    'nombre' => $router->nombre,
                ],
                'perfiles' => $perfilesProcesados
            ]);
        } catch (\Exception $e) {
            Log::error('Error al importar perfiles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al importar perfiles: ' . $e->getMessage()
            ], 500);
        }
    }

    public function guardarPerfilesImportados(GuardarPerfilesImportadosRequest $request)
    {
        try {
            $resultado = $this->planService->guardarPerfilesImportados(
                $request->router_id,
                $request->perfiles
            );

            return response()->json([
                'success' => true,
                'message' => "Guardados: {$resultado['guardados']}, Actualizados: {$resultado['actualizados']}, Errores: {$resultado['errores']}",
                'resultado' => $resultado
            ]);
        } catch (\Exception $e) {
            Log::error('Error al guardar perfiles importados: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar perfiles: ' . $e->getMessage()
            ], 500);
        }
    }
}
