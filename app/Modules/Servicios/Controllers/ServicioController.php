<?php

namespace App\Modules\Servicios\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Servicios\Requests\StoreServicioRequest;
use App\Modules\Servicios\Requests\UpdateServicioRequest;
use App\Modules\Servicios\Requests\CambiarEstadoServicioRequest;
use App\Modules\Servicios\Models\Servicio;
use App\Modules\Servicios\Services\ServicioService;
use App\Modules\Servicios\Repositories\ServicioRepository;
use App\Modules\Clientes\Models\Cliente;
use App\Core\Traits\RespondsWithJson;
use App\Core\Traits\NormalizesMacAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Modules\Red\Services\RouterOSPppoeService;
use App\Modules\Red\Services\RouterOSScriptService;
use App\Modules\Red\Services\RouterOSNatService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ServicioController extends Controller
{
    use RespondsWithJson, NormalizesMacAddress;

    public function __construct(
        private ServicioService $servicioService,
        private ServicioRepository $servicioRepository
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Servicio::class);
        $request->validate([
            'buscar' => ['sometimes', 'string', 'max:100'],
            'estado' => ['sometimes', 'string', 'max:20'],
            'tipo_pppoe' => ['sometimes', 'string', 'max:20'],
        ]);

        $query = Servicio::query();

        // Búsqueda usando el trait Searchable
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                // Búsqueda en columnas locales usando scopeSearch
                $q->search($buscar, ['mac_address', 'usuario_pppoe'])
                    // ✅ Búsqueda en relación cliente a través de ubicación
                    ->orWhereHas('ubicacion.cliente', function ($clienteQuery) use ($buscar) {
                        $clienteQuery->search($buscar, ['nombre', 'documento']);
                    });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo_pppoe')) {
            $query->where('tipo_pppoe', $request->tipo_pppoe);
        }

        // ✅ Cargar cliente a través de ubicación
        $servicios = $query->with(['ubicacion.cliente', 'router', 'plan', 'onu'])
            ->latest()
            ->paginate(15);

        return view('servicios.index', compact('servicios'));
    }

    public function create(Cliente $cliente)
    {
        $this->authorize('create', Servicio::class);
        $ubicaciones = $cliente->ubicaciones()->with('router')->get();
        $nodos = Cache::remember('red.nodos.activos', 600, function () {
            return \App\Modules\Red\Models\Nodo::where('estado', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre']);
        });
        $marcas = \App\Modules\Sistema\Models\OnuMarca::where('estado', true)
            ->with('modelosActivos')
            ->orderBy('orden')
            ->get();

        // Cargar todos los modelos activos para el JavaScript
        $modelos = \App\Modules\Servicios\Models\OnuModelo::where('estado', true)
            ->orderBy('marca_id')
            ->orderBy('nombre')
            ->get();

        return view('clientes.servicios.create', compact('cliente', 'ubicaciones', 'nodos', 'marcas', 'modelos'));
    }

    public function store(StoreServicioRequest $request, Cliente $cliente)
    {
        $this->authorize('create', Servicio::class);
        try {
            $servicio = DB::transaction(function () use ($request, $cliente) {
                $validated = $request->validated();
                // ❌ ELIMINADO: $validated['cliente_id'] = $cliente->id;

                // ✅ Procesar ubicación (REQUIRED ahora)
                if (empty($validated['ubicacion_id']) && !empty($validated['ubicacion_direccion'])) {
                    $ubicacion = $this->servicioService->obtenerOCrearUbicacion([
                        'direccion' => $validated['ubicacion_direccion'],
                        'referencia' => $validated['ubicacion_referencia'] ?? null,
                        'distrito' => $validated['ubicacion_distrito'] ?? null,
                        'provincia' => $validated['ubicacion_provincia'] ?? null,
                        'departamento' => $validated['ubicacion_departamento'] ?? null,
                    ], $cliente->id);
                    $validated['ubicacion_id'] = $ubicacion->id;
                }

                // ✅ Validar que ubicacion_id pertenezca al cliente
                if (!empty($validated['ubicacion_id'])) {
                    $ubicacion = \App\Modules\Clientes\Models\Ubicacion::find($validated['ubicacion_id']);
                    if (!$ubicacion || $ubicacion->cliente_id !== $cliente->id) {
                        throw new \Exception('La ubicación no pertenece al cliente seleccionado');
                    }
                }

                // Normalizar MAC address
                if (!empty($validated['mac_address'])) {
                    $validated['mac_address'] = $this->normalizarMacAddress($validated['mac_address']);
                }

                // Extraer onu_id antes de crear el servicio (no es un campo del modelo Servicio)
                $onuId = null;
                if (isset($validated['onu_id']) && $validated['onu_id'] !== '' && $validated['onu_id'] !== '0' && $validated['onu_id'] !== null) {
                    $onuId = (int)$validated['onu_id'];
                }
                // Remover onu_id de validated ya que no es un campo del modelo Servicio
                unset($validated['onu_id']);

                // Procesar credenciales provisionales
                $validated = $this->servicioService->procesarCredencialesProvisionales(
                    $validated,
                    $onuId
                );

                // Crear servicio
                $servicio = Servicio::create($validated);

                // Asociar ONU si existe (esto actualiza servicio_id en la tabla onus)
                $this->servicioService->asociarOnuAServicio(
                    $servicio,
                    $onuId,
                    $validated['mac_address'] ?? null
                );

                return $servicio;
            });

            // ✅ Obtener servicio con relaciones para invalidar cache
            $servicio = Servicio::with('ubicacion.cliente')->findOrFail($servicio->id);
            $this->servicioService->invalidarCache($servicio);

            // ✅ Obtener cliente desde ubicación
            $cliente = $servicio->ubicacion->cliente;

            // Si es petición AJAX, devolver JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Servicio creado correctamente.',
                    'servicio' => [
                        'id' => $servicio->id,
                        'cliente_id' => $cliente->id,
                    ],
                    'redirect' => route('clientes.show', $cliente)
                ]);
            }

            return redirect()
                ->route('clientes.show', $cliente)
                ->with('success', 'Servicio creado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear servicio: ' . $e->getMessage());

            // Si es petición AJAX, devolver JSON con error
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el servicio: ' . $e->getMessage(),
                    'error' => $e->getMessage()
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Error al crear el servicio: ' . $e->getMessage());
        }
    }

    public function show($clienteOrServicio = null, $servicioOrNull = null)
    {
        // Manejar diferentes casos de llamada:
        // 1. Ruta anidada: clientes/{cliente}/servicios/{servicio} -> show(Cliente $cliente, $servicio)
        // 2. Ruta normal: servicios/{servicio} -> show(Servicio $servicio)

        $servicio = null;
        $cliente = null;

        // Si el primer parámetro es una instancia de Cliente, es ruta anidada
        if ($clienteOrServicio instanceof Cliente) {
            $cliente = $clienteOrServicio;
            // El segundo parámetro es el servicio (puede ser string, int o modelo)
            if ($servicioOrNull instanceof Servicio) {
                $servicio = $servicioOrNull;
            } else {
                $servicioId = $servicioOrNull ?? request()->route('servicio');
                $servicio = Servicio::findOrFail($servicioId);
            }
        } elseif ($clienteOrServicio instanceof Servicio) {
            // Ruta normal: servicios/{servicio}
            $servicio = $clienteOrServicio;
        } else {
            // Si no es modelo, intentar obtener desde la ruta
            $servicioId = $servicioOrNull ?? request()->route('servicio') ?? $clienteOrServicio;
            $servicio = Servicio::findOrFail($servicioId);

            // Intentar obtener cliente desde la ruta si existe
            $clienteParam = request()->route('cliente');
            if ($clienteParam) {
                $cliente = $clienteParam instanceof Cliente ? $clienteParam : Cliente::findOrFail($clienteParam);
            }
        }

        $this->authorize('view', $servicio);

        // ✅ Cargar todas las relaciones necesarias para la vista
        $servicio->load([
            'ubicacion.cliente',
            'ubicacion.router',
            'router.nodo',
            'plan',
            'onu',
            'recibos',
            'pagos'
        ]);

        // Debug: Verificar si onu se cargó y si existe
        if (!$servicio->relationLoaded('onu')) {
            Log::warning("ONU no cargada para servicio {$servicio->id}, intentando cargar manualmente");
            $servicio->load('onu');
        }

        // Verificar si la ONU existe en la base de datos
        if ($servicio->onu_id) {
            $onuExists = \App\Modules\Servicios\Models\Onu::where('id', $servicio->onu_id)
                ->where('servicio_id', $servicio->id)
                ->exists();

            if (!$onuExists && $servicio->onu) {
                // Si la relación está cargada pero no coincide, recargar
                $servicio->load('onu');
            } elseif (!$onuExists) {
                // Si no existe, limpiar la relación
                $servicio->setRelation('onu', null);
            }
        }
        $fromCliente = request()->has('from_cliente') || request()->routeIs('clientes.servicios.*');

        // ✅ Obtener cliente desde ubicación o usar el parámetro de ruta
        $clienteResolved = $servicio->ubicacion->cliente ?? $cliente;

        // ✅ Verificar que el servicio pertenece al cliente (si se proporcionó cliente)
        if ($clienteResolved && $servicio->ubicacion && $servicio->ubicacion->cliente_id !== $clienteResolved->id) {
            abort(404, 'El servicio no pertenece al cliente especificado.');
        }

        return view('servicios.show', compact('servicio', 'fromCliente', 'cliente'));
    }

    public function edit($clienteOrServicio = null, $servicioOrNull = null)
    {
        // Manejar diferentes casos de llamada:
        // 1. Ruta anidada: clientes/{cliente}/servicios/{servicio}/edit -> edit(Cliente $cliente, $servicio)
        // 2. Ruta normal: servicios/{servicio}/edit -> edit(Servicio $servicio)

        $servicio = null;
        $cliente = null;

        // Si el primer parámetro es una instancia de Cliente, es ruta anidada
        if ($clienteOrServicio instanceof Cliente) {
            $cliente = $clienteOrServicio;
            // El segundo parámetro es el servicio (puede ser string, int o modelo)
            if ($servicioOrNull instanceof Servicio) {
                $servicio = $servicioOrNull;
            } else {
                $servicioId = $servicioOrNull ?? request()->route('servicio');
                $servicio = Servicio::findOrFail($servicioId);
            }
        } elseif ($clienteOrServicio instanceof Servicio) {
            // Ruta normal: servicios/{servicio}
            $servicio = $clienteOrServicio;
        } else {
            // Si no es modelo, intentar obtener desde la ruta
            $servicioId = $servicioOrNull ?? request()->route('servicio') ?? $clienteOrServicio;
            $servicio = Servicio::findOrFail($servicioId);

            // Intentar obtener cliente desde la ruta si existe
            $clienteParam = request()->route('cliente');
            if ($clienteParam) {
                $cliente = $clienteParam instanceof Cliente ? $clienteParam : Cliente::findOrFail($clienteParam);
            }
        }

        $this->authorize('update', $servicio);

        // ✅ Cargar cliente a través de ubicación
        $servicio->load(['ubicacion.cliente', 'ubicacion', 'router', 'plan', 'onu']);

        // ✅ Obtener cliente desde ubicación (si no se obtuvo desde la ruta)
        if (!$cliente) {
            $cliente = $servicio->ubicacion->cliente;
        }
        $ubicaciones = $cliente->ubicaciones()->with('router')->get();
        $nodos = Cache::remember('red.nodos.activos', 600, function () {
            return \App\Modules\Red\Models\Nodo::where('estado', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre']);
        });
        $routers = \App\Modules\Red\Models\Router::where('estado', true)->orderBy('nombre')->get();
        $planes = \App\Modules\Servicios\Models\Plan::where('estado', true)->orderBy('nombre')->get();
        $marcas = \App\Modules\Sistema\Models\OnuMarca::where('estado', true)
            ->with('modelosActivos')
            ->orderBy('orden')
            ->get();
        $modelos = \App\Modules\Servicios\Models\OnuModelo::where('estado', true)
            ->with('marca')
            ->orderBy('marca_id')
            ->orderBy('nombre')
            ->get();
        $fromCliente = request()->has('from_cliente') || request()->routeIs('clientes.servicios.*');
        $clienteId = $cliente->id ?? null;

        // Si es petición AJAX (drawer), devolver solo el contenido del formulario sin layout
        if (request()->ajax() || request()->wantsJson()) {
            // Renderizar solo la vista parcial del formulario (sin layout)
            // El drawer espera encontrar #form-servicio-container
            return response('<div id="form-servicio-container">' . view('servicios._form-edit', compact('servicio', 'cliente', 'clienteId', 'ubicaciones', 'nodos', 'routers', 'planes', 'marcas', 'modelos', 'fromCliente'))->render() . '</div>');
        }

        return view('servicios.edit', compact('servicio', 'cliente', 'clienteId', 'ubicaciones', 'nodos', 'routers', 'planes', 'marcas', 'modelos', 'fromCliente'));
    }

    public function update(UpdateServicioRequest $request, $clienteOrServicio = null, $servicioOrNull = null)
    {
        // Manejar diferentes casos de llamada:
        // 1. Ruta anidada: clientes/{cliente}/servicios/{servicio} -> update(Request $request, Cliente $cliente, $servicio)
        // 2. Ruta normal: servicios/{servicio} -> update(Request $request, Servicio $servicio)

        $servicio = null;
        $cliente = null;

        // Si el segundo parámetro existe y es Cliente, el primero es Request y el segundo es Cliente
        // Si el segundo parámetro es null o es un string/int, entonces el primer parámetro es el servicio
        if ($clienteOrServicio instanceof Cliente) {
            $cliente = $clienteOrServicio;
            // El tercer parámetro sería el servicio, pero update solo tiene 2 parámetros después de Request
            // Necesitamos obtenerlo desde la ruta
            $servicioId = $servicioOrNull ?? request()->route('servicio');
            $servicio = Servicio::findOrFail($servicioId);
        } elseif ($clienteOrServicio instanceof Servicio) {
            // Ruta normal: servicios/{servicio}
            $servicio = $clienteOrServicio;
        } else {
            // Si no es modelo, intentar obtener desde la ruta
            $servicioId = $servicioOrNull ?? request()->route('servicio') ?? $clienteOrServicio;
            $servicio = Servicio::findOrFail($servicioId);

            // Intentar obtener cliente desde la ruta si existe
            $clienteParam = request()->route('cliente');
            if ($clienteParam) {
                $cliente = $clienteParam instanceof Cliente ? $clienteParam : Cliente::findOrFail($clienteParam);
            }
        }

        $this->authorize('update', $servicio);

        try {
            $validated = $request->validated();

            // ✅ Cargar ubicación y cliente actuales
            $servicio->load('ubicacion.cliente');
            $clienteActual = $servicio->ubicacion->cliente;

            // Normalizar MAC address
            if (!empty($validated['mac_address'])) {
                $validated['mac_address'] = $this->normalizarMacAddress($validated['mac_address']);
            }

            // ✅ Procesar ubicación si se proporciona nueva dirección
            if (!empty($validated['ubicacion_direccion'])) {
                $ubicacion = $this->servicioService->obtenerOCrearUbicacion([
                    'direccion' => $validated['ubicacion_direccion'],
                    'referencia' => $validated['ubicacion_referencia'] ?? null,
                    'distrito' => $validated['ubicacion_distrito'] ?? null,
                    'provincia' => $validated['ubicacion_provincia'] ?? null,
                    'departamento' => $validated['ubicacion_departamento'] ?? null,
                ], $clienteActual->id);
                $validated['ubicacion_id'] = $ubicacion->id;
            }

            // ✅ Validar ubicacion_id si se cambia
            if (isset($validated['ubicacion_id']) && $validated['ubicacion_id'] !== $servicio->ubicacion_id) {
                $ubicacion = \App\Modules\Clientes\Models\Ubicacion::find($validated['ubicacion_id']);

                if (!$ubicacion || $ubicacion->cliente_id !== $clienteActual->id) {
                    throw new \Exception('La ubicación no pertenece al cliente del servicio');
                }
            }

            // Procesar credenciales provisionales
            $validated = $this->servicioService->procesarCredencialesProvisionales(
                $validated,
                $validated['onu_id'] ?? null
            );

            // Extraer datos de ubicación (notas y fotos) para no enviarlos al modelo Servicio
            $ubicacionNotas = $validated['ubicacion_notas'] ?? null;
            unset(
                $validated['ubicacion_notas'],
                $validated['ubicacion_foto_1'],
                $validated['ubicacion_foto_2'],
                $validated['ubicacion_foto_3']
            );

            $servicio->update($validated);

            // Actualizar notas y fotos de la ubicación del servicio
            $ubicacion = $servicio->ubicacion;
            if ($ubicacion) {
                if ($ubicacionNotas !== null) {
                    $ubicacion->update(['notas' => $ubicacionNotas]);
                }
                foreach (
                    ['ubicacion_foto_1' => 'foto_1', 'ubicacion_foto_2' => 'foto_2', 'ubicacion_foto_3' => 'foto_3']
                    as $requestKey => $dbKey
                ) {
                    if ($request->hasFile($requestKey)) {
                        if ($ubicacion->$dbKey) {
                            Storage::disk('public')->delete($ubicacion->$dbKey);
                        }
                        $path = $request->file($requestKey)->store(
                            'ubicaciones-fotos/' . $ubicacion->id,
                            'public'
                        );
                        $ubicacion->update([$dbKey => $path]);
                    }
                }
            }

            // Procesar datos de ONU si se proporcionan (usuario, password, etc.)
            $this->procesarDatosOnu($servicio, $request);

            // Asociar ONU si existe
            $this->servicioService->asociarOnuAServicio(
                $servicio,
                $validated['onu_id'] ?? null,
                $validated['mac_address'] ?? null
            );

            // Invalidar cache
            $this->servicioService->invalidarCache($servicio);

            // ✅ Obtener cliente desde ubicación
            $servicio->load('ubicacion.cliente');
            $cliente = $servicio->ubicacion->cliente;
            $fromCliente = $request->has('from_cliente');

            if ($fromCliente) {
                return redirect()
                    ->route('clientes.servicios.show', ['cliente' => $cliente->id, 'servicio' => $servicio])
                    ->with('success', 'Servicio actualizado correctamente.');
            }

            return redirect()
                ->route('servicios.show', $servicio)
                ->with('success', 'Servicio actualizado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar servicio: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el servicio: ' . $e->getMessage());
        }
    }

    /**
     * Procesar datos de ONU desde el formulario de edición
     * Si hay MAC address y se proporcionan datos de ONU, crear o actualizar la ONU
     */
    private function procesarDatosOnu(Servicio $servicio, Request $request): void
    {
        // Solo procesar si hay MAC address en el servicio
        if (!$servicio->mac_address) {
            return;
        }

        // Verificar si se proporcionaron datos de ONU
        $tieneDatosOnu = $request->has('onu_usuario') ||
            $request->has('onu_password') ||
            $request->has('onu_marca_id') ||
            $request->has('onu_modelo_id') ||
            $request->has('onu_serial_number_completo') ||
            $request->has('onu_mac_address') ||
            $request->has('onu_notas');

        if (!$tieneDatosOnu) {
            return;
        }

        // Buscar ONU existente por servicio o por MAC
        $onu = $servicio->onu;

        if (!$onu && $servicio->mac_address) {
            // Buscar ONU por MAC address
            $macNormalizada = $this->normalizarMacAddress($servicio->mac_address);
            $onu = \App\Modules\Servicios\Models\Onu::where('mac_address', $macNormalizada)->first();
        }

        // Preparar datos para crear/actualizar ONU
        $datosOnu = [];

        if ($request->has('onu_usuario')) {
            $datosOnu['usuario'] = $request->input('onu_usuario') ?: null;
        }

        if ($request->has('onu_password')) {
            $datosOnu['password'] = $request->input('onu_password') ?: null;
        }

        if ($request->has('onu_notas')) {
            $datosOnu['notas'] = $request->input('onu_notas') ?: null;
        }

        // Procesar marca y modelo
        if ($request->has('onu_marca_id') && $request->input('onu_marca_id')) {
            $marca = \App\Modules\Sistema\Models\OnuMarca::find($request->input('onu_marca_id'));
            if ($marca) {
                $datosOnu['marca'] = $marca->nombre;
            }
        }

        if ($request->has('onu_modelo_id') && $request->input('onu_modelo_id')) {
            $modelo = \App\Modules\Servicios\Models\OnuModelo::find($request->input('onu_modelo_id'));
            if ($modelo) {
                $datosOnu['modelo'] = $modelo->nombre;
            }
        }

        // Procesar serial numbers
        if ($request->has('onu_serial_number_completo')) {
            $datosOnu['serial_number_completo'] = $request->input('onu_serial_number_completo') ?: null;
        }

        if ($request->has('onu_serial_number_olt')) {
            $datosOnu['serial_number_olt'] = $request->input('onu_serial_number_olt') ?: null;
        }

        // Procesar MAC address de ONU
        if ($request->has('onu_mac_address') && $request->input('onu_mac_address')) {
            $datosOnu['mac_address'] = $this->normalizarMacAddress($request->input('onu_mac_address'));
        } elseif (!$onu && $servicio->mac_address) {
            // Si no hay ONU y no se proporciona MAC de ONU, usar la MAC del servicio
            $datosOnu['mac_address'] = $this->normalizarMacAddress($servicio->mac_address);
        }

        // Asignar ISP
        $datosOnu['isp_id'] = auth()->user()->isp_id ?? $servicio->isp_id;

        // Crear o actualizar ONU
        if ($onu) {
            // Actualizar ONU existente
            $onu->update($datosOnu);
            // Asegurar que esté asociada al servicio
            if ($onu->servicio_id !== $servicio->id) {
                $onu->update(['servicio_id' => $servicio->id]);
            }
            Log::info('ONU actualizada desde edición de servicio', [
                'servicio_id' => $servicio->id,
                'onu_id' => $onu->id
            ]);
        } elseif (!empty($datosOnu)) {
            // Crear nueva ONU solo si hay datos
            $datosOnu['servicio_id'] = $servicio->id;
            $onu = \App\Modules\Servicios\Models\Onu::create($datosOnu);
            Log::info('ONU creada desde edición de servicio', [
                'servicio_id' => $servicio->id,
                'onu_id' => $onu->id
            ]);
        }
    }

    public function destroy($clienteOrServicio = null, $servicioOrNull = null)
    {
        // Manejar diferentes casos de llamada:
        // 1. Ruta anidada: clientes/{cliente}/servicios/{servicio} -> destroy(Cliente $cliente, $servicio)
        // 2. Ruta normal: servicios/{servicio} -> destroy(Servicio $servicio)

        $servicio = null;
        $cliente = null;

        // Si el primer parámetro es una instancia de Cliente, es ruta anidada
        if ($clienteOrServicio instanceof Cliente) {
            $cliente = $clienteOrServicio;
            // El segundo parámetro es el servicio (puede ser string, int o modelo)
            if ($servicioOrNull instanceof Servicio) {
                $servicio = $servicioOrNull;
            } else {
                $servicioId = $servicioOrNull ?? request()->route('servicio');
                $servicio = Servicio::findOrFail($servicioId);
            }
        } elseif ($clienteOrServicio instanceof Servicio) {
            // Ruta normal: servicios/{servicio}
            $servicio = $clienteOrServicio;
        } else {
            // Si no es modelo, intentar obtener desde la ruta
            $servicioId = $servicioOrNull ?? request()->route('servicio') ?? $clienteOrServicio;
            $servicio = Servicio::findOrFail($servicioId);

            // Intentar obtener cliente desde la ruta si existe
            $clienteParam = request()->route('cliente');
            if ($clienteParam) {
                $cliente = $clienteParam instanceof Cliente ? $clienteParam : Cliente::findOrFail($clienteParam);
            }
        }

        $this->authorize('delete', $servicio);

        $validacion = $this->servicioService->puedeEliminar($servicio);

        if (!$validacion['puede_eliminar']) {
            $razones = implode(', ', $validacion['razones']);
            return back()
                ->with('error', "No se puede eliminar el servicio porque {$razones}.");
        }

        // ✅ Obtener cliente y ubicación antes de eliminar (si no se obtuvo desde la ruta)
        $servicio->load('ubicacion.cliente');
        if (!$cliente) {
            $cliente = $servicio->ubicacion->cliente;
        }

        // Guardar referencia a la ubicación y su ID antes de eliminar el servicio
        $ubicacionId = $servicio->ubicacion_id;
        $ubicacion = $servicio->ubicacion;

        // Eliminar el servicio
        $servicio->delete();

        // ✅ Verificar si la ubicación queda sin servicios y eliminarla si es así
        // Recargar la ubicación desde la base de datos para verificar servicios actualizados
        $ubicacion = \App\Modules\Clientes\Models\Ubicacion::find($ubicacionId);
        if ($ubicacion && $ubicacion->servicios()->count() === 0) {
            $ubicacion->delete();
            if (config('app.debug')) {
                Log::debug('Ubicación eliminada automáticamente al eliminar el último servicio', [
                    'ubicacion_id' => $ubicacionId,
                    'cliente_id' => $cliente->id
                ]);
            }
        }

        return redirect()
            ->route('clientes.show', $cliente)
            ->withFragment('content-servicios')
            ->with('success', 'Servicio eliminado correctamente.');
    }

    public function provisionales()
    {
        $this->authorize('viewAny', Servicio::class);
        // ✅ Cargar cliente a través de ubicación
        $servicios = Servicio::provisionales()
            ->with(['ubicacion.cliente', 'router', 'plan'])
            ->latest()
            ->paginate(15);

        return view('servicios.provisionales.index', compact('servicios'));
    }

    public function cambiarEstado(
        CambiarEstadoServicioRequest $request,
        Servicio $servicio,
        RouterOSScriptService $scriptService
    ) {
        $this->authorize('update', $servicio);
        $estadoAnterior = $servicio->estado;
        $nuevoEstado = $request->estado;

        // Cargar relaciones necesarias
        $servicio->load('router', 'ubicacion.cliente');
        $cliente = $servicio->ubicacion->cliente;
        $fromCliente = $request->has('cliente_id');

        // Manejar script y scheduler en MikroTik
        if ($servicio->router && $servicio->mac_address) {
            $callerId = $this->normalizarMacAddress($servicio->mac_address);

            if ($nuevoEstado === 'cortado' && $estadoAnterior !== 'cortado') {
                // Crear script y scheduler cuando se corta el servicio
                $scriptResult = $scriptService->createCorteScript($servicio->router, $callerId);

                if ($scriptResult['success']) {
                    $schedulerResult = $scriptService->createCorteScheduler(
                        $servicio->router,
                        $scriptResult['script_name']
                    );

                    if (!$schedulerResult['success']) {
                        Log::warning('Script creado pero scheduler falló', [
                            'servicio_id' => $servicio->id,
                            'script_result' => $scriptResult,
                            'scheduler_result' => $schedulerResult
                        ]);
                    }
                } else {
                    Log::warning('No se pudo crear script de corte en MikroTik', [
                        'servicio_id' => $servicio->id,
                        'router_id' => $servicio->router->id,
                        'caller_id' => $callerId,
                        'error' => $scriptResult['message']
                    ]);
                }
            } elseif ($nuevoEstado === 'activo' && $estadoAnterior === 'cortado') {
                // Eliminar script y scheduler cuando se reactiva el servicio
                $removeResult = $scriptService->removeCorteScriptAndScheduler(
                    $servicio->router,
                    $callerId
                );

                if (!$removeResult['success']) {
                    Log::warning('No se pudo eliminar script/scheduler de corte en MikroTik', [
                        'servicio_id' => $servicio->id,
                        'router_id' => $servicio->router->id,
                        'caller_id' => $callerId,
                        'error' => $removeResult['message']
                    ]);
                }
            }
        }

        // Actualizar estado del servicio
        $servicio->update(['estado' => $nuevoEstado]);
        $this->servicioService->invalidarCache($servicio);

        $mensaje = 'Estado del servicio actualizado correctamente.';
        if ($nuevoEstado === 'cortado' && $servicio->router && $servicio->mac_address) {
            $mensaje .= ' Script y scheduler de corte creados en MikroTik.';
        } elseif ($nuevoEstado === 'activo' && $estadoAnterior === 'cortado' && $servicio->router && $servicio->mac_address) {
            $mensaje .= ' Script y scheduler de corte eliminados de MikroTik.';
        }

        if ($fromCliente) {
            return redirect()
                ->route('clientes.show', ['cliente' => $cliente->id])
                ->with('active_tab', 'servicios')
                ->with('success', $mensaje);
        }

        return redirect()
            ->route('servicios.show', $servicio)
            ->with('success', $mensaje);
    }

    /**
     * Abre la interfaz web de la ONU del servicio (crea regla NAT)
     * Busca la IP del cliente por su MAC en las conexiones PPPoE activas
     * Si es formulario (no AJAX): redirige a la URL de la ONU en nueva pestaña
     */
    public function abrirInterfazOnu(
        Servicio $servicio,
        RouterOSPppoeService $pppoeService,
        RouterOSNatService $natService
    ) {
        $this->authorize('view', $servicio);
        $servicio->load('router');

        if (!$servicio->router) {
            return $this->abrirInterfazOnuError('El servicio no tiene router asignado.');
        }

        if (empty($servicio->mac_address)) {
            return $this->abrirInterfazOnuError('El servicio no tiene dirección MAC registrada.');
        }

        $internalIp = $pppoeService->getIpByMacAddress($servicio->router, $servicio->mac_address);

        if (!$internalIp) {
            return $this->abrirInterfazOnuError('El cliente no tiene una sesión PPPoE activa. La ONU debe estar conectada.');
        }

        try {
            $internalPort = 443;
            $router = $servicio->router;
            $externalPort = $natService->getAvailablePort($router);

            $horaEliminacion = now()->setTimezone(config('app.timezone', 'America/Lima'))->addMinutes(5)->format('H:i:s');
            $comment = "ONU-{$internalIp}-{$externalPort} (Se elimina: {$horaEliminacion})";

            $natService->createDstNatRule(
                $router,
                (string) $externalPort,
                $internalIp,
                $internalPort,
                $comment
            );

            $host = preg_replace('#^https?://#', '', trim($router->ip_url));
            $url = "https://{$host}:{$externalPort}";

            // Formulario con target="_blank": redirigir (no se bloquea como popup)
            if (!request()->wantsJson()) {
                return redirect()->away($url);
            }

            return response()->json([
                'success' => true,
                'message' => 'Regla NAT creada correctamente',
                'url' => $url,
                'port' => $externalPort,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear NAT para ONU desde servicio', [
                'servicio_id' => $servicio->id,
                'error' => $e->getMessage(),
            ]);
            return $this->abrirInterfazOnuError('Error al crear regla NAT: ' . $e->getMessage());
        }
    }

    /**
     * GET: Crea NAT y redirige a la interfaz ONU (para enlace directo target="_blank")
     */
    public function abrirOnuRedirect(
        Servicio $servicio,
        RouterOSPppoeService $pppoeService,
        RouterOSNatService $natService
    ) {
        $this->authorize('view', $servicio);
        $servicio->load('router');
        if (!$servicio->router || empty($servicio->mac_address)) {
            return redirect()->back()->with('error', 'Servicio sin router o MAC')->with('active_tab', 'servicios');
        }
        $internalIp = $pppoeService->getIpByMacAddress($servicio->router, $servicio->mac_address);
        if (!$internalIp) {
            return redirect()->back()->with('error', 'No hay sesión PPPoE activa. La ONU debe estar conectada.')->with('active_tab', 'servicios');
        }
        try {
            $router = $servicio->router;
            $externalPort = $natService->getAvailablePort($router);
            $horaEliminacion = now()->setTimezone(config('app.timezone', 'America/Lima'))->addMinutes(5)->format('H:i:s');
            $comment = "ONU-{$internalIp}-{$externalPort} (Se elimina: {$horaEliminacion})";
            $natService->createDstNatRule($router, (string) $externalPort, $internalIp, 443, $comment);
            $host = preg_replace('#^https?://#', '', trim($router->ip_url));
            return redirect()->away("https://{$host}:{$externalPort}");
        } catch (\Exception $e) {
            Log::error('Error al crear NAT para ONU', ['servicio_id' => $servicio->id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->with('active_tab', 'servicios');
        }
    }

    /**
     * Obtiene router_id e IP del servicio (para usar con abrirInterfazOnu como en routers)
     */
    public function getIpPppoe(Servicio $servicio, RouterOSPppoeService $pppoeService)
    {
        $this->authorize('view', $servicio);
        $servicio->load('router');
        if (!$servicio->router || empty($servicio->mac_address)) {
            return response()->json(['success' => false, 'message' => 'Servicio sin router o MAC'], 404);
        }
        $ip = $pppoeService->getIpByMacAddress($servicio->router, $servicio->mac_address);
        if (!$ip) {
            return response()->json(['success' => false, 'message' => 'No hay sesión PPPoE activa'], 404);
        }
        return response()->json([
            'success' => true,
            'router_id' => $servicio->router->id,
            'ip' => $ip,
        ]);
    }

    private function abrirInterfazOnuError(string $message)
    {
        if (request()->wantsJson()) {
            return response()->json(['success' => false, 'message' => $message], 400);
        }
        return back()->with('error', $message)->with('active_tab', 'servicios');
    }

    public function getRoutersByNodo(Request $request)
    {
        $this->authorize('viewAny', Servicio::class);
        $nodoId = $request->input('nodo_id');

        if (!$nodoId) {
            return response()->json([
                'success' => false,
                'message' => 'nodo_id es requerido'
            ], 400);
        }

        $routers = \App\Modules\Red\Models\Router::where('nodo_id', $nodoId)
            ->where('estado', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'ip_url']);

        return response()->json([
            'success' => true,
            'routers' => $routers
        ]);
    }

    public function getPlanesByRouter(Request $request)
    {
        $this->authorize('viewAny', Servicio::class);
        $routerId = $request->input('router_id');

        if (!$routerId) {
            return response()->json([
                'success' => false,
                'message' => 'router_id es requerido'
            ], 400);
        }

        $planes = \App\Modules\Servicios\Models\Plan::where('router_id', $routerId)
            ->where('estado', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'precio_mensual', 'velocidad_bajada_mbps', 'velocidad_subida_mbps']);

        return response()->json([
            'success' => true,
            'planes' => $planes
        ]);
    }

    /**
     * API: Pools de IP del router (para remote-address en PPPoE)
     */
    public function getIpPoolsByRouter(Request $request, RouterOSPppoeService $pppoeService)
    {
        $this->authorize('viewAny', Servicio::class);
        $routerId = $request->input('router_id');

        if (!$routerId) {
            return response()->json([
                'success' => false,
                'message' => 'router_id es requerido'
            ], 400);
        }

        $router = \App\Modules\Red\Models\Router::find($routerId);
        if (!$router) {
            return response()->json([
                'success' => false,
                'message' => 'Router no encontrado'
            ], 404);
        }

        try {
            $pools = $pppoeService->getIpPoolsAvailable($router);
            return response()->json([
                'success' => true,
                'pools' => $pools
            ]);
        } catch (\Exception $e) {
            Log::warning('Error al obtener IP pools', [
                'router_id' => $routerId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => true,
                'pools' => []
            ]);
        }
    }

    /**
     * API: Lista de IPs libres de un pool (para elegir una concreta)
     */
    public function getIpLibres(Request $request, RouterOSPppoeService $pppoeService)
    {
        $this->authorize('viewAny', Servicio::class);
        $routerId = $request->input('router_id');
        $pool = $request->input('pool');

        if (!$routerId || !$pool) {
            return response()->json(['success' => false, 'message' => 'router_id y pool son requeridos'], 400);
        }

        $router = \App\Modules\Red\Models\Router::find($routerId);
        if (!$router) {
            return response()->json(['success' => false, 'message' => 'Router no encontrado'], 404);
        }

        try {
            $ips = $pppoeService->getFreeIpsForPool($router, $pool, 300);
            return response()->json(['success' => true, 'ips' => $ips]);
        } catch (\Exception $e) {
            Log::warning('Error al obtener IPs libres', ['router_id' => $routerId, 'pool' => $pool, 'error' => $e->getMessage()]);
            return response()->json(['success' => true, 'ips' => []]);
        }
    }

    /**
     * API: Sugerir una IP libre (del pool indicado o del primer pool con libres)
     */
    public function getSugerirIpLibre(Request $request, RouterOSPppoeService $pppoeService)
    {
        $this->authorize('viewAny', Servicio::class);
        $routerId = $request->input('router_id');
        $pool = $request->input('pool');

        if (!$routerId) {
            return response()->json(['success' => false, 'message' => 'router_id es requerido'], 400);
        }

        $router = \App\Modules\Red\Models\Router::find($routerId);
        if (!$router) {
            return response()->json(['success' => false, 'message' => 'Router no encontrado'], 404);
        }

        try {
            $ip = $pppoeService->getSuggestedFreeIp($router, $pool ?: null);
            return response()->json(['success' => (bool) $ip, 'ip' => $ip]);
        } catch (\Exception $e) {
            Log::warning('Error al sugerir IP libre', ['router_id' => $routerId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'ip' => null]);
        }
    }

    public function buscarEquipoExistente(Request $request, \App\Modules\Red\Services\RouterOSPppoeService $pppoeService)
    {
        $this->authorize('viewAny', Servicio::class);
        $request->validate([
            'mac' => ['nullable', 'string', 'max:50'],
            'serial' => ['nullable', 'string', 'max:50'],
            'dni' => ['nullable', 'string', 'max:15'],
            'router_id' => ['nullable', 'integer'],
        ]);
        if ($request->filled('router_id') && ! \App\Modules\Red\Models\Router::where('id', $request->router_id)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['router_id' => [__('validation.exists', ['attribute' => 'router'])]]);
        }

        $mac = $request->input('mac');
        $serial = $request->input('serial');
        $dni = $request->input('dni');
        $routerId = $request->input('router_id');

        $resultados = [];

        // Búsqueda por MAC
        if ($mac) {
            $macNormalizada = $this->normalizarMacAddress($mac);
            // ✅ Cargar cliente a través de ubicación
            $servicio = Servicio::with(['ubicacion.cliente', 'router'])->where('mac_address', $macNormalizada)->first();
            if ($servicio) {
                $router = $servicio->router;
                $nodoId = $router ? $router->nodo_id : null;

                $resultados[] = [
                    'tipo' => 'servicio',
                    'id' => $servicio->id,
                    'servicio_id' => $servicio->id,
                    'mac_address' => $servicio->mac_address,
                    'usuario_pppoe' => $servicio->usuario_pppoe,
                    'password_pppoe' => $servicio->password_pppoe,
                    'tipo_pppoe' => $servicio->tipo_pppoe,
                    'cliente' => $servicio->ubicacion->cliente->nombre ?? null,
                    'dni' => $servicio->ubicacion->cliente->documento ?? null,
                    'router_id' => $servicio->router_id,
                    'nodo_id' => $nodoId,
                    'plan_id' => $servicio->plan_id,
                    'onu_id' => $servicio->onu_id,
                ];
            }

            $onu = \App\Modules\Servicios\Models\Onu::where('mac_address', $macNormalizada)->first();
            if ($onu) {
                $resultados[] = [
                    'tipo' => 'onu',
                    'id' => $onu->id,
                    'mac_address' => $onu->mac_address,
                    'serial_number' => $onu->serial_number,
                    'serial_number_completo' => $onu->serial_number_completo,
                    'serial_number_olt' => $onu->serial_number_olt,
                    'marca' => $onu->marca,
                    'modelo' => $onu->modelo,
                ];
            }
        }

        // Búsqueda por serial
        if ($serial) {
            $onu = \App\Modules\Servicios\Models\Onu::where('serial_number', $serial)
                ->orWhere('serial_number_completo', $serial)
                ->orWhere('serial_number_olt', $serial)
                ->first();
            if ($onu) {
                $resultados[] = [
                    'tipo' => 'onu',
                    'id' => $onu->id,
                    'mac_address' => $onu->mac_address,
                    'serial_number' => $onu->serial_number,
                    'serial_number_completo' => $onu->serial_number_completo,
                    'serial_number_olt' => $onu->serial_number_olt,
                    'marca' => $onu->marca,
                    'modelo' => $onu->modelo,
                ];
            }
        }

        // Búsqueda por DNI
        if ($dni && $routerId) {
            // Limpiar DNI (solo números)
            $dni = preg_replace('/\D/', '', $dni);

            // 1. Buscar en servicios de la base de datos
            $cliente = \App\Modules\Clientes\Models\Cliente::where('documento', $dni)
                ->where('tipo_documento', 'dni')
                ->first();

            if ($cliente) {
                $servicios = Servicio::with(['ubicacion.cliente', 'router', 'plan'])
                    ->whereHas('ubicacion', function ($q) use ($cliente) {
                        $q->where('cliente_id', $cliente->id);
                    })
                    ->where('router_id', $routerId)
                    ->where('estado', 'activo')
                    ->get();

                foreach ($servicios as $servicio) {
                    // Obtener nodo_id del router
                    $router = $servicio->router;
                    $nodoId = $router ? $router->nodo_id : null;

                    $resultados[] = [
                        'tipo' => 'servicio',
                        'id' => $servicio->id,
                        'servicio_id' => $servicio->id,
                        'mac_address' => $servicio->mac_address,
                        'usuario_pppoe' => $servicio->usuario_pppoe,
                        'password_pppoe' => $servicio->password_pppoe,
                        'tipo_pppoe' => $servicio->tipo_pppoe,
                        'cliente' => $servicio->ubicacion->cliente->nombre ?? null,
                        'dni' => $dni,
                        'router_id' => $servicio->router_id,
                        'nodo_id' => $nodoId,
                        'plan_id' => $servicio->plan_id,
                        'onu_id' => $servicio->onu_id,
                    ];
                }
            }

            // 2. Buscar en RouterOS conexiones activas
            try {
                $router = \App\Modules\Red\Models\Router::find($routerId);
                if ($router) {
                    $conexiones = $pppoeService->getActiveConnections($router);

                    // Buscar conexiones que contengan el DNI en el nombre de usuario
                    // Formatos posibles: "42809186_01", "042809186_01", "42809186", etc.
                    $dniVariantes = [
                        $dni,
                        str_pad($dni, 8, '0', STR_PAD_LEFT),
                        $dni . '_01',
                        str_pad($dni, 8, '0', STR_PAD_LEFT) . '_01',
                        '0' . $dni,
                        '0' . str_pad($dni, 8, '0', STR_PAD_LEFT),
                    ];

                    foreach ($conexiones as $conexion) {
                        $usuario = $conexion['name'] ?? '';
                        $callerId = $conexion['caller-id'] ?? '';

                        // Verificar si el usuario contiene alguna variante del DNI
                        foreach ($dniVariantes as $dniVariante) {
                            if (strpos($usuario, $dniVariante) !== false) {
                                // Obtener el password del secret PPPoE
                                $password = null;
                                try {
                                    $password = $pppoeService->getSecretPassword($router, $usuario);
                                } catch (\Exception $e) {
                                    Log::warning('No se pudo obtener password del secret PPPoE: ' . $e->getMessage());
                                }

                                $resultados[] = [
                                    'tipo' => 'routeros',
                                    'usuario_pppoe' => $usuario,
                                    'password_pppoe' => $password,
                                    'caller_id' => $callerId,
                                    'ip_address' => $conexion['address'] ?? null,
                                    'profile' => $conexion['profile'] ?? null,
                                    'uptime' => $conexion['uptime'] ?? null,
                                    'dni' => $dni,
                                    'router_id' => $routerId,
                                ];
                                break; // Solo agregar una vez por conexión
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Error al buscar en RouterOS por DNI: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'resultados' => $resultados
        ]);
    }

    public function getOnuByServicio(Servicio $servicio)
    {
        $this->authorize('view', $servicio);
        $onu = $servicio->onu;

        if (!$onu) {
            return response()->json([
                'success' => false,
                'message' => 'El servicio no tiene ONU asociada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'onu' => [
                'id' => $onu->id,
                'serial_number' => $onu->serial_number,
                'serial_number_completo' => $onu->serial_number_completo,
                'serial_number_olt' => $onu->serial_number_olt,
                'mac_address' => $onu->mac_address,
                'marca' => $onu->marca,
                'modelo' => $onu->modelo,
                'usuario' => $onu->usuario,
                'password' => $onu->password,
            ]
        ]);
    }

    public function getServicioById(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'success' => false,
                'message' => 'ID inválido'
            ], 400);
        }

        try {
            $servicio = Servicio::with('router')->findOrFail($id);
            $this->authorize('view', $servicio);

            // Obtener nodo_id del router
            $router = $servicio->router;
            $nodoId = $router ? $router->nodo_id : null;

            return response()->json([
                'success' => true,
                'servicio' => [
                    'id' => $servicio->id,
                    'mac_address' => $servicio->mac_address,
                    'usuario_pppoe' => $servicio->usuario_pppoe,
                    'password_pppoe' => $servicio->password_pppoe,
                    'tipo_pppoe' => $servicio->tipo_pppoe,
                    'router_id' => $servicio->router_id,
                    'nodo_id' => $nodoId,
                    'plan_id' => $servicio->plan_id,
                    'onu_id' => $servicio->onu_id,
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio no encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener servicio: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el servicio'
            ], 500);
        }
    }
}
