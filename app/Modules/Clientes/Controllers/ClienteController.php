<?php

namespace App\Modules\Clientes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Clientes\Requests\StoreClienteRequest;
use App\Modules\Clientes\Requests\UpdateClienteRequest;
use App\Modules\Clientes\Models\Cliente;
use App\Core\Services\DniService;
use App\Core\Services\RucService;
use App\Core\Services\TenantConnectionService;
use App\Modules\Clientes\Services\ClienteService;
use App\Core\Traits\RespondsWithJson;
use App\Core\Traits\NormalizesMacAddress;
use App\Modules\Red\Services\RouterOSScriptService;
use App\Modules\Red\Services\RouterOSPppoeService;
use App\Modules\Red\Models\Router;
use App\Modules\Servicios\Models\Plan;
use App\Modules\Clientes\Models\Ubicacion;
use App\Modules\Servicios\Models\Servicio;
use App\Modules\Comprobantes\Models\Recibo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    use RespondsWithJson, NormalizesMacAddress;

    public function __construct(
        private ClienteService $clienteService,
        private DniService $dniService,
        private RucService $rucService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Cliente::class);

        $routers = \App\Modules\Red\Models\Router::where('estado', true)->orderBy('nombre')->get();
        $routerId = $request->input('router_id');

        $query = Cliente::query();

        if (empty($routerId)) {
            $clientes = collect();
            return view('clientes.index', compact('clientes', 'routers', 'routerId'));
        }

        $query->whereHas('ubicaciones', function ($q) use ($routerId) {
            $q->where('router_id', $routerId);
        });

        // Búsqueda usando el trait Searchable
        if ($request->filled('buscar')) {
            $query->search($request->buscar, ['nombre', 'documento', 'telefonos']);
        }

        $clientes = $query->with(['ubicaciones' => function ($q) {
            $q->withCount('servicios');
        }])
            ->withCount([
                'ubicaciones',
                'servicios',
                'servicios as servicios_activos' => function ($q) {
                    $q->where('servicios.estado', 'activo');
                },
                'recibos as tiene_recibos_pendientes' => function ($q) {
                    $q->where(function ($query) {
                        $query->whereIn('estado', ['pendiente', 'vencido'])
                            ->where('saldo', '>', 0);
                    });
                },
                'recibos as tiene_recibos_vencidos' => function ($q) {
                    $q->where('estado', 'vencido')
                        ->where('saldo', '>', 0);
                }
            ])
            ->latest()
            ->paginate(20);

        return view('clientes.index', compact('clientes', 'routers', 'routerId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Cliente::class);

        return view('clientes.create');
    }

    public function importarPppoeForm(Request $request, RouterOSPppoeService $pppoeService)
    {
        $this->authorize('create', Cliente::class);

        $routers = Router::where('estado', true)->orderBy('nombre')->get();
        $routerId = $request->input('router_id');
        $secrets = collect();

        if ($routerId) {
            $router = Router::findOrFail($routerId);
            $conexionesRaw = $pppoeService->getActiveConnections($router);
            $secretsRaw = $pppoeService->getSecrets($router);
            $planes = Plan::where('router_id', $router->id)->get()->keyBy('perfil_mikrotik');
            $planesPorNombre = Plan::where('router_id', $router->id)->get()->keyBy('nombre');
            $secretsByName = collect($secretsRaw)->keyBy(function ($secret) {
                return trim((string) ($secret['name'] ?? ''));
            });

            $serviciosExistentes = Servicio::where('router_id', $router->id)
                ->whereNotNull('usuario_pppoe')
                ->get(['usuario_pppoe', 'mac_address'])
                ->map(function ($servicio) {
                    $usuario = mb_strtolower(trim((string) $servicio->usuario_pppoe));
                    $mac = $servicio->mac_address ? mb_strtolower($this->normalizarMacAddress($servicio->mac_address)) : '';
                    return $usuario . '|' . $mac;
                })
                ->unique()
                ->toArray();
            $serviciosExistentes = array_flip($serviciosExistentes);

            $secrets = collect($conexionesRaw)
                ->map(function ($conexion) use ($planes, $planesPorNombre, $serviciosExistentes, $secretsByName) {
                    $name = trim((string) ($conexion['name'] ?? ''));
                    if ($name === '') {
                        return null;
                    }

                    $callerIdRaw = trim((string) (isset($conexion['caller-id']) ? $conexion['caller-id'] : ''));
                    $callerId = $callerIdRaw !== '' ? $this->normalizarMacAddress($callerIdRaw) : null;
                    
                    // Generar key en minúsculas para consistencia con importarPppoe
                    $key = mb_strtolower($name) . '|' . mb_strtolower($callerId ? $callerId : '');

                    $secret = $secretsByName->get($name);
                    if (!$secret) {
                        // Si no hay secret, no se puede importar
                        return null;
                    }
                    $profile = isset($secret['profile']) ? $secret['profile'] : null;
                    $plan = $profile ? (isset($planes[$profile]) ? $planes[$profile] : null) : null;
                    if (!$plan && $profile) {
                        $plan = isset($planesPorNombre[$profile]) ? $planesPorNombre[$profile] : null;
                    }
                    $disabledValue = strtolower((string) (isset($secret['disabled']) ? $secret['disabled'] : 'false'));
                    $disabled = in_array($disabledValue, ['true', 'yes', '1'], true);
                    $exists = isset($serviciosExistentes[$key]);
                    
                    // Log de depuración para cada secret procesado
                    Log::debug('Secret procesado en importarPppoeForm', [
                        'name' => $name,
                        'key' => $key,
                        'caller_id' => $callerId,
                        'profile' => $profile,
                        'plan_id' => $plan ? $plan->id : null,
                        'plan_nombre' => $plan ? $plan->nombre : null,
                        'disabled' => $disabled,
                        'exists' => $exists,
                        'habilitado_para_importar' => !$disabled && !$exists && $plan && $callerId
                    ]);
                    
                    $planId = $plan ? $plan->id : null;
                    $planNombre = $plan ? $plan->nombre : null;
                    
                    // Log detallado para diagnóstico
                    Log::debug('Secret procesado - valores finales', [
                        'name' => $name,
                        'key' => $key,
                        'caller_id' => $callerId,
                        'profile' => $profile,
                        'plan_id' => $planId,
                        'plan_nombre' => $planNombre,
                        'plan_object' => $plan ? 'exists' : 'null',
                        'disabled' => $disabled,
                        'exists' => $exists,
                        'habilitado' => !$disabled && !$exists && $planId && $callerId
                    ]);
                    
                    return [
                        'key' => $key,
                        'name' => $name,
                        'profile' => $profile,
                        'plan_id' => $planId,
                        'plan_nombre' => $planNombre,
                        'disabled' => $disabled,
                        'exists' => $exists,
                        'comment' => isset($secret['comment']) ? $secret['comment'] : null,
                        'address' => isset($conexion['address']) ? $conexion['address'] : null,
                        'caller_id' => $callerId,
                        'uptime' => isset($conexion['uptime']) ? $conexion['uptime'] : null,
                    ];
                })
                ->filter()
                ->unique('key')
                ->values();
        }

        return view('clientes.pppoe-import', compact('routers', 'routerId', 'secrets'));
    }

    public function importarPppoe(Request $request, RouterOSPppoeService $pppoeService)
    {
        $this->authorize('create', Cliente::class);

        // Log de depuración
        Log::info('=== INICIO IMPORTACIÓN PPPoE ===', [
            'request_all' => $request->all(),
            'usuarios_raw' => $request->input('usuarios'),
            'router_id' => $request->input('router_id'),
        ]);

        $tenantConn = TenantConnectionService::currentTenantConnectionName();
        $data = $request->validate([
            'router_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) use ($tenantConn): void {
                    if (! $tenantConn) {
                        $fail(__('validation.exists', ['attribute' => $attribute]));
                        return;
                    }
                    if (! DB::connection($tenantConn)->table('routers')->where('id', (int) $value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => $attribute]));
                    }
                },
            ],
            'usuarios' => ['nullable', 'array'],
            'usuarios.*' => ['string', 'max:255'],
        ]);

        Log::info('Datos validados', [
            'router_id' => $data['router_id'],
            'usuarios_count' => count($data['usuarios'] ?? []),
            'usuarios' => $data['usuarios'] ?? [],
        ]);

        $router = Router::findOrFail($data['router_id']);
        $ispId = $router->isp_id ?? (Auth::user()?->isp_id ?? session('current_isp_id'));
        $secrets = collect($pppoeService->getSecrets($router))->keyBy(function ($secret) {
            return trim((string) ($secret['name'] ?? ''));
        });
        $planes = Plan::where('router_id', $router->id)->get()->keyBy('perfil_mikrotik');
        $planesPorNombre = Plan::where('router_id', $router->id)->get()->keyBy('nombre');
        $conexiones = collect($pppoeService->getActiveConnections($router))
            ->map(function ($conexion) {
                $name = trim((string) ($conexion['name'] ?? ''));
                $callerIdRaw = trim((string) ($conexion['caller-id'] ?? ''));
                $callerId = $callerIdRaw !== '' ? $this->normalizarMacAddress($callerIdRaw) : null;
                $key = mb_strtolower($name) . '|' . mb_strtolower($callerId ?? '');
                return [
                    'key' => $key,
                    'name' => $name,
                    'caller_id' => $callerId,
                ];
            })
            ->filter(fn ($c) => !empty($c['name']))
            ->keyBy('key');

        $serviciosExistentes = Servicio::where('router_id', $router->id)
            ->whereNotNull('usuario_pppoe')
            ->get(['usuario_pppoe', 'mac_address'])
            ->map(function ($servicio) {
                $usuario = mb_strtolower(trim((string) $servicio->usuario_pppoe));
                $mac = $servicio->mac_address ? mb_strtolower($this->normalizarMacAddress($servicio->mac_address)) : '';
                return $usuario . '|' . $mac;
            })
            ->unique()
            ->toArray();
        $serviciosExistentes = array_flip($serviciosExistentes);

        $seleccionados = collect($data['usuarios'] ?? []);
        
        Log::info('Seleccionados después de validación', [
            'count' => $seleccionados->count(),
            'items' => $seleccionados->toArray(),
        ]);
        
        if ($seleccionados->isEmpty()) {
            Log::warning('No se seleccionaron usuarios para importar', [
                'router_id' => $router->id,
                'request_data' => $request->all(),
            ]);
            return redirect()
                ->route('clientes.pppoe.importar', ['router_id' => $router->id])
                ->with('error', 'Selecciona al menos un usuario PPPoE para importar.');
        }

        $resultado = [
            'importados' => 0,
            'omitidos' => 0,
            'errores' => 0,
        ];

        $documentoCounter = $this->getNextDocumentoPlaceholder();

        DB::beginTransaction();
        try {
            foreach ($seleccionados as $seleccion) {
                $seleccion = trim((string) $seleccion);
                if ($seleccion === '') {
                    $resultado['omitidos']++;
                    continue;
                }

                $parts = explode('|', $seleccion, 2);
                $usuario = trim((string) (isset($parts[0]) ? $parts[0] : ''));
                $callerId = trim((string) (isset($parts[1]) ? $parts[1] : ''));
                $callerId = $callerId !== '' ? $this->normalizarMacAddress($callerId) : null;
                $key = mb_strtolower($usuario) . '|' . mb_strtolower($callerId ? $callerId : '');
                
                Log::debug('Procesando selección en importarPppoe', [
                    'seleccion_original' => $seleccion,
                    'usuario' => $usuario,
                    'caller_id' => $callerId,
                    'key' => $key
                ]);

                if ($usuario === '' || !$callerId) {
                    $resultado['omitidos']++;
                    continue;
                }

                if ($conexiones->isNotEmpty() && !$conexiones->has($key)) {
                    $resultado['omitidos']++;
                    continue;
                }

                if (isset($serviciosExistentes[$key])) {
                    $resultado['omitidos']++;
                    continue;
                }

                $secret = $secrets->get($usuario);
                if (!$secret) {
                    $resultado['omitidos']++;
                    continue;
                }

                $disabledValue = strtolower((string) ($secret['disabled'] ?? 'false'));
                if (in_array($disabledValue, ['true', 'yes', '1'], true)) {
                    $resultado['omitidos']++;
                    continue;
                }

                $profile = $secret['profile'] ?? null;
                $plan = $profile ? ($planes[$profile] ?? null) : null;
                if (!$plan && $profile) {
                    $plan = $planesPorNombre[$profile] ?? null;
                }
                if (!$plan) {
                    $resultado['omitidos']++;
                    continue;
                }

            $dni = $this->extraerDniDesdeUsuario($usuario);
            $tipoPppoe = $dni ? 'usuario_unico' : 'usuario_compartido';
            $cliente = null;
            $nombreCliente = 'PPPoE ' . $usuario;
            $dniInfo = null;

            if ($dni) {
                $cliente = Cliente::where('documento', $dni)
                    ->where('tipo_documento', 'dni')
                    ->first();

                if (!$cliente) {
                    $dniInfo = $this->dniService->consultar($dni);
                    if (!empty($dniInfo['nombre'])) {
                        $nombreCliente = $dniInfo['nombre'];
                    }

                    $cliente = Cliente::create([
                        'nombre' => $nombreCliente,
                        'tipo_documento' => 'dni',
                        'documento' => $dni,
                        'dni_nombres' => $dniInfo['nombres'] ?? null,
                        'dni_apellido_paterno' => $dniInfo['apellido_paterno'] ?? null,
                        'dni_apellido_materno' => $dniInfo['apellido_materno'] ?? null,
                        'fuente_info' => $dniInfo['fuente'] ?? 'import_pppoe',
                        'notas' => 'Importado desde PPPoE (' . $router->nombre . ')',
                        'isp_id' => $ispId,
                    ]);
                } elseif ($ispId && empty($cliente->isp_id)) {
                    $cliente->update(['isp_id' => $ispId]);
                }
            }

            if (!$cliente) {
                $documento = str_pad((string) $documentoCounter, 8, '0', STR_PAD_LEFT);
                $documentoCounter++;

                $cliente = Cliente::create([
                    'nombre' => $nombreCliente,
                    'tipo_documento' => 'dni',
                    'documento' => $documento,
                    'fuente_info' => 'import_pppoe',
                    'notas' => 'Importado desde PPPoE (' . $router->nombre . ')',
                    'isp_id' => $ispId,
                ]);
            }

            $ubicacion = Ubicacion::where('cliente_id', $cliente->id)
                ->where('router_id', $router->id)
                ->first();

            if (!$ubicacion) {
                $ubicacion = Ubicacion::create([
                    'cliente_id' => $cliente->id,
                    'router_id' => $router->id,
                    'direccion' => 'Pendiente',
                    'referencia' => 'Importado desde PPPoE',
                    'isp_id' => $ispId,
                ]);
            }

                $servicio = Servicio::create([
                    'ubicacion_id' => $ubicacion->id,
                    'router_id' => $router->id,
                    'plan_id' => $plan->id,
                    'tipo_pppoe' => $tipoPppoe,
                    'usuario_pppoe' => $usuario,
                    'password_pppoe' => $secret['password'] ?? null,
                    'mac_address' => $callerId,
                    'estado' => 'activo',
                    'es_provisional' => true,
                    'fecha_instalacion' => now(),
                    'isp_id' => $ispId,
                ]);

                // Crear ONU si hay MAC address (caller-id)
                if ($callerId) {
                    // Normalizar MAC address para búsqueda
                    $macNormalizada = $this->normalizarMacAddress($callerId);
                    
                    // Verificar si ya existe una ONU con esta MAC
                    $onuExistente = \App\Modules\Servicios\Models\Onu::where('mac_address', $macNormalizada)->first();
                    
                    if (!$onuExistente) {
                        // Crear nueva ONU con los datos básicos
                        // Usar MAC address como serial_number temporal (se puede actualizar después)
                        $serialTemporal = str_replace(':', '', $macNormalizada);
                        
                        \App\Modules\Servicios\Models\Onu::create([
                            'servicio_id' => $servicio->id,
                            'mac_address' => $macNormalizada,
                            'serial_number' => $serialTemporal, // Requerido - usar MAC como temporal
                            'serial_number_completo' => null, // Se puede completar después
                            'serial_number_olt' => null, // Se puede completar después
                            'usuario' => null, // Se puede completar después desde edición
                            'password' => null, // Se puede completar después desde edición
                            'marca' => null,
                            'modelo' => null,
                            'notas' => 'Importado desde PPPoE (' . $router->nombre . ')',
                            'isp_id' => $ispId,
                        ]);
                        
                        Log::info('ONU creada durante importación masiva PPPoE', [
                            'servicio_id' => $servicio->id,
                            'mac_address' => $macNormalizada,
                            'router' => $router->nombre
                        ]);
                    } else {
                        // Si existe, asociarla al servicio
                        $onuExistente->update(['servicio_id' => $servicio->id]);
                        
                        Log::info('ONU existente asociada durante importación masiva PPPoE', [
                            'servicio_id' => $servicio->id,
                            'onu_id' => $onuExistente->id,
                            'mac_address' => $macNormalizada
                        ]);
                    }
                }

                $resultado['importados']++;
                
                Log::info('Usuario importado exitosamente', [
                    'usuario' => $usuario,
                    'caller_id' => $callerId,
                    'cliente_id' => $cliente->id,
                    'servicio_id' => $servicio->id
                ]);
            }

            DB::commit();
            
            Log::info('=== FIN IMPORTACIÓN PPPoE ===', [
                'router_id' => $router->id,
                'resultado' => $resultado
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al importar clientes PPPoE', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $resultado['errores']++;
            
            return redirect()
                ->route('clientes.pppoe.importar', ['router_id' => $router->id])
                ->with('error', 'Error al importar: ' . $e->getMessage());
        }

        $mensaje = "Importados: {$resultado['importados']}, Omitidos: {$resultado['omitidos']}, Errores: {$resultado['errores']}";
        
        if ($resultado['importados'] === 0) {
            return redirect()
                ->route('clientes.pppoe.importar', ['router_id' => $router->id])
                ->with('warning', $mensaje . '. Revisa los logs para más detalles.');
        }
        
        return redirect()
            ->route('clientes.pppoe.importar', ['router_id' => $router->id])
            ->with('success', $mensaje);
    }

    private function getNextDocumentoPlaceholder(): int
    {
        $ultimo = Cliente::where('documento', 'like', '99%')
            ->orderBy('documento', 'desc')
            ->value('documento');

        $ultimoNumero = $ultimo ? (int) $ultimo : 99000000;
        $next = $ultimoNumero + 1;

        if ($next > 99999999) {
            $next = 99000001;
        }

        return $next;
    }

    private function extraerDniDesdeUsuario(string $usuario): ?string
    {
        $usuario = trim($usuario);
        if ($usuario === '') {
            return null;
        }

        if (preg_match('/\b(\d{8})\b/', $usuario, $match)) {
            return $match[1];
        }

        if (preg_match('/(\d{8})/', $usuario, $match)) {
            return $match[1];
        }

        return null;
    }

    /**
     * Formulario para crear usuario PPPoE (secret en MikroTik)
     */
    public function crearUsuarioPppoeForm(Cliente $cliente, RouterOSPppoeService $pppoeService)
    {
        $this->authorize('view', $cliente);

        $routers = Router::where('estado', true)->orderBy('nombre')->get();

        $prefijo = trim((string) $cliente->documento);
        if ($prefijo === '') {
            $prefijo = 'user';
        }
        $sugeridoInicial = $prefijo . '_01';
        $routerInicial = request()->query('router_id') ?: old('router_id');
        if ($routerInicial) {
            $router = Router::find($routerInicial);
            if ($router) {
                try {
                    $sugeridoInicial = $pppoeService->getSiguienteUsuarioDisponible($router, $prefijo);
                } catch (\Throwable $e) {
                    $sugeridoInicial = $prefijo . '_01';
                }
            }
        }

        return view('clientes.crear-usuario-pppoe', compact('cliente', 'routers', 'sugeridoInicial', 'prefijo'));
    }

    /**
     * API: Siguiente usuario PPPoE disponible para el router (dni_01, dni_02, ...)
     */
    public function getSiguienteUsuarioPppoe(Request $request, Cliente $cliente, RouterOSPppoeService $pppoeService)
    {
        $this->authorize('view', $cliente);

        $routerId = $request->input('router_id');
        if (!$routerId) {
            return response()->json(['success' => false, 'message' => 'router_id es requerido'], 400);
        }

        $router = Router::find($routerId);
        if (!$router) {
            return response()->json(['success' => false, 'message' => 'Router no encontrado'], 404);
        }

        $prefijo = trim((string) $cliente->documento);
        if ($prefijo === '') {
            $prefijo = 'user';
        }

        try {
            $sugerido = $pppoeService->getSiguienteUsuarioDisponible($router, $prefijo);
            return response()->json(['success' => true, 'sugerido' => $sugerido]);
        } catch (\Exception $e) {
            Log::warning('Error al obtener siguiente usuario PPPoE', [
                'cliente_id' => $cliente->id,
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);
            $sugerido = $prefijo . '_01';
            return response()->json(['success' => true, 'sugerido' => $sugerido]);
        }
    }

    /**
     * Crear usuario PPPoE (secret en router) y opcionalmente registrar servicio en el sistema
     */
    public function storeCrearUsuarioPppoe(Request $request, Cliente $cliente, RouterOSPppoeService $pppoeService)
    {
        $this->authorize('update', $cliente);

        $tenantConn = TenantConnectionService::currentTenantConnectionName();
        $validated = $request->validate([
            'usuario_pppoe' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'router_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) use ($tenantConn): void {
                    if (! $tenantConn) {
                        $fail(__('validation.exists', ['attribute' => $attribute]));
                        return;
                    }
                    if (! DB::connection($tenantConn)->table('routers')->where('id', (int) $value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => $attribute]));
                    }
                },
            ],
            'plan_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) use ($tenantConn): void {
                    if (! $tenantConn) {
                        $fail(__('validation.exists', ['attribute' => $attribute]));
                        return;
                    }
                    if (! DB::connection($tenantConn)->table('planes')->where('id', (int) $value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => $attribute]));
                    }
                },
            ],
            'network' => ['nullable', 'string', 'max:255'],
            'registrar_servicio' => ['nullable', 'boolean'],
        ]);

        $router = Router::findOrFail($validated['router_id']);
        $plan = \App\Modules\Servicios\Models\Plan::findOrFail($validated['plan_id']);

        if ($plan->router_id != $router->id) {
            return back()
                ->withInput()
                ->with('error', 'El plan seleccionado no pertenece al router elegido.');
        }

        $profile = $plan->perfil_mikrotik ?: $plan->nombre;
        $remoteAddress = $validated['network'] ?? $plan->remote_address;

        try {
            $pppoeService->addSecret(
                $router,
                $validated['usuario_pppoe'],
                $validated['password'],
                $profile,
                $remoteAddress ?: null
            );
        } catch (\Exception $e) {
            Log::error('Error al crear secret PPPoE', [
                'cliente_id' => $cliente->id,
                'usuario' => $validated['usuario_pppoe'],
                'error' => $e->getMessage(),
            ]);
            return back()
                ->withInput()
                ->with('error', 'No se pudo crear el usuario en el router: ' . $e->getMessage());
        }

        if (!empty($validated['registrar_servicio'])) {
            $ubicacion = Ubicacion::firstOrCreate(
                [
                    'cliente_id' => $cliente->id,
                    'router_id' => $router->id,
                ],
                [
                    'direccion' => 'Pendiente',
                    'referencia' => 'Usuario PPPoE creado desde panel',
                    'isp_id' => $cliente->isp_id,
                ]
            );

            Servicio::create([
                'ubicacion_id' => $ubicacion->id,
                'router_id' => $router->id,
                'plan_id' => $plan->id,
                'tipo_pppoe' => 'usuario_unico',
                'usuario_pppoe' => $validated['usuario_pppoe'],
                'password_pppoe' => $validated['password'],
                'estado' => 'activo',
                'es_provisional' => true,
                'fecha_instalacion' => now(),
                'isp_id' => $cliente->isp_id,
            ]);
        }

        $mensaje = 'Usuario PPPoE "' . $validated['usuario_pppoe'] . '" creado correctamente en el router.';
        if (!empty($validated['registrar_servicio'])) {
            $mensaje .= ' Servicio registrado en el cliente.';
        }

        return redirect()
            ->to(route('clientes.show', $cliente) . '#content-servicios')
            ->with('success', $mensaje);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClienteRequest $request)
    {
        $this->authorize('create', Cliente::class);

        $validated = $request->validated();

        // Procesar datos DNI/RUC usando el servicio
        $validated = $this->clienteService->procesarDatosCliente($validated, $request);

        $cliente = Cliente::create($validated);

        return redirect()
            ->route('clientes.show', $cliente)
            ->with('success', 'Cliente creado correctamente.')
            ->with('crear_servicio', true);
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        $this->authorize('view', $cliente);

        // Optimizado: Cargar todas las relaciones necesarias de una vez
        $cliente->load([
            'ubicaciones' => function ($query) {
                $query->with('router')->withCount('servicios');
            },
            'ubicaciones.servicios' => function ($query) {
                $query->with(['router', 'plan', 'onu']);
            },
            'servicios' => function ($query) {
                $query->with(['ubicacion', 'router', 'plan', 'onu']);
            },
            'recibos' => function ($query) {
                $query->with([
                    'servicio:id,mac_address,plan_id',
                    'promesasPago' => function ($q) {
                        $q->whereIn('estado', ['pendiente', 'vencida'])->latest();
                    },
                    'pagos' => function ($q) {
                        $q->latest()->limit(5); // Solo últimos 5 para preview
                    }
                ]);
            },
            'pagos' => function ($query) {
                $query->with(['servicio:id,mac_address', 'recibo:id,periodo', 'medioPago', 'registradoPor:id,name'])
                    ->latest()
                    ->limit(50); // Limitar para performance
            },
            'promesasPago' => function ($query) {
                $query->with(['recibo:id,periodo', 'servicio:id,mac_address', 'creadoPor:id,name'])
                    ->whereIn('estado', ['pendiente', 'vencida'])
                    ->latest();
            }
        ]);

        // Calcular estadísticas usando el servicio
        $estadisticas = $this->clienteService->calcularEstadisticas($cliente);

        // Pre-cargar atributos calculados para evitar consultas en la vista
        $cliente->tiene_servicios_activos = $cliente->servicios->where('estado', 'activo')->isNotEmpty();
        $cliente->tiene_promesas_activas = $cliente->promesasPago->whereIn('estado', ['pendiente', 'vencida'])->isNotEmpty();

        // Agregar promesa activa a cada recibo para evitar múltiples llamadas
        $cliente->recibos->each(function ($recibo) {
            $recibo->promesa_activa = $recibo->promesasPago->first();
        });

        return view('clientes.show', compact('cliente', 'estadisticas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        $this->authorize('update', $cliente);

        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        $this->authorize('update', $cliente);

        $validated = $request->validated();

        // Asegurar que telefonos esté en el array de datos a actualizar
        if (!isset($validated['telefonos'])) {
            // Forzar que se incluya telefonos (incluso si está vacío)
            $validated['telefonos'] = $request->input('telefonos', '');
        }

        $cliente->update($validated);

        $cliente->refresh();

        return redirect()
            ->route('clientes.show', $cliente)
            ->with('success', 'Cliente actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        $this->authorize('delete', $cliente);

        // Verificar si puede eliminarse usando el servicio
        $validacion = $this->clienteService->puedeEliminar($cliente);

        if (!$validacion['puede_eliminar']) {
            $razones = implode(', ', $validacion['razones']);
            return back()
                ->with('error', "No se puede eliminar el cliente porque {$razones}.");
        }

        $cliente->delete();

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente eliminado correctamente.');
    }

    /**
     * Consultar DNI para obtener nombre completo
     */
    public function consultarDni(Request $request)
    {
        $this->authorize('create', Cliente::class);

        $dniInput = $request->input('dni', '');
        $dni = preg_replace('/\D/', '', $dniInput); // Solo números

        if (empty($dni) || strlen($dni) < 8) {
            return response()->json([
                'success' => false,
                'message' => 'DNI inválido. Debe tener al menos 8 dígitos.',
                'dni_recibido' => $dniInput,
            ], 422);
        }

        // Asegurar que tenga exactamente 8 dígitos
        $dni = str_pad($dni, 8, '0', STR_PAD_LEFT);

        try {
            $resultado = $this->dniService->consultar($dni);

            if ($resultado === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró información para este DNI. Por favor, ingresa el nombre manualmente.',
                ], 200);
            }

            // Si hay un error específico (token expirado, etc.), tratar como DNI no encontrado
            if (isset($resultado['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró información para este DNI. Por favor, ingresa el nombre manualmente.',
                ], 200);
            }

            // Si se encontró el nombre
            if (isset($resultado['nombre'])) {
                return response()->json([
                    'success' => true,
                    'nombre' => $resultado['nombre'],
                    'nombres' => $resultado['nombres'] ?? null,
                    'apellido_paterno' => $resultado['apellido_paterno'] ?? null,
                    'apellido_materno' => $resultado['apellido_materno'] ?? null,
                    'dni' => $resultado['dni'] ?? null,
                    'direccion' => $resultado['direccion'] ?? null,
                    'fuente' => $resultado['fuente'] ?? 'apisperu',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se encontró información para este DNI.',
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error en consultarDni: " . $e->getMessage() . " - Trace: " . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar el DNI. Por favor, ingresa el nombre manualmente.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Consultar RUC para obtener información de la empresa
     */
    public function consultarRuc(Request $request)
    {
        $this->authorize('create', Cliente::class);

        $rucInput = $request->input('ruc', '');
        $ruc = preg_replace('/\D/', '', $rucInput); // Solo números

        if (empty($ruc) || strlen($ruc) < 11) {
            return response()->json([
                'success' => false,
                'message' => 'RUC inválido. Debe tener 11 dígitos.',
                'ruc_recibido' => $rucInput,
            ], 422);
        }

        // Asegurar que tenga exactamente 11 dígitos
        $ruc = str_pad($ruc, 11, '0', STR_PAD_LEFT);

        try {
            $resultado = $this->rucService->consultar($ruc);

            if ($resultado === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró información para este RUC. Por favor, ingresa la razón social manualmente.',
                ], 200);
            }

            // Si hay un error específico
            if (isset($resultado['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $resultado['error'],
                    'nota' => 'Por favor, verifica el token de APISPERU en Sistema > APIs > Editar API APISPERU.',
                ], 200);
            }

            // Si se encontró la información
            if (isset($resultado['razon_social'])) {
                return response()->json([
                    'success' => true,
                    'razon_social' => $resultado['razon_social'],
                    'nombre' => $resultado['razon_social'], // nombre = razon_social para RUC
                    'nombre_comercial' => $resultado['nombre_comercial'] ?? null,
                    'ruc' => $resultado['ruc'] ?? null,
                    'direccion' => $resultado['direccion'] ?? null,
                    'departamento' => $resultado['departamento'] ?? $resultado['departamento_api'] ?? null,
                    'provincia' => $resultado['provincia'] ?? $resultado['provincia_api'] ?? null,
                    'distrito' => $resultado['distrito'] ?? $resultado['distrito_api'] ?? null,
                    'ubigeo' => $resultado['ubigeo'] ?? null,
                    'estado' => $resultado['estado'] ?? null,
                    'condicion' => $resultado['condicion'] ?? null,
                    'telefonos' => $resultado['telefonos'] ?? null,
                    'capital' => $resultado['capital'] ?? null,
                    'fuente' => $resultado['fuente'] ?? 'apisperu',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se encontró información para este RUC.',
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error en consultarRuc: " . $e->getMessage() . " - Trace: " . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar el RUC. Por favor, ingresa la razón social manualmente.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Obtener credenciales PPPoE de servicios previos del cliente
     */
    public function obtenerCredencialesServicios(Cliente $cliente)
    {
        $this->authorize('view', $cliente);

        try {
            // Buscar el último servicio activo del cliente que tenga usuario y password PPPoE
            $servicio = \App\Modules\Servicios\Models\Servicio::whereHas('ubicacion', function ($query) use ($cliente) {
                $query->where('cliente_id', $cliente->id);
            })
                ->where('tipo_pppoe', 'usuario_unico')
                ->whereNotNull('usuario_pppoe')
                ->whereNotNull('password_pppoe')
                ->where('usuario_pppoe', '!=', '')
                ->where('password_pppoe', '!=', '')
                ->latest()
                ->first();

            if ($servicio) {
                return response()->json([
                    'success' => true,
                    'credenciales' => [
                        'usuario_pppoe' => $servicio->usuario_pppoe,
                        'password_pppoe' => $servicio->password_pppoe,
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se encontraron credenciales previas para este cliente',
            ]);
        } catch (\Exception $e) {
            Log::error("Error en obtenerCredencialesServicios: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener credenciales',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Cortar servicios vencidos (servicios activos con deudas vencidas)
     */
    public function cortarServiciosVencidos(Request $request, RouterOSScriptService $scriptService)
    {
        /** @var \App\Modules\ControlAcceso\Models\User|null $user */
        $user = Auth::user();
        if (!$user || !$user->hasPermission('clientes.update')) {
            abort(403, 'No autorizado para cortar servicios vencidos.');
        }

        try {
            // Buscar servicios activos que tienen recibos vencidos
            $serviciosQuery = Servicio::where('estado', 'activo')
                ->whereHas('recibos', function ($query) {
                    $query->where('estado', 'vencido')
                        ->where('saldo', '>', 0);
                })
                ->with(['router', 'recibos' => function ($q) {
                    $q->where('estado', 'vencido')
                        ->where('saldo', '>', 0);
                }])
                ->orderBy('id');

            if (!$serviciosQuery->exists()) {
                return redirect()
                    ->route('clientes.index')
                    ->with('info', 'No se encontraron servicios activos con recibos vencidos.');
            }

            $cortados = 0;
            $errores = 0;
            $erroresDetalle = [];

            try {
                $serviciosQuery->chunkById(200, function ($serviciosVencidos) use (&$cortados, &$errores, &$erroresDetalle, $scriptService) {
                    DB::beginTransaction();
                    try {
                        foreach ($serviciosVencidos as $servicio) {
                            try {
                                // Actualizar estado del servicio
                                $servicio->update(['estado' => 'cortado']);

                                // Crear script y scheduler en MikroTik si tiene router y MAC
                                if ($servicio->router && $servicio->mac_address) {
                                    $macNormalizada = $this->normalizarMacAddress($servicio->mac_address);

                                    $scriptResult = $scriptService->createCorteScript($servicio->router, $macNormalizada);

                                    if ($scriptResult['success']) {
                                        $schedulerResult = $scriptService->createCorteScheduler(
                                            $servicio->router,
                                            $scriptResult['script_name']
                                        );

                                        if (!$schedulerResult['success']) {
                                            Log::warning('Script creado pero scheduler falló al cortar servicio vencido', [
                                                'servicio_id' => $servicio->id,
                                                'script_result' => $scriptResult,
                                                'scheduler_result' => $schedulerResult
                                            ]);
                                        }
                                    } else {
                                        Log::warning('No se pudo crear script de corte en MikroTik', [
                                            'servicio_id' => $servicio->id,
                                            'error' => $scriptResult['message'] ?? 'Error desconocido'
                                        ]);
                                    }
                                }

                                $cortados++;
                            } catch (\Exception $e) {
                                $errores++;
                                $erroresDetalle[] = [
                                    'servicio_id' => $servicio->id,
                                    'error' => $e->getMessage()
                                ];
                                Log::error("Error al cortar servicio vencido", [
                                    'servicio_id' => $servicio->id,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        throw $e;
                    }
                });

                $mensaje = "Se cortaron {$cortados} servicio(s) con recibos vencidos.";
                if ($errores > 0) {
                    $mensaje .= " Hubo {$errores} error(es).";
                }

                return redirect()
                    ->route('clientes.index')
                    ->with('success', $mensaje);
            } catch (\Exception $e) {
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error("Error al cortar servicios vencidos", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('clientes.index')
                ->with('error', 'Error al cortar servicios vencidos: ' . $e->getMessage());
        }
    }
}
