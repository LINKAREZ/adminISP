<?php

namespace App\Modules\Instalaciones\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\Instalaciones\Models\OrdenInstalacion;
use App\Modules\Instalaciones\Requests\CompletarOrdenRequest;
use App\Modules\Instalaciones\Requests\StoreOrdenInstalacionRequest;
use App\Modules\Instalaciones\Requests\StorePaso1ClienteRequest;
use App\Modules\Instalaciones\Requests\StorePaso2PlanRequest;
use App\Modules\Instalaciones\Requests\StorePaso2Request;
use App\Modules\Instalaciones\Requests\StorePaso3Request;
use App\Modules\Clientes\Services\ClienteService;
use App\Modules\Instalaciones\Requests\UpdateOrdenInstalacionRequest;
use App\Modules\Instalaciones\Services\ComisionService;
use App\Modules\Instalaciones\Services\InstalacionService;
use App\Modules\Red\Models\Nodo;
use App\Modules\Red\Models\Router;
use App\Modules\Servicios\Models\Plan;
use App\Modules\ControlAcceso\Models\User;
use App\Core\Services\TenantConnectionService;
use App\Core\Services\TenantDatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrdenInstalacionController extends Controller
{
    public function __construct(
        private InstalacionService $instalacionService,
        private ClienteService $clienteService,
        private ComisionService $comisionService
    ) {}

    private function asegurarTablaInstalaciones(): void
    {
        $ispId = session('current_isp_id') ?? (app()->has('current_isp_id') ? app('current_isp_id') : null);
        TenantDatabaseService::runMigrationsIfTableMissing($ispId ? (int) $ispId : null, 'ordenes_instalacion', 'Instalaciones');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', OrdenInstalacion::class);
        $this->asegurarTablaInstalaciones();

        $query = OrdenInstalacion::with(['cliente', 'plan', 'nodo', 'router', 'ubicacion', 'servicio']);

        if ($request->filled('estado')) {
            if ($request->estado === 'disponibles') {
                $query->where('estado', OrdenInstalacion::ESTADO_PENDIENTE)->whereNull('tecnico_id');
            } else {
                $query->where('estado', $request->estado);
            }
        }
        if ($request->filled('tecnico_id')) {
            $query->where('tecnico_id', $request->tecnico_id);
        }
        if ($request->filled('buscar')) {
            $term = $request->buscar;
            $query->whereHas('cliente', function ($q) use ($term) {
                $q->where('nombre', 'like', "%{$term}%")
                    ->orWhere('documento', 'like', "%{$term}%")
                    ->orWhere('telefonos', 'like', "%{$term}%");
            });
        }

        $ordenes = $query->orderByRaw("FIELD(estado, 'borrador', 'en_curso', 'programada', 'pendiente', 'completada', 'cancelada')")
            ->orderBy('fecha_programada')
            ->orderBy('id', 'desc')
            ->paginate(config('isp.paginacion.recibos', 20));

        $totalBorrador = OrdenInstalacion::where('estado', OrdenInstalacion::ESTADO_BORRADOR)->count();

        return view('instalaciones.index', compact('ordenes', 'totalBorrador'));
    }

    public function create()
    {
        $this->authorize('create', OrdenInstalacion::class);
        $this->asegurarTablaInstalaciones();
        $clientes = Cliente::orderBy('nombre')->get(['id', 'nombre', 'documento', 'telefonos']);
        $planes = Plan::where('estado', true)->orderBy('nombre')->get();
        $routers = Router::where('estado', true)->orderBy('nombre')->get();
        $ispId = app()->has('current_isp_id') ? app('current_isp_id') : null;
        $tecnicosQuery = User::on(TenantConnectionService::CENTRAL_CONNECTION)->orderBy('name');
        if ($ispId) {
            $tecnicosQuery->where('isp_id', $ispId);
        } else {
            $tecnicosQuery->whereNotNull('isp_id');
        }
        $tecnicos = $tecnicosQuery->get(['id', 'name', 'email']);

        return view('instalaciones.create', compact('clientes', 'planes', 'routers', 'tecnicos'));
    }

    /** Wizard paso 1: Crear cliente (aún no existe) */
    public function paso1()
    {
        $this->authorize('create', OrdenInstalacion::class);
        $this->asegurarTablaInstalaciones();
        return view('instalaciones.wizard.paso1');
    }

    public function storePaso1(StorePaso1ClienteRequest $request)
    {
        $this->authorize('create', OrdenInstalacion::class);
        $validated = $request->validated();
        $validated = $this->clienteService->procesarDatosCliente($validated, $request);
        $cliente = Cliente::create($validated);
        $orden = OrdenInstalacion::create([
            'cliente_id' => $cliente->id,
            'estado' => OrdenInstalacion::ESTADO_BORRADOR,
            'direccion' => 'Pendiente de definir',
        ]);
        return redirect()->route('instalaciones.paso-2', $orden)->with('success', 'Cliente creado. Indica nodo, router y plan.');
    }

    /** Wizard paso 2: Nodo, Router, Plan */
    public function paso2(OrdenInstalacion $orden)
    {
        $this->authorize('update', $orden);
        if (!$orden->esBorrador()) {
            return redirect()->route('instalaciones.show', $orden)->with('error', 'Esta orden ya pasó este paso.');
        }
        $nodos = Nodo::where('estado', true)->with(['routers' => fn ($q) => $q->where('estado', true)->orderBy('nombre')])->orderBy('nombre')->get();
        $routers = Router::where('estado', true)->with('nodo')->orderBy('nombre')->get();
        $planes = Plan::where('estado', true)->with('router')->orderBy('nombre')->get();
        return view('instalaciones.wizard.paso2', compact('orden', 'nodos', 'routers', 'planes'));
    }

    public function storePaso2(StorePaso2PlanRequest $request, OrdenInstalacion $orden)
    {
        $this->authorize('update', $orden);
        if (!$orden->esBorrador()) {
            return redirect()->route('instalaciones.show', $orden)->with('error', 'Esta orden ya pasó este paso.');
        }
        $orden->update($request->validated());
        return redirect()->route('instalaciones.paso-3', $orden)->with('success', 'Plan asignado. Indica la dirección.');
    }

    /** Wizard paso 3: Dirección exacta */
    public function paso3(OrdenInstalacion $orden)
    {
        $this->authorize('update', $orden);
        if (!$orden->esBorrador()) {
            return redirect()->route('instalaciones.show', $orden)->with('error', 'Esta orden ya pasó este paso.');
        }
        return view('instalaciones.wizard.paso3', compact('orden'));
    }

    public function storePaso3(StorePaso2Request $request, OrdenInstalacion $orden)
    {
        $this->authorize('update', $orden);
        if (!$orden->esBorrador()) {
            return redirect()->route('instalaciones.show', $orden)->with('error', 'Esta orden ya pasó este paso.');
        }
        $orden->update($request->validated());
        return redirect()->route('instalaciones.paso-4', $orden)->with('success', 'Dirección guardada. Sube las fotos de referencia.');
    }

    /** Wizard paso 4: Fotos de referencia → orden queda disponible para técnicos */
    public function paso4(OrdenInstalacion $orden)
    {
        $this->authorize('update', $orden);
        if (!$orden->esBorrador()) {
            return redirect()->route('instalaciones.show', $orden)->with('error', 'Esta orden ya pasó este paso.');
        }
        return view('instalaciones.wizard.paso4', compact('orden'));
    }

    public function storePaso4(StorePaso3Request $request, OrdenInstalacion $orden)
    {
        $this->authorize('update', $orden);
        if (!$orden->esBorrador()) {
            return redirect()->route('instalaciones.show', $orden)->with('error', 'Esta orden ya pasó este paso.');
        }
        $data = [];
        foreach (['foto_1', 'foto_2', 'foto_3'] as $key) {
            $tituloKey = $key . '_titulo';
            if ($request->hasFile($key)) {
                $path = $request->file($key)->store('ordenes_instalacion/' . $orden->id, 'public');
                $data[$key] = $path;
            }
            if ($request->filled($tituloKey)) {
                $data[$tituloKey] = $request->input($tituloKey);
            }
        }
        $orden->update(array_merge($data, [
            'estado' => OrdenInstalacion::ESTADO_PENDIENTE,
            'tecnico_id' => null,
        ]));
        return redirect()->route('instalaciones.index')->with('success', 'Orden creada. Queda disponible para que un técnico la tome.');
    }

    /** Técnico toma la orden (se asigna) */
    public function tomar(OrdenInstalacion $orden)
    {
        $this->authorize('update', $orden);
        if (!$orden->estaDisponible()) {
            return redirect()->route('instalaciones.index')->with('error', 'Esta orden no está disponible o ya fue asignada.');
        }
        $orden->update([
            'tecnico_id' => auth()->id(),
            'estado' => OrdenInstalacion::ESTADO_EN_CURSO,
        ]);
        return redirect()->route('instalaciones.show', $orden)->with('success', 'Orden asignada. Puedes completar la instalación cuando termines.');
    }

    public function store(StoreOrdenInstalacionRequest $request)
    {
        $orden = OrdenInstalacion::create($request->validated());
        return redirect()
            ->route('instalaciones.show', $orden)
            ->with('success', 'Orden de instalación creada.');
    }

    public function show(OrdenInstalacion $orden)
    {
        $this->authorize('view', $orden);
        $orden->load(['cliente', 'plan', 'nodo', 'router', 'ubicacion', 'servicio']);
        return view('instalaciones.show', compact('orden'));
    }

    public function edit(OrdenInstalacion $orden)
    {
        $this->authorize('update', $orden);
        if ($orden->estaCompletada()) {
            return redirect()->route('instalaciones.show', $orden)
                ->with('info', 'No se puede editar una orden ya completada.');
        }
        $planes = Plan::where('estado', true)->orderBy('nombre')->get();
        $routers = Router::where('estado', true)->orderBy('nombre')->get();
        $ispId = app()->has('current_isp_id') ? app('current_isp_id') : null;
        $tecnicosQuery = User::on(TenantConnectionService::CENTRAL_CONNECTION)->orderBy('name');
        if ($ispId) {
            $tecnicosQuery->where('isp_id', $ispId);
        } else {
            $tecnicosQuery->whereNotNull('isp_id');
        }
        $tecnicos = $tecnicosQuery->get(['id', 'name', 'email']);

        return view('instalaciones.edit', compact('orden', 'planes', 'routers', 'tecnicos'));
    }

    public function seguimientoAltas(Request $request)
    {
        $this->authorize('viewAny', OrdenInstalacion::class);

        if (! TenantConnectionService::currentTenantConnectionName()) {
            return view('tenant-sin-configurar');
        }

        $query = OrdenInstalacion::where('estado', OrdenInstalacion::ESTADO_COMPLETADA)
            ->whereNotNull('fecha_completada')
            ->whereNotNull('servicio_id')
            ->with(['cliente', 'plan', 'servicio', 'comisionVendedor'])
            ->orderByDesc('fecha_completada');
        if ($request->filled('vendedor_id')) {
            $query->where('vendedor_id', $request->vendedor_id);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_completada', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_completada', '<=', $request->fecha_hasta);
        }
        if ($request->filled('mes_permanencia')) {
            $mes = (int) $request->mes_permanencia;
            $now = now();
            if ($mes === 1) {
                $query->where('fecha_completada', '>=', $now->copy()->subMonth());
            } elseif ($mes === 2) {
                $query->where('fecha_completada', '>=', $now->copy()->subMonths(2))
                    ->where('fecha_completada', '<', $now->copy()->subMonth());
            } elseif ($mes === 3) {
                $query->where('fecha_completada', '<', $now->copy()->subMonths(2));
            }
        }
        $ordenes = $query->paginate(25)->withQueryString();
        $vendedoresQuery = User::on(TenantConnectionService::CENTRAL_CONNECTION)->orderBy('name');
        $ispId = session('current_isp_id') ?? auth()->user()?->isp_id;
        if ($ispId) {
            $vendedoresQuery->where('isp_id', $ispId);
        } else {
            $vendedoresQuery->whereNotNull('isp_id');
        }
        $vendedores = $vendedoresQuery->get(['id', 'name']);
        return view('instalaciones.altas.index', [
            'ordenes' => $ordenes,
            'vendedores' => $vendedores,
            'comisionService' => $this->comisionService,
        ]);
    }

    public function update(UpdateOrdenInstalacionRequest $request, OrdenInstalacion $orden)
    {
        if ($orden->estaCompletada()) {
            return redirect()->route('instalaciones.show', $orden)->with('error', 'Orden ya completada.');
        }
        $orden->update($request->validated());
        return redirect()->route('instalaciones.show', $orden)->with('success', 'Orden actualizada.');
    }

    public function destroy(OrdenInstalacion $orden)
    {
        $this->authorize('delete', $orden);
        if ($orden->estaCompletada()) {
            return redirect()->route('instalaciones.index')->with('error', 'No se puede eliminar una orden completada.');
        }
        $orden->delete();
        return redirect()->route('instalaciones.index')->with('success', 'Orden eliminada.');
    }

    public function completarForm(OrdenInstalacion $orden)
    {
        $this->authorize('update', $orden);
        if (!$orden->puedeCompletar()) {
            return redirect()->route('instalaciones.show', $orden)
                ->with('error', 'Esta orden no puede completarse.');
        }
        $orden->load(['cliente', 'plan', 'router']);
        $onus = \App\Modules\Servicios\Models\Onu::whereNull('servicio_id')->orderBy('id')->get();
        $modelos = \App\Modules\Servicios\Models\OnuModelo::where('estado', true)->orderBy('marca_id')->orderBy('nombre')->get();

        $almacenTecnico = null;
        $stockTecnico = collect();
        if ($orden->tecnico_id) {
            $almacenTecnico = \App\Modules\Almacen\Models\Almacen::where('tipo', 'vehiculo')
                ->where('user_id', $orden->tecnico_id)->first();
            if ($almacenTecnico) {
                $stockTecnico = \App\Modules\Almacen\Models\Stock::where('almacen_id', $almacenTecnico->id)
                    ->where('cantidad', '>', 0)->with('articulo')->get();
            }
        }

        return view('instalaciones.completar', compact('orden', 'onus', 'modelos', 'almacenTecnico', 'stockTecnico'));
    }

    public function completar(CompletarOrdenRequest $request, OrdenInstalacion $orden)
    {
        $this->authorize('update', $orden);
        if (!$orden->puedeCompletar()) {
            return redirect()->route('instalaciones.show', $orden)->with('error', 'Esta orden no puede completarse.');
        }
        try {
            $result = $this->instalacionService->completarOrden($orden, $request->validated());
            $servicio = $result['servicio'];
            $cliente = $orden->cliente;

            return redirect()
                ->route('clientes.show', $cliente)
                ->with('success', 'Instalación completada. Servicio creado correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error al completar orden de instalación', ['orden_id' => $orden->id, 'error' => $e->getMessage()]);
            return redirect()->route('instalaciones.completar-form', $orden)
                ->withInput()
                ->with('error', 'Error al completar: ' . $e->getMessage());
        }
    }
}
