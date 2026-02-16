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
use App\Modules\Red\Services\RouterOSDhcpService;
use App\Core\Services\TenantConnectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PlanController extends Controller
{
    public function __construct(
        private PlanService $planService
    ) {}

    /**
     * Asegura que la tabla plan_dhcp_config exista en el tenant actual.
     * La crea con Schema si no existe (no depende de migraciones en la petición).
     */
    private function asegurarTablasPlanDhcp(): void
    {
        $tenantConn = TenantConnectionService::currentTenantConnectionName();
        if (!$tenantConn) {
            return;
        }
        try {
            if (Schema::connection($tenantConn)->hasTable('plan_dhcp_config')) {
                return;
            }
        } catch (\Throwable $e) {
            Log::debug('PlanController: hasTable plan_dhcp_config falló', ['error' => $e->getMessage()]);
        }
        try {
            Schema::connection($tenantConn)->create('plan_dhcp_config', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('plan_id');
                $table->string('nombre_servidor_routeros')->nullable();
                $table->string('interfaz')->nullable();
                $table->string('pool_nombre')->nullable();
                $table->string('red_cidr', 50)->nullable();
                $table->string('rango_ip')->nullable();
                $table->string('gateway', 45)->nullable();
                $table->string('dns')->nullable();
                $table->string('domain')->nullable();
                $table->string('lease_time', 20)->nullable();
                $table->unsignedBigInteger('isp_id')->nullable();
                $table->timestamps();
                $table->foreign('plan_id')->references('id')->on('planes')->cascadeOnDelete();
            });
            Log::info('PlanController: tabla plan_dhcp_config creada en tenant', ['conn' => $tenantConn]);
        } catch (\Throwable $e) {
            Log::warning('PlanController: no se pudo crear plan_dhcp_config', ['conn' => $tenantConn, 'error' => $e->getMessage()]);
        }
    }

    public function index(Request $request)
    {
        $tenantConn = TenantConnectionService::currentTenantConnectionName();
        if (!$tenantConn) {
            return redirect()->route('dashboard')->with('warning', 'No hay ISP configurado. Debe usar una cuenta asignada a un ISP con base de datos.');
        }
        $this->asegurarTablasPlanDhcp();
        $this->authorize('viewAny', Plan::class);
        $routerSeleccionado = $request->input('router_id');
        $tipoConexion = $request->input('tipo_conexion', 'pppoe'); // pppoe, dhcp, estatica
        $routers = Router::where('estado', true)->orderBy('nombre')->get();

        $query = Plan::with('router')->orderBy('nombre');

        if ($routerSeleccionado) {
            $query->where('router_id', $routerSeleccionado);
        }

        if (in_array($tipoConexion, ['pppoe', 'dhcp', 'estatica'])) {
            $query->where('tipo_conexion', $tipoConexion);
        }

        $planes = $query->paginate(20)->withQueryString();

        return view('servicios.planes.index', compact('planes', 'routers', 'routerSeleccionado', 'tipoConexion'));
    }

    public function create(Request $request)
    {
        if (!TenantConnectionService::currentTenantConnectionName()) {
            return redirect()->route('dashboard')->with('warning', 'No hay ISP configurado. Debe usar una cuenta asignada a un ISP con base de datos.');
        }
        $this->authorize('create', Plan::class);
        $routerId = $request->input('router_id');
        $tipoConexion = $request->input('tipo_conexion', 'pppoe');
        $routers = Router::where('estado', true)->orderBy('nombre')->get();

        return view('servicios.planes.create', compact('routers', 'routerId', 'tipoConexion'));
    }

    public function store(StorePlanRequest $request)
    {
        $this->authorize('create', Plan::class);
        try {
            Plan::create($request->validated());

            return redirect()
                ->route('servicios.planes.index', ['router_id' => $request->router_id, 'tipo_conexion' => $request->tipo_conexion ?? 'pppoe'])
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
        $this->asegurarTablasPlanDhcp();
        $this->authorize('view', $plan);
        $plan->load('router', 'dhcpConfig');
        return view('servicios.planes.show', compact('plan'));
    }

    public function edit(Plan $plan)
    {
        $this->asegurarTablasPlanDhcp();
        $this->authorize('update', $plan);
        $plan->load('router', 'dhcpConfig');
        $routers = Router::where('estado', true)->orderBy('nombre')->get();

        return view('servicios.planes.edit', compact('plan', 'routers'));
    }

    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        $this->authorize('update', $plan);
        try {
            $plan->update($request->validated());

            return redirect()
                ->route('servicios.planes.index', ['router_id' => $plan->router_id, 'tipo_conexion' => $plan->tipo_conexion ?? 'pppoe'])
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
        $this->authorize('delete', $plan);
        if ($plan->servicios()->exists()) {
            return back()
                ->with('error', 'No se puede eliminar el plan porque tiene servicios asociados.');
        }

        $routerId = $plan->router_id;
        $tipoConexion = $plan->tipo_conexion ?? 'pppoe';
        $plan->delete();

        return redirect()
            ->route('servicios.planes.index', ['router_id' => $routerId, 'tipo_conexion' => $tipoConexion])
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

    /** Lista interfaces (ether, vlan, bridge) para DHCP. */
    public function getInterfacesDhcp(Request $request, RouterOSDhcpService $dhcpService)
    {
        $routerId = $request->input('router_id');
        if (!$routerId) {
            return response()->json(['success' => false, 'message' => 'router_id requerido'], 422);
        }
        $router = Router::find($routerId);
        if (!$router) {
            return response()->json(['success' => false, 'message' => 'Router no encontrado'], 404);
        }
        $interfaces = $dhcpService->getInterfaces($router);
        return response()->json(['success' => true, 'interfaces' => $interfaces]);
    }

    /** Lista servidores DHCP del router para importar. */
    public function getServidoresDhcp(Request $request, RouterOSDhcpService $dhcpService)
    {
        $this->asegurarTablasPlanDhcp();
        $routerId = $request->input('router_id');
        if (!$routerId) {
            return response()->json(['success' => false, 'message' => 'router_id requerido'], 422);
        }
        $router = Router::find($routerId);
        if (!$router) {
            return response()->json(['success' => false, 'message' => 'Router no encontrado'], 404);
        }
        $servidores = $dhcpService->getServidoresDhcp($router);
        return response()->json(['success' => true, 'servidores' => $servidores]);
    }

    /** Detalle completo de un servidor DHCP (red, gateway, DNS, pool). */
    public function getDetalleServidorDhcp(Request $request, RouterOSDhcpService $dhcpService)
    {
        $routerId = $request->input('router_id');
        $nombre = $request->input('nombre_servidor');
        if (!$routerId || !$nombre) {
            return response()->json(['success' => false, 'message' => 'router_id y nombre_servidor requeridos'], 422);
        }
        $router = Router::find($routerId);
        if (!$router) {
            return response()->json(['success' => false, 'message' => 'Router no encontrado'], 404);
        }
        $detalle = $dhcpService->getDetalleCompletoServidorDhcp($router, $nombre);
        if ($detalle === null) {
            return response()->json(['success' => false, 'message' => 'Servidor DHCP no encontrado'], 404);
        }
        return response()->json(['success' => true, 'detalle' => $detalle]);
    }

    /** Importar servidores DHCP desde MikroTik: obtiene detalle de cada uno y guarda Plan + PlanDhcpConfig. */
    public function importarDhcp(Request $request, RouterOSDhcpService $dhcpService)
    {
        $this->asegurarTablasPlanDhcp();
        $request->validate([
            'router_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if ($value && ! Router::where('id', (int) $value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => 'router']));
                    }
                },
            ],
            'servidores' => 'required|array',
            'servidores.*.nombre_servidor' => 'required|string|max:255',
            'servidores.*.nombre_plan' => 'nullable|string|max:255',
            'servidores.*.precio_mensual' => 'nullable|numeric|min:0',
        ]);

        try {
            $router = Router::findOrFail($request->router_id);
            $servidoresParaGuardar = [];
            foreach ($request->servidores as $s) {
                $nombreServidor = $s['nombre_servidor'] ?? '';
                if ($nombreServidor === '') {
                    continue;
                }
                $detalle = $dhcpService->getDetalleCompletoServidorDhcp($router, $nombreServidor);
                if ($detalle === null) {
                    continue;
                }
                $servidoresParaGuardar[] = [
                    'nombre_servidor' => $nombreServidor,
                    'nombre_plan' => $s['nombre_plan'] ?? $nombreServidor,
                    'precio_mensual' => $s['precio_mensual'] ?? null,
                    'velocidad_bajada_mbps' => $s['velocidad_bajada_mbps'] ?? null,
                    'velocidad_subida_mbps' => $s['velocidad_subida_mbps'] ?? null,
                    'detalle' => $detalle,
                ];
            }
            $resultado = $this->planService->guardarServidoresDhcpImportados($router->id, $servidoresParaGuardar);
            return response()->json([
                'success' => true,
                'message' => "Guardados: {$resultado['guardados']}, Actualizados: {$resultado['actualizados']}, Errores: {$resultado['errores']}",
                'resultado' => $resultado,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al importar DHCP: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
