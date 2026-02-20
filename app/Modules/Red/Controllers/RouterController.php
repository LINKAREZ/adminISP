<?php

namespace App\Modules\Red\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Traits\LogsContext;
use App\Modules\Red\Requests\StoreRouterRequest;
use App\Modules\Red\Requests\UpdateRouterRequest;
use App\Modules\Red\Requests\DesconectarPppoeRequest;
use App\Modules\Red\Requests\CrearNatOnuRequest;
use App\Modules\Red\Requests\EliminarNatOnuRequest;
use App\Modules\Red\Requests\AddAddressListItemRequest;
use App\Modules\Red\Requests\CrearReglaBloqueoRequest;
use App\Modules\Red\Requests\StoreReglaRequest;
use App\Modules\Red\Requests\UpdateReglaRequest;
use App\Core\Services\TenantConnectionService;
use App\Modules\Sistema\Services\PlanLimitService;
use App\Modules\Sistema\Models\Licencia;
use App\Modules\Red\Models\Router;
use App\Modules\Red\Models\Nodo;
use App\Modules\Red\Models\Regla;
use App\Modules\Red\Services\RouterOSConnectionService;
use App\Modules\Red\Services\RouterOSExportService;
use App\Modules\Red\Services\RouterOSPppoeService;
use App\Modules\Red\Services\RouterOSFirewallService;
use App\Modules\Red\Services\RouterOSNatService;
use App\Modules\Red\Services\SnmpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RouterController extends Controller
{
    use LogsContext;

    public function __construct()
    {
        $this->authorizeResource(Router::class, 'router');
    }

    public function index()
    {
        $conn = TenantConnectionService::currentTenantConnectionName();
        if (!$conn) {
            return view('tenant-sin-configurar');
        }
        $routers = Router::on($conn)->with('nodo')->withoutGlobalScopes()->latest()->paginate(15);
        $nodos = Nodo::on($conn)->withoutGlobalScopes()->where('estado', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('red.routers.index', compact('routers', 'nodos'));
    }

    public function create()
    {
        $conn = TenantConnectionService::currentTenantConnectionName();
        if (!$conn) {
            return view('tenant-sin-configurar');
        }
        $nodos = Nodo::on($conn)->withoutGlobalScopes()->where('estado', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
        $ispId = session('current_isp_id');
        $licencias = $ispId
            ? Licencia::on('mysql')->where('is_active', true)->whereHas('assignedToIsps', fn ($q) => $q->where('isps.id', $ispId))->orderBy('sort_order')->orderBy('name')->get()
            : collect();
        return view('red.routers.create', compact('nodos', 'licencias'));
    }

    public function store(StoreRouterRequest $request, PlanLimitService $planLimitService)
    {
        $conn = TenantConnectionService::currentTenantConnectionName();
        if (!$conn) {
            return redirect()->route('red.routers.index')
                ->with('error', 'No hay ISP seleccionado. Seleccione un ISP para crear routers.');
        }
        $isp = auth()->user()?->isp;
        if ($isp && !$planLimitService->canAddRouter($isp)) {
            return redirect()->route('red.routers.index')
                ->with('error', 'Límite de routers alcanzado (plan Gratuito: máximo 1 router). Pase a un plan de pago para añadir más.');
        }
        Router::on($conn)->create($request->validated());

        return redirect()->route('red.routers.index')
            ->with('success', 'Router creado correctamente.');
    }

    public function show(
        Router $router,
        RouterOSConnectionService $connectionService,
        RouterOSPppoeService $pppoeService,
        RouterOSFirewallService $firewallService
    ) {
        $router->load('nodo');

        $conexionExitosa = false;
        $mensajeConexion = '';
        $infoSistema = null;
        $conexionesPppoe = [];
        $totalConexiones = 0;

        try {
            $conexionExitosa = $connectionService->testConnection($router);
            if ($conexionExitosa) {
                $mensajeConexion = 'Conexión exitosa';
                try {
                    $infoSistema = $connectionService->getSystemInfo($router);
                } catch (\Exception $e) {
                    //
                }

                try {
                    $todasConexiones = $pppoeService->getActiveConnections($router);
                    $totalConexiones = count($todasConexiones);
                    $conexionesPppoe = array_slice($todasConexiones, 0, 10);

                    $this->logInfo("Conexiones PPPoE procesadas", [
                        'router_id' => $router->id,
                        'total_conexiones' => $totalConexiones,
                        'conexiones_preview' => count($conexionesPppoe),
                        'action' => 'router_show_pppoe',
                    ]);
                } catch (\Exception $e) {
                    $this->logError("Error al obtener conexiones PPPoE", [
                        'router_id' => $router->id,
                        'action' => 'router_show_pppoe',
                    ], $e);
                    $conexionesPppoe = [];
                    $totalConexiones = 0;
                }
            } else {
                $mensajeConexion = 'No se pudo conectar al router';
            }
        } catch (\Exception $e) {
            $mensajeConexion = 'Error: ' . $e->getMessage();
        }

        $reglasBloqueo = [];
        $addressLists = [];
        if ($conexionExitosa) {
            try {
                $reglasBloqueo = $firewallService->getBlockRules($router);
                $addressLists = $firewallService->getAddressLists($router);
            } catch (\Exception $e) {
                $this->logError("Error al obtener reglas de bloqueo", [
                    'router_id' => $router->id,
                    'action' => 'router_show_rules',
                ], $e);
            }
        }

        $this->asegurarReglaCorte($router);

        $reglas = $router->reglas()->latest()->limit(200)->get();

        return view('red.routers.show', compact(
            'router',
            'conexionExitosa',
            'mensajeConexion',
            'infoSistema',
            'conexionesPppoe',
            'totalConexiones',
            'reglasBloqueo',
            'addressLists',
            'reglas'
        ));
    }

    public function conexionesPppoe(Router $router, RouterOSPppoeService $pppoeService)
    {
        $this->authorize('view', $router);
        try {
            $conexiones = $pppoeService->getActiveConnections($router);

            $perfilesActivos = [];
            $usuariosDuplicados = [];
            $usuariosVistos = [];

            foreach ($conexiones as $conexion) {
                $usuario = $conexion['name'] ?? '';
                $perfil = $conexion['profile'] ?? '';

                if ($perfil && !in_array($perfil, $perfilesActivos)) {
                    $perfilesActivos[] = $perfil;
                }

                if ($usuario) {
                    if (in_array($usuario, $usuariosVistos)) {
                        if (!in_array($usuario, $usuariosDuplicados)) {
                            $usuariosDuplicados[] = $usuario;
                        }
                    } else {
                        $usuariosVistos[] = $usuario;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'conexiones' => $conexiones,
                'router' => [
                    'id' => $router->id,
                    'nombre' => $router->nombre,
                ],
                'estadisticas' => [
                    'total' => count($conexiones),
                    'perfiles_activos' => count($perfilesActivos),
                    'usuarios_duplicados' => count($usuariosDuplicados),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener conexiones: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function detalleConexionPppoe(Router $router, string $sessionId, RouterOSPppoeService $pppoeService)
    {
        $this->authorize('view', $router);
        try {
            $detalle = $pppoeService->getConnectionDetails($router, $sessionId);

            if (empty($detalle)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conexión no encontrada',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'conexion' => $detalle,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalles: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function exportarPppoe(Router $router, RouterOSExportService $exportService)
    {
        $this->authorize('update', $router);
        try {
            $result = $exportService->syncServiciosToRouter($router);
            $msg = sprintf(
                'Exportación completada: %d creados, %d actualizados.',
                $result['created'],
                $result['updated']
            );
            if ($result['skipped'] > 0) {
                $msg .= ' ' . $result['skipped'] . ' omitidos (sin contraseña u otro motivo).';
            }
            if (!empty($result['errors'])) {
                $msg .= ' Errores: ' . count($result['errors']) . ' servicio(s).';
            }
            return redirect()->route('red.routers.show', $router)->with('success', $msg);
        } catch (\Throwable $e) {
            Log::error('Error al exportar PPPoE al router', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('red.routers.show', $router)
                ->with('error', 'No se pudo exportar: ' . $e->getMessage());
        }
    }

    public function desconectarPppoe(Router $router, DesconectarPppoeRequest $request, RouterOSPppoeService $pppoeService)
    {
        $this->authorize('update', $router);
        try {
            $pppoeService->disconnectSession($router, $request->session_id);

            return response()->json([
                'success' => true,
                'message' => 'Sesión desconectada correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desconectar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crea una regla NAT para acceder a la interfaz web de una ONU
     * y retorna la URL para abrirla
     */
    public function crearNatOnu(Router $router, CrearNatOnuRequest $request, RouterOSNatService $natService)
    {
        $this->authorize('update', $router);
        try {
            $internalIp = $request->ip;
            $internalPort = 443; // Puerto HTTPS de la ONU

            // Obtener puerto disponible si no se especifica
            if ($request->has('port') && $request->port) {
                $externalPort = (int)$request->port;
            } else {
                $externalPort = $natService->getAvailablePort($router);
            }

            // Calcular la hora de eliminación (5 minutos después) en zona horaria de Perú
            $horaEliminacion = now()->setTimezone(config('app.timezone', 'America/Lima'))->addMinutes(5)->format('H:i:s');
            $comment = "ONU-{$internalIp}-{$externalPort} (Se elimina: {$horaEliminacion})";

            // Crear la regla NAT
            $result = $natService->createDstNatRule(
                $router,
                (string)$externalPort,
                $internalIp,
                $internalPort,
                $comment
            );

            // Construir la URL usando el host del router (ip_url: centro.wan.net.pe, castro.wan.net.pe, etc.)
            $host = preg_replace('#^https?://#', '', trim($router->ip_url));
            $url = "https://{$host}:{$externalPort}";

            return response()->json([
                'success' => true,
                'message' => $result['exists'] ? 'Regla NAT ya existía' : 'Regla NAT creada correctamente',
                'url' => $url,
                'port' => $externalPort,
                'internal_ip' => $internalIp,
                'internal_port' => $internalPort,
                'rule' => $result['rule'],
                'rule_id' => $result['rule_id'] ?? null,
                'comment' => $comment,
                'exists' => $result['exists'] ?? false
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear NAT para ONU', [
                'router_id' => $router->id,
                'ip' => $request->ip,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear regla NAT: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function eliminarNatOnu(Router $router, EliminarNatOnuRequest $request, RouterOSNatService $natService)
    {
        $this->authorize('update', $router);
        try {
            $ruleId = $request->input('rule_id');
            $comment = $request->input('comment');
            $externalPort = $request->input('port');

            if (!$ruleId && !$comment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Se requiere rule_id o comment para eliminar la regla NAT',
                ], 400);
            }

            $result = $natService->removeDstNatRule(
                $router,
                $ruleId,
                $comment,
                $externalPort ? (string)$externalPort : null
            );

            return response()->json([
                'success' => true,
                'message' => $result['deleted'] ? 'Regla NAT eliminada correctamente' : 'Regla NAT no encontrada',
                'deleted' => $result['deleted'] ?? false,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar NAT para ONU', [
                'router_id' => $router->id,
                'rule_id' => $request->input('rule_id'),
                'comment' => $request->input('comment'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar regla NAT: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit(Router $router)
    {
        $conn = TenantConnectionService::currentTenantConnectionName();
        if (!$conn) {
            return view('tenant-sin-configurar');
        }
        $nodos = Nodo::on($conn)->withoutGlobalScopes()->where('estado', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
        $ispId = session('current_isp_id');
        $licencias = $ispId
            ? Licencia::on('mysql')->where('is_active', true)->whereHas('assignedToIsps', fn ($q) => $q->where('isps.id', $ispId))->orderBy('sort_order')->orderBy('name')->get()
            : collect();
        return view('red.routers.edit', compact('router', 'nodos', 'licencias'));
    }

    public function update(UpdateRouterRequest $request, Router $router)
    {
        $validated = $request->validated();

        if (empty($validated['contraseña'])) {
            unset($validated['contraseña']);
        }

        $router->update($validated);

        return redirect()->route('red.routers.index')
            ->with('success', 'Router actualizado correctamente.');
    }

    public function destroy(Router $router)
    {
        $router->delete();

        return redirect()->route('red.routers.index')
            ->with('success', 'Router eliminado correctamente.');
    }

    public function getReglasBloqueo(Router $router, RouterOSFirewallService $firewallService)
    {
        $this->authorize('view', $router);
        try {
            $reglas = $firewallService->getBlockRules($router);

            return response()->json([
                'success' => true,
                'reglas' => $reglas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener reglas: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAddressLists(Router $router, RouterOSFirewallService $firewallService)
    {
        $this->authorize('view', $router);
        try {
            $addressLists = $firewallService->getAddressLists($router);

            return response()->json([
                'success' => true,
                'address_lists' => $addressLists
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener address lists: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAddressListItems(Router $router, RouterOSFirewallService $firewallService, Request $request)
    {
        $this->authorize('view', $router);
        try {
            $validated = $request->validate([
                'list' => ['required', 'string', 'max:64'],
            ]);
            $listName = $validated['list'];

            $items = $firewallService->getAddressListItems($router, $listName);

            return response()->json([
                'success' => true,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener elementos: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function addAddressListItem(Router $router, RouterOSFirewallService $firewallService, AddAddressListItemRequest $request)
    {
        $this->authorize('update', $router);
        try {
            $firewallService->addAddressListItem(
                $router,
                $request->input('list'),
                $request->input('address'),
                $request->input('comment')
            );

            return response()->json([
                'success' => true,
                'message' => 'Elemento agregado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar elemento: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function crearReglaBloqueo(CrearReglaBloqueoRequest $request, Router $router, RouterOSFirewallService $firewallService)
    {
        $this->authorize('update', $router);
        try {
            $regla = $firewallService->createBlockRule(
                $router,
                $request->source_address_list,
                $request->chain ?? 'forward',
                $request->comment
            );

            return response()->json([
                'success' => true,
                'message' => 'Regla de bloqueo creada correctamente',
                'regla' => $regla
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear regla: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getReglas(Router $router)
    {
        $this->authorize('view', $router);
        $reglas = $router->reglas()->latest()->limit(200)->get();

        return response()->json([
            'success' => true,
            'reglas' => $reglas
        ]);
    }

    public function storeRegla(StoreReglaRequest $request, Router $router)
    {
        $this->authorize('update', $router);
        $regla = \App\Modules\Red\Models\Regla::create([
            'router_id' => $router->id,
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'configuracion' => $request->configuracion,
            'activo' => $request->activo ?? true,
            'exportado' => false,
            'notas' => $request->notas,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Regla creada correctamente',
            'regla' => $regla
        ]);
    }

    public function updateRegla(UpdateReglaRequest $request, Router $router, Regla $regla)
    {
        $this->authorize('update', $router);
        if ($regla->router_id !== $router->id) {
            return response()->json([
                'success' => false,
                'message' => 'La regla no pertenece a este router',
            ], 403);
        }

        $regla->update([
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'configuracion' => $request->configuracion,
            'activo' => $request->activo ?? $regla->activo,
            'notas' => $request->notas,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Regla actualizada correctamente',
            'regla' => $regla->fresh()
        ]);
    }

    public function destroyRegla(
        Router $router,
        Regla $regla,
        RouterOSConnectionService $connectionService,
        RouterOSFirewallService $firewallService
    ) {
        $this->authorize('update', $router);
        try {
            /** @var \App\Modules\ControlAcceso\Models\User|null $user */
            $user = Auth::user();
            if (!$user || !$user->hasPermission('red.update')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar reglas',
                ], 403);
            }

            if ($regla->router_id !== $router->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'La regla no pertenece a este router',
                ], 403);
            }

            $esReglaCorte = $regla->nombre === 'Regla de corte de servicio';

            $eliminadaDeMikrotik = false;
            if ($regla->exportado) {
                try {
                    if ($connectionService->testConnection($router)) {
                        $config = $regla->configuracion;

                        $this->logInfo("Iniciando eliminación de regla de MikroTik", [
                            'router_id' => $router->id,
                            'regla_id' => $regla->id,
                            'regla_nombre' => $regla->nombre,
                            'regla_exportado' => $regla->exportado,
                            'action' => 'router_destroy_rule',
                        ]);

                        switch ($regla->tipo) {
                            case 'firewall':
                                $sourceAddressList = $config['source_address_list'] ?? '';
                                $chain = $config['chain'] ?? 'forward';
                                $comment = $config['comment'] ?? null;

                                $this->logInfo("Parámetros para eliminar regla firewall", [
                                    'router_id' => $router->id,
                                    'regla_id' => $regla->id,
                                    'action' => 'router_destroy_rule',
                                ]);

                                $resultado = $firewallService->removeBlockRule(
                                    $router,
                                    $sourceAddressList,
                                    $chain,
                                    $comment
                                );
                                $eliminadaDeMikrotik = $resultado['deleted'] ?? false;

                                $this->logInfo("Resultado de eliminación de regla de MikroTik", [
                                    'router_id' => $router->id,
                                    'regla_id' => $regla->id,
                                    'eliminada' => $eliminadaDeMikrotik,
                                    'action' => 'router_destroy_rule',
                                ]);
                                break;
                            default:
                                $this->logWarning("Tipo de regla no soportado para eliminación en MikroTik", [
                                    'regla_id' => $regla->id,
                                    'tipo' => $regla->tipo,
                                    'action' => 'router_destroy_rule',
                                ]);
                        }
                    } else {
                        $this->logWarning("No se pudo conectar al router para eliminar regla de MikroTik", [
                            'router_id' => $router->id,
                            'regla_id' => $regla->id,
                            'action' => 'router_destroy_rule',
                        ]);
                    }
                } catch (\Exception $e) {
                    $this->logError("Error al eliminar regla de MikroTik (continuando con eliminación de BD)", [
                        'router_id' => $router->id,
                        'regla_id' => $regla->id,
                        'action' => 'router_destroy_rule',
                    ], $e);
                }
            }

            if ($esReglaCorte) {
                $regla->update(['exportado' => false]);

                $mensaje = 'Regla de corte desinstalada de MikroTik';
                if ($eliminadaDeMikrotik) {
                    $mensaje .= ' correctamente';
                } else {
                    $mensaje .= ' (no se encontró en MikroTik, pero se marcó como no exportada)';
                }

                return response()->json([
                    'success' => true,
                    'message' => $mensaje,
                    'eliminada_de_mikrotik' => $eliminadaDeMikrotik,
                    'regla_preservada' => true
                ]);
            }

            $regla->delete();

            $mensaje = 'Regla eliminada correctamente';
            if ($regla->exportado) {
                if ($eliminadaDeMikrotik) {
                    $mensaje .= ' (también eliminada de MikroTik)';
                } else {
                    $mensaje .= ' (no se pudo eliminar de MikroTik, pero se eliminó de la base de datos)';
                }
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'eliminada_de_mikrotik' => $eliminadaDeMikrotik
            ]);
        } catch (\Exception $e) {
            $this->logError('Error al eliminar regla', [
                'router_id' => $router->id,
                'regla_id' => $regla->id ?? null,
                'action' => 'router_destroy_rule',
            ], $e);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar regla: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportarRegla(
        Router $router,
        Regla $regla,
        RouterOSConnectionService $connectionService,
        RouterOSFirewallService $firewallService
    ) {
        $this->authorize('update', $router);
        if ($regla->router_id !== $router->id) {
            return response()->json([
                'success' => false,
                'message' => 'La regla no pertenece a este router',
            ], 403);
        }

        try {
            if (!$connectionService->testConnection($router)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo conectar al router',
                ], 500);
            }

            $resultado = null;
            switch ($regla->tipo) {
                case 'firewall':
                    $config = $regla->configuracion;
                    $resultado = $firewallService->createBlockRule(
                        $router,
                        $config['source_address_list'] ?? '',
                        $config['chain'] ?? 'forward',
                        $config['comment'] ?? null
                    );
                    break;
                case 'address-list':
                    $config = $regla->configuracion;
                    $firewallService->addAddressListItem(
                        $router,
                        $config['list'] ?? '',
                        $config['address'] ?? '',
                        $config['comment'] ?? null
                    );
                    $resultado = ['success' => true];
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Tipo de regla no soportado para exportación',
                    ], 400);
            }

            $regla->update(['exportado' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Regla exportada correctamente a MikroTik',
                'resultado' => $resultado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar regla: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function testSnmp(Router $router, \App\Modules\Red\Services\SnmpService $snmpService)
    {
        $this->authorize('view', $router);
        try {
            // Verificación detallada
            $extensionLoaded = extension_loaded('snmp');
            $hasSnmpGet = function_exists('snmpget');
            $hasSnmpWalk = function_exists('snmpwalk');
            $snmpAvailable = $snmpService->isAvailable();

            $info = [
                'php_sapi' => php_sapi_name(),
                'php_version' => PHP_VERSION,
                'php_ini_loaded_file' => php_ini_loaded_file(),
                'extension_dir' => ini_get('extension_dir'),
                'extension_loaded_snmp' => $extensionLoaded,
                'function_exists_snmpget' => $hasSnmpGet,
                'function_exists_snmpwalk' => $hasSnmpWalk,
                'snmp_available' => $snmpAvailable,
                'all_checks_pass' => $extensionLoaded && $hasSnmpGet && $hasSnmpWalk,
                'router' => [
                    'id' => $router->id,
                    'nombre' => $router->nombre,
                    'ip_url' => $router->ip_url,
                    'puerto_snmp' => $router->puerto_snmp,
                    'tiene_comunidad' => !empty($router->comunidad),
                ],
            ];

            // Si está configurado, intentar una prueba real
            if ($info['snmp_available'] && $router->puerto_snmp && $router->comunidad) {
                try {
                    $host = $router->ip_url;
                    $community = $router->comunidad;
                    $port = $router->puerto_snmp ?? 161;

                    // Intentar obtener el sysDescr (OID estándar)
                    $oid = '1.3.6.1.2.1.1.1.0';
                    $result = @snmpget($host . ':' . $port, $community, $oid, 5, 2);

                    if ($result !== false) {
                        $info['test_success'] = true;
                        $info['test_result'] = $result;
                        $info['test_message'] = 'Conexión SNMP exitosa';

                        // Intentar listar interfaces disponibles
                        try {
                            $oid = '1.3.6.1.2.1.2.2.1.2'; // ifName
                            $interfaces = @snmpwalk($host . ':' . $port, $community, $oid, 5, 2);

                            if ($interfaces !== false) {
                                $interfacesList = [];
                                foreach ($interfaces as $fullOid => $name) {
                                    $cleanName = trim($name, '"');
                                    $parts = explode('.', $fullOid);
                                    $index = (int)end($parts);
                                    $interfacesList[] = [
                                        'index' => $index,
                                        'name' => $cleanName
                                    ];
                                }
                                $info['interfaces_available'] = $interfacesList;
                                $info['total_interfaces'] = count($interfacesList);
                            }
                        } catch (\Exception $e) {
                            $info['interfaces_error'] = $e->getMessage();
                        }
                    } else {
                        $info['test_success'] = false;
                        $info['test_message'] = 'No se pudo conectar al router por SNMP';
                        $lastError = error_get_last();
                        if ($lastError) {
                            $info['test_error_detail'] = $lastError['message'];
                        }
                    }
                } catch (\Exception $e) {
                    $info['test_success'] = false;
                    $info['test_error'] = $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'info' => $info,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getSnmpInterfaceInfo(Router $router, string $interfaceName, SnmpService $snmpService)
    {
        $this->authorize('view', $router);
        try {
            $this->logDebug('getSnmpInterfaceInfo llamado', [
                'router_id' => $router->id,
                'router_nombre' => $router->nombre,
                'interface_name' => $interfaceName,
                'puerto_snmp' => $router->puerto_snmp,
                'tiene_comunidad' => !empty($router->comunidad),
            ]);

            if (empty($router->puerto_snmp) || empty($router->comunidad)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Router no tiene configuración SNMP (puerto o comunidad faltante)',
                ], 400);
            }

            $host = $router->ip_url;
            $community = $router->comunidad;
            $port = $router->puerto_snmp ?? 161;
            $timeout = 5;
            $retries = 2;

            // Decodificar el nombre de la interfaz (puede venir URL encoded)
            $interfaceName = urldecode($interfaceName);

            $info = [
                'router' => [
                    'id' => $router->id,
                    'nombre' => $router->nombre,
                    'ip_url' => $host,
                    'puerto_snmp' => $port,
                ],
                'interface_buscada' => $interfaceName,
                'interface_buscada_decoded' => $interfaceName,
                'snmp_available' => $snmpService->isAvailable(),
            ];

            $this->logDebug('Buscando interfaz SNMP', [
                'router_id' => $router->id,
                'interface_name' => $interfaceName,
                'host' => $host,
                'port' => $port
            ]);

            if (!$info['snmp_available']) {
                return response()->json([
                    'success' => false,
                    'message' => 'SNMP no está disponible en este servidor',
                    'info' => $info,
                ], 503);
            }

            // Obtener índice de la interfaz usando snmpwalk directamente
            $oid = '1.3.6.1.2.1.2.2.1.2';
            $interfaces = @snmpwalk($host . ':' . $port, $community, $oid, $timeout, $retries);

            $interfaceIndex = null;
            $interfaceNameClean = str_replace('pppoe-', '', $interfaceName);
            $interfaceNameLower = strtolower($interfaceNameClean);

            // Variantes del nombre a buscar (más exhaustivo)
            $searchVariants = [
                $interfaceName,
                $interfaceNameClean,
                str_replace('pppoe-', 'pppoe-out', $interfaceName),
                str_replace('pppoe-', 'pppoe-out-', $interfaceName),
                str_replace('pppoe-', '', $interfaceName),
                strtolower($interfaceName),
                strtolower($interfaceNameClean),
                // Intentar sin guiones
                str_replace('-', '', $interfaceName),
                str_replace('-', '', $interfaceNameClean),
                // Intentar con diferentes formatos
                'pppoe-' . $interfaceNameClean,
                'pppoe-out-' . $interfaceNameClean,
            ];

            // Eliminar duplicados
            $searchVariants = array_unique($searchVariants);

            $this->logDebug('Variantes de búsqueda de interfaz', [
                'interface_name' => $interfaceName,
                'variants' => $searchVariants
            ]);

            if ($interfaces !== false) {
                foreach ($interfaces as $fullOid => $name) {
                    $cleanName = trim($name, '"');
                    $cleanNameLower = strtolower($cleanName);

                    $parts = explode('.', $fullOid);
                    $index = (int)end($parts);

                    // Comparar nombres con todas las variantes (búsqueda más flexible)
                    $matches = false;
                    $matchReason = null;

                    foreach ($searchVariants as $variant) {
                        $variantLower = strtolower($variant);
                        $variantClean = str_replace(['-', '_', ' '], '', $variantLower);
                        $cleanNameClean = str_replace(['-', '_', ' '], '', $cleanNameLower);

                        // Coincidencia exacta
                        if ($cleanName === $variant || $cleanNameLower === $variantLower) {
                            $matches = true;
                            $matchReason = "exacta: '{$variant}'";
                            break;
                        }

                        // Coincidencia parcial (contiene)
                        if (str_contains($cleanName, $variant) || str_contains($cleanNameLower, $variantLower)) {
                            $matches = true;
                            $matchReason = "contiene: '{$variant}'";
                            break;
                        }

                        // Coincidencia inversa (el variant contiene el nombre)
                        if (str_contains($variant, $cleanName) || str_contains($variantLower, $cleanNameLower)) {
                            $matches = true;
                            $matchReason = "inversa: '{$variant}' contiene '{$cleanName}'";
                            break;
                        }

                        // Coincidencia sin caracteres especiales
                        if ($cleanNameClean === $variantClean) {
                            $matches = true;
                            $matchReason = "sin caracteres especiales: '{$variant}'";
                            break;
                        }
                    }

                    if ($matches) {
                        $interfaceIndex = $index;
                        $this->logDebug('Interfaz encontrada en SNMP', [
                            'interface_buscada' => $interfaceName,
                            'interface_encontrada' => $cleanName,
                            'index' => $index,
                            'match_reason' => $matchReason,
                            'full_oid' => $fullOid
                        ]);
                        break;
                    }
                }
            }

            if ($interfaceIndex === null) {
                // Si ya tenemos las interfaces del primer snmpwalk, usarlas; si no, obtenerlas
                if ($interfaces === false || empty($interfaces)) {
                    $oid = '1.3.6.1.2.1.2.2.1.2';
                    $interfaces = @snmpwalk($host . ':' . $port, $community, $oid, $timeout, $retries);
                }

                $interfacesList = [];
                $pppoeInterfaces = [];

                if ($interfaces !== false && !empty($interfaces)) {
                    foreach ($interfaces as $fullOid => $name) {
                        $cleanName = trim($name, '"');
                        $cleanNameLower = strtolower($cleanName);
                        $parts = explode('.', $fullOid);
                        $index = (int)end($parts);

                        // Verificar si es una interfaz PPPoE
                        $isPppoe = str_contains($cleanNameLower, 'pppoe');

                        $interfaceData = [
                            'index' => $index,
                            'name' => $cleanName,
                            'matches' => false,
                            'is_pppoe' => $isPppoe,
                        ];

                        // Verificar coincidencias con todas las variantes (usando la misma lógica de búsqueda)
                        foreach ($searchVariants as $variant) {
                            $variantLower = strtolower($variant);
                            $variantClean = str_replace(['-', '_', ' '], '', $variantLower);
                            $cleanNameClean = str_replace(['-', '_', ' '], '', $cleanNameLower);

                            if (
                                $cleanName === $variant ||
                                $cleanNameLower === $variantLower ||
                                stripos($cleanName, $variant) !== false ||
                                stripos($variant, $cleanName) !== false ||
                                stripos($cleanNameLower, $variantLower) !== false ||
                                stripos($variantLower, $cleanNameLower) !== false ||
                                $cleanNameClean === $variantClean
                            ) {
                                $interfaceData['matches'] = true;
                                break;
                            }
                        }

                        $interfacesList[] = $interfaceData;

                        // Si es PPPoE, agregarlo a la lista de PPPoE
                        if ($isPppoe) {
                            $pppoeInterfaces[] = $interfaceData;
                        }
                    }
                } else {
                    // Si snmpwalk falló, intentar diagnosticar
                    $lastError = error_get_last();
                    Log::warning('snmpwalk falló al obtener interfaces para mostrar disponibles', [
                        'host' => $host,
                        'port' => $port,
                        'last_error' => $lastError,
                        'suggestion' => 'Verifica que SNMP esté habilitado en el router y que la comunidad sea correcta'
                    ]);
                }

                // Ordenar: primero las que coinciden, luego las PPPoE
                usort($interfacesList, function ($a, $b) {
                    if ($a['matches'] && !$b['matches']) return -1;
                    if (!$a['matches'] && $b['matches']) return 1;
                    if ($a['is_pppoe'] && !$b['is_pppoe']) return -1;
                    if (!$a['is_pppoe'] && $b['is_pppoe']) return 1;
                    return 0;
                });

                return response()->json([
                    'success' => false,
                    'message' => 'Interfaz no encontrada en SNMP',
                    'interface_buscada' => $interfaceName,
                    'variantes_buscadas' => $searchVariants,
                    'interfaces_disponibles' => $interfacesList,
                    'interfaces_pppoe' => $pppoeInterfaces,
                    'total_interfaces' => count($interfacesList),
                    'total_pppoe' => count($pppoeInterfaces),
                    'suggestion' => 'Verifica que el nombre de la interfaz en SNMP coincida. Las interfaces PPPoE disponibles se muestran arriba.',
                ], 404);
            }

            $info['interface_index'] = $interfaceIndex;

            // Obtener información completa de la interfaz
            $interfaceInfo = [];

            // Función helper para extraer valor numérico
            $extractNumeric = function ($value) {
                if ($value === false || $value === null || $value === '') return null;

                // Si ya es numérico, retornarlo
                if (is_numeric($value)) {
                    return (int)$value;
                }

                // Intentar extraer número de la cadena
                $valueStr = (string)$value;

                // Limpiar comillas y espacios
                $valueStr = trim($valueStr, '"\' ');

                // Remover prefijos comunes de SNMP (STRING:, INTEGER:, Counter32:, Gauge32:, etc.)
                $valueStr = preg_replace('/^(STRING|INTEGER|Counter32|Counter64|Gauge32|Gauge64|Timeticks|OCTET STRING|Hex-STRING):\s*/i', '', $valueStr);
                $valueStr = trim($valueStr, '"\' ');

                // Si después de limpiar es numérico, retornarlo
                if (is_numeric($valueStr)) {
                    return (int)$valueStr;
                }

                // Buscar números en la cadena (puede venir como "INTEGER: 123" o "Counter32: 123")
                if (preg_match('/(\d+)/', $valueStr, $matches)) {
                    return (int)$matches[1];
                }

                return null;
            };

            // Función helper para obtener y limpiar valor SNMP
            $getSnmpValue = function ($oid) use ($host, $port, $community, $timeout, $retries) {
                $result = @snmpget($host . ':' . $port, $community, $oid, $timeout, $retries);

                if ($result === false) {
                    $lastError = error_get_last();
                    $this->logDebug('snmpget falló', [
                        'oid' => $oid,
                        'host' => $host,
                        'port' => $port,
                        'error' => $lastError
                    ]);
                    return null;
                }

                // Limpiar el resultado: remover prefijos SNMP y comillas
                $cleaned = (string)$result;

                // Remover prefijos comunes (STRING:, INTEGER:, etc.)
                $cleaned = preg_replace('/^(STRING|INTEGER|Counter32|Counter64|Gauge32|Gauge64|Timeticks|OCTET STRING|Hex-STRING):\s*/i', '', $cleaned);

                // Limpiar comillas y espacios
                $cleaned = trim($cleaned, '"\' ');

                $this->logDebug('snmpget resultado', [
                    'oid' => $oid,
                    'raw' => $result,
                    'cleaned' => $cleaned
                ]);

                return $cleaned;
            };

            // 1. Información básica
            $oidName = "1.3.6.1.2.1.2.2.1.2.{$interfaceIndex}"; // ifDescr / ifName
            $oidType = "1.3.6.1.2.1.2.2.1.3.{$interfaceIndex}"; // ifType
            $oidMtu = "1.3.6.1.2.1.2.2.1.4.{$interfaceIndex}"; // ifMtu
            $oidSpeed = "1.3.6.1.2.1.2.2.1.5.{$interfaceIndex}"; // ifSpeed
            $oidAdminStatus = "1.3.6.1.2.1.2.2.1.7.{$interfaceIndex}"; // ifAdminStatus
            $oidOperStatus = "1.3.6.1.2.1.2.2.1.8.{$interfaceIndex}"; // ifOperStatus

            // Obtener valores básicos
            $nameValue = $getSnmpValue($oidName);
            $typeValue = $getSnmpValue($oidType);
            $mtuValue = $getSnmpValue($oidMtu);
            $speedValue = $getSnmpValue($oidSpeed);
            $adminStatusValue = $getSnmpValue($oidAdminStatus);
            $operStatusValue = $getSnmpValue($oidOperStatus);

            // Limpiar el nombre de la interfaz (puede venir con prefijo STRING:)
            $nameClean = $nameValue;
            if ($nameValue && stripos($nameValue, 'STRING:') === 0) {
                $nameClean = preg_replace('/^STRING:\s*/i', '', $nameValue);
                $nameClean = trim($nameClean, '"\' ');
            }

            $interfaceInfo['basica'] = [
                'index' => $interfaceIndex,
                'name' => $nameClean ?: $nameValue,
                'name_raw' => $nameValue,
                'type' => $extractNumeric($typeValue),
                'type_raw' => $typeValue,
                'mtu' => $extractNumeric($mtuValue),
                'mtu_raw' => $mtuValue,
                'speed' => $extractNumeric($speedValue),
                'speed_raw' => $speedValue,
                'admin_status' => $extractNumeric($adminStatusValue),
                'admin_status_raw' => $adminStatusValue,
                'oper_status' => $extractNumeric($operStatusValue),
                'oper_status_raw' => $operStatusValue,
                'admin_status_text' => $this->getIfAdminStatusText($extractNumeric($adminStatusValue)),
                'oper_status_text' => $this->getIfOperStatusText($extractNumeric($operStatusValue)),
            ];

            // 2. Estadísticas de tráfico acumulado
            $oidInOctets = "1.3.6.1.2.1.2.2.1.10.{$interfaceIndex}"; // ifInOctets
            $oidOutOctets = "1.3.6.1.2.1.2.2.1.16.{$interfaceIndex}"; // ifOutOctets
            $oidInPackets = "1.3.6.1.2.1.2.2.1.11.{$interfaceIndex}"; // ifInUcastPkts
            $oidOutPackets = "1.3.6.1.2.1.2.2.1.17.{$interfaceIndex}"; // ifOutUcastPkts
            $oidInErrors = "1.3.6.1.2.1.2.2.1.14.{$interfaceIndex}"; // ifInErrors
            $oidOutErrors = "1.3.6.1.2.1.2.2.1.20.{$interfaceIndex}"; // ifOutErrors

            // Obtener valores de tráfico
            $inOctetsValue = $getSnmpValue($oidInOctets);
            $outOctetsValue = $getSnmpValue($oidOutOctets);
            $inPacketsValue = $getSnmpValue($oidInPackets);
            $outPacketsValue = $getSnmpValue($oidOutPackets);
            $inErrorsValue = $getSnmpValue($oidInErrors);
            $outErrorsValue = $getSnmpValue($oidOutErrors);

            $bytesRx = $extractNumeric($inOctetsValue);
            $bytesTx = $extractNumeric($outOctetsValue);

            $interfaceInfo['trafico_acumulado'] = [
                'bytes_recibidos' => $bytesRx,
                'bytes_recibidos_raw' => $inOctetsValue,
                'bytes_recibidos_formatted' => $this->formatBytes($bytesRx),
                'bytes_enviados' => $bytesTx,
                'bytes_enviados_raw' => $outOctetsValue,
                'bytes_enviados_formatted' => $this->formatBytes($bytesTx),
                'paquetes_recibidos' => $extractNumeric($inPacketsValue),
                'paquetes_recibidos_raw' => $inPacketsValue,
                'paquetes_enviados' => $extractNumeric($outPacketsValue),
                'paquetes_enviados_raw' => $outPacketsValue,
                'errores_recibidos' => $extractNumeric($inErrorsValue),
                'errores_recibidos_raw' => $inErrorsValue,
                'errores_enviados' => $extractNumeric($outErrorsValue),
                'errores_enviados_raw' => $outErrorsValue,
            ];

            // 3. Tasas en tiempo real (MikroTik específico)
            $oidRxRate = "1.3.6.1.4.1.14988.1.1.1.1.2.{$interfaceIndex}"; // MikroTik ifInRate
            $oidTxRate = "1.3.6.1.4.1.14988.1.1.1.1.3.{$interfaceIndex}"; // MikroTik ifOutRate

            $rxRateValue = $getSnmpValue($oidRxRate);
            $txRateValue = $getSnmpValue($oidTxRate);
            $rxRate = $extractNumeric($rxRateValue);
            $txRate = $extractNumeric($txRateValue);

            $interfaceInfo['tasas_tiempo_real'] = [
                'rx_rate' => $rxRate,
                'rx_rate_raw' => $rxRateValue,
                'rx_rate_formatted' => $rxRate ? $this->formatSpeed($rxRate) : null,
                'tx_rate' => $txRate,
                'tx_rate_raw' => $txRateValue,
                'tx_rate_formatted' => $txRate ? $this->formatSpeed($txRate) : null,
            ];

            // 4. Intentar obtener tasas usando el servicio
            $snmpRates = $snmpService->getInterfaceTrafficRates($router, $interfaceName);
            if ($snmpRates) {
                $interfaceInfo['tasas_obtenidas_servicio'] = $snmpRates;
            }

            // 5. Información detallada de MikroTik
            $oidMikroTikRxPacket = "1.3.6.1.4.1.14988.1.1.1.1.4.{$interfaceIndex}";
            $oidMikroTikTxPacket = "1.3.6.1.4.1.14988.1.1.1.1.5.{$interfaceIndex}";
            $oidMikroTikRxDrop = "1.3.6.1.4.1.14988.1.1.1.1.6.{$interfaceIndex}";
            $oidMikroTikTxDrop = "1.3.6.1.4.1.14988.1.1.1.1.7.{$interfaceIndex}";

            $rxPacketValue = $getSnmpValue($oidMikroTikRxPacket);
            $txPacketValue = $getSnmpValue($oidMikroTikTxPacket);
            $rxDropValue = $getSnmpValue($oidMikroTikRxDrop);
            $txDropValue = $getSnmpValue($oidMikroTikTxDrop);

            $interfaceInfo['mikrotik_especifico'] = [
                'rx_rate_oid' => $oidRxRate,
                'tx_rate_oid' => $oidTxRate,
                'rx_rate_valor' => $rxRate,
                'rx_rate_valor_raw' => $rxRateValue,
                'tx_rate_valor' => $txRate,
                'tx_rate_valor_raw' => $txRateValue,
                'rx_rate_disponible' => $rxRate !== null,
                'tx_rate_disponible' => $txRate !== null,
                'rx_packets' => $extractNumeric($rxPacketValue),
                'rx_packets_raw' => $rxPacketValue,
                'tx_packets' => $extractNumeric($txPacketValue),
                'tx_packets_raw' => $txPacketValue,
                'rx_drops' => $extractNumeric($rxDropValue),
                'rx_drops_raw' => $rxDropValue,
                'tx_drops' => $extractNumeric($txDropValue),
                'tx_drops_raw' => $txDropValue,
            ];

            // 6. Información adicional de tráfico
            $oidInDiscards = "1.3.6.1.2.1.2.2.1.13.{$interfaceIndex}"; // ifInDiscards
            $oidOutDiscards = "1.3.6.1.2.1.2.2.1.19.{$interfaceIndex}"; // ifOutDiscards
            $oidInUnknownProtos = "1.3.6.1.2.1.2.2.1.15.{$interfaceIndex}"; // ifInUnknownProtos
            $oidLastChange = "1.3.6.1.2.1.2.2.1.9.{$interfaceIndex}"; // ifLastChange

            $interfaceInfo['trafico_adicional'] = [
                'discards_recibidos' => $extractNumeric($getSnmpValue($oidInDiscards)),
                'discards_enviados' => $extractNumeric($getSnmpValue($oidOutDiscards)),
                'protocolos_desconocidos' => $extractNumeric($getSnmpValue($oidInUnknownProtos)),
                'ultimo_cambio' => $extractNumeric($getSnmpValue($oidLastChange)),
                'ultimo_cambio_timestamp' => $extractNumeric($getSnmpValue($oidLastChange)) ? date('Y-m-d H:i:s', $extractNumeric($getSnmpValue($oidLastChange)) / 100) : null,
            ];

            // 7. OIDs específicos de MikroTik para interfaces
            // MikroTik MIB: 1.3.6.1.4.1.14988.1.1.1.1
            $oidMikroTikRxRate = "1.3.6.1.4.1.14988.1.1.1.1.2.{$interfaceIndex}"; // ifInRate
            $oidMikroTikTxRate = "1.3.6.1.4.1.14988.1.1.1.1.3.{$interfaceIndex}"; // ifOutRate
            $oidMikroTikRxPacket = "1.3.6.1.4.1.14988.1.1.1.1.4.{$interfaceIndex}"; // ifInPacket
            $oidMikroTikTxPacket = "1.3.6.1.4.1.14988.1.1.1.1.5.{$interfaceIndex}"; // ifOutPacket
            $oidMikroTikRxDrop = "1.3.6.1.4.1.14988.1.1.1.1.6.{$interfaceIndex}"; // ifInDrop
            $oidMikroTikTxDrop = "1.3.6.1.4.1.14988.1.1.1.1.7.{$interfaceIndex}"; // ifOutDrop

            $interfaceInfo['mikrotik_detallado'] = [
                'rx_rate' => $extractNumeric($getSnmpValue($oidMikroTikRxRate)),
                'rx_rate_formatted' => $extractNumeric($getSnmpValue($oidMikroTikRxRate)) ? $this->formatSpeed($extractNumeric($getSnmpValue($oidMikroTikRxRate))) : null,
                'tx_rate' => $extractNumeric($getSnmpValue($oidMikroTikTxRate)),
                'tx_rate_formatted' => $extractNumeric($getSnmpValue($oidMikroTikTxRate)) ? $this->formatSpeed($extractNumeric($getSnmpValue($oidMikroTikTxRate))) : null,
                'rx_packets' => $extractNumeric($getSnmpValue($oidMikroTikRxPacket)),
                'tx_packets' => $extractNumeric($getSnmpValue($oidMikroTikTxPacket)),
                'rx_drops' => $extractNumeric($getSnmpValue($oidMikroTikRxDrop)),
                'tx_drops' => $extractNumeric($getSnmpValue($oidMikroTikTxDrop)),
            ];

            // 8. OIDs relacionados con PPPoE específicamente
            // Nota: MikroTik no tiene OIDs específicos de PPPoE, las interfaces PPPoE se manejan como interfaces normales
            // La información de sesión PPPoE se obtiene vía RouterOS API, no SNMP
            // Aquí mostramos la información disponible por SNMP para esta interfaz
            $pppoeName = $interfaceInfo['basica']['name'] ?? null;
            $isPppoeInterface = $pppoeName && (stripos($pppoeName, 'pppoe') !== false);

            $interfaceInfo['pppoe_info'] = [
                'es_interfaz_pppoe' => $isPppoeInterface,
                'interface_name' => $pppoeName,
                'interface_index' => $interfaceIndex,
                'nota' => $isPppoeInterface
                    ? 'Esta es una interfaz PPPoE. La información de sesión (usuario, IP asignada, etc.) se obtiene vía RouterOS API, no SNMP. SNMP solo proporciona estadísticas de tráfico de la interfaz.'
                    : 'Esta interfaz no parece ser PPPoE. Los OIDs estándar IF-MIB se usan para todas las interfaces.',
                'informacion_disponible' => [
                    'trafico_acumulado' => 'Bytes y paquetes recibidos/enviados (OIDs estándar IF-MIB)',
                    'tasas_tiempo_real' => 'Velocidades RX/TX en tiempo real (OIDs específicos MikroTik)',
                    'estadisticas_detalladas' => 'Errores, descartes, drops (OIDs estándar IF-MIB y MikroTik)',
                ],
                'informacion_no_disponible' => [
                    'sesion_pppoe' => 'Usuario, IP asignada, caller-id, uptime de sesión (solo vía RouterOS API)',
                    'autenticacion' => 'Método de autenticación, perfil PPPoE (solo vía RouterOS API)',
                ],
            ];

            // 9. Información del sistema del router (contexto)
            $oidSysDescr = '1.3.6.1.2.1.1.1.0'; // sysDescr
            $oidSysUpTime = '1.3.6.1.2.1.1.3.0'; // sysUpTime
            $oidSysName = '1.3.6.1.2.1.1.5.0'; // sysName
            $oidSysLocation = '1.3.6.1.2.1.1.6.0'; // sysLocation

            $interfaceInfo['router_sistema'] = [
                'descripcion' => $getSnmpValue($oidSysDescr),
                'uptime' => $extractNumeric($getSnmpValue($oidSysUpTime)),
                'uptime_formatted' => $extractNumeric($getSnmpValue($oidSysUpTime)) ? $this->formatUptime($extractNumeric($getSnmpValue($oidSysUpTime)) / 100) : null,
                'nombre' => $getSnmpValue($oidSysName),
                'ubicacion' => $getSnmpValue($oidSysLocation),
            ];

            // 10. Todos los OIDs consultados (lista completa)
            $interfaceInfo['oids_consultados'] = [
                'basica' => [
                    'name' => ['oid' => $oidName, 'descripcion' => 'ifDescr - Nombre de la interfaz'],
                    'type' => ['oid' => $oidType, 'descripcion' => 'ifType - Tipo de interfaz'],
                    'mtu' => ['oid' => $oidMtu, 'descripcion' => 'ifMtu - MTU de la interfaz'],
                    'speed' => ['oid' => $oidSpeed, 'descripcion' => 'ifSpeed - Velocidad de la interfaz'],
                    'admin_status' => ['oid' => $oidAdminStatus, 'descripcion' => 'ifAdminStatus - Estado administrativo'],
                    'oper_status' => ['oid' => $oidOperStatus, 'descripcion' => 'ifOperStatus - Estado operativo'],
                    'last_change' => ['oid' => $oidLastChange, 'descripcion' => 'ifLastChange - Último cambio de estado'],
                ],
                'trafico_estandar' => [
                    'in_octets' => ['oid' => $oidInOctets, 'descripcion' => 'ifInOctets - Bytes recibidos acumulados'],
                    'out_octets' => ['oid' => $oidOutOctets, 'descripcion' => 'ifOutOctets - Bytes enviados acumulados'],
                    'in_packets' => ['oid' => $oidInPackets, 'descripcion' => 'ifInUcastPkts - Paquetes recibidos'],
                    'out_packets' => ['oid' => $oidOutPackets, 'descripcion' => 'ifOutUcastPkts - Paquetes enviados'],
                    'in_errors' => ['oid' => $oidInErrors, 'descripcion' => 'ifInErrors - Errores recibidos'],
                    'out_errors' => ['oid' => $oidOutErrors, 'descripcion' => 'ifOutErrors - Errores enviados'],
                    'in_discards' => ['oid' => $oidInDiscards, 'descripcion' => 'ifInDiscards - Paquetes descartados recibidos'],
                    'out_discards' => ['oid' => $oidOutDiscards, 'descripcion' => 'ifOutDiscards - Paquetes descartados enviados'],
                    'in_unknown_protos' => ['oid' => $oidInUnknownProtos, 'descripcion' => 'ifInUnknownProtos - Protocolos desconocidos'],
                ],
                'tasas_mikrotik' => [
                    'rx_rate' => ['oid' => $oidMikroTikRxRate, 'descripcion' => 'MikroTik ifInRate - Velocidad de recepción en tiempo real'],
                    'tx_rate' => ['oid' => $oidMikroTikTxRate, 'descripcion' => 'MikroTik ifOutRate - Velocidad de transmisión en tiempo real'],
                    'rx_packet' => ['oid' => $oidMikroTikRxPacket, 'descripcion' => 'MikroTik ifInPacket - Paquetes recibidos por segundo'],
                    'tx_packet' => ['oid' => $oidMikroTikTxPacket, 'descripcion' => 'MikroTik ifOutPacket - Paquetes enviados por segundo'],
                    'rx_drop' => ['oid' => $oidMikroTikRxDrop, 'descripcion' => 'MikroTik ifInDrop - Paquetes descartados recibidos'],
                    'tx_drop' => ['oid' => $oidMikroTikTxDrop, 'descripcion' => 'MikroTik ifOutDrop - Paquetes descartados enviados'],
                ],
                'sistema' => [
                    'sys_descr' => ['oid' => $oidSysDescr, 'descripcion' => 'sysDescr - Descripción del sistema'],
                    'sys_uptime' => ['oid' => $oidSysUpTime, 'descripcion' => 'sysUpTime - Tiempo de actividad del sistema'],
                    'sys_name' => ['oid' => $oidSysName, 'descripcion' => 'sysName - Nombre del sistema'],
                    'sys_location' => ['oid' => $oidSysLocation, 'descripcion' => 'sysLocation - Ubicación del sistema'],
                ],
            ];

            $info['interface_info'] = $interfaceInfo;

            return response()->json([
                'success' => true,
                'info' => $info,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    private function getIfAdminStatusText(?int $status): string
    {
        return match ($status) {
            1 => 'up',
            2 => 'down',
            3 => 'testing',
            default => 'unknown',
        };
    }

    private function getIfOperStatusText(?int $status): string
    {
        return match ($status) {
            1 => 'up',
            2 => 'down',
            3 => 'testing',
            4 => 'unknown',
            5 => 'dormant',
            6 => 'notPresent',
            7 => 'lowerLayerDown',
            default => 'unknown',
        };
    }

    private function formatBytes(?int $bytes): ?string
    {
        if ($bytes === null) return null;

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    private function formatSpeed(?int $bytesPerSecond): ?string
    {
        if ($bytesPerSecond === null) return null;

        $bitsPerSecond = $bytesPerSecond * 8;

        if ($bitsPerSecond >= 1000000000) {
            return round($bitsPerSecond / 1000000000, 2) . ' Gbps';
        } elseif ($bitsPerSecond >= 1000000) {
            return round($bitsPerSecond / 1000000, 2) . ' Mbps';
        } elseif ($bitsPerSecond >= 1000) {
            return round($bitsPerSecond / 1000, 2) . ' Kbps';
        } else {
            return $bitsPerSecond . ' bps';
        }
    }

    private function formatUptime(int $centiseconds): string
    {
        $seconds = $centiseconds / 100;
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        $parts = [];
        if ($days > 0) $parts[] = $days . 'd';
        if ($hours > 0) $parts[] = $hours . 'h';
        if ($minutes > 0) $parts[] = $minutes . 'm';
        if ($secs > 0 || empty($parts)) $parts[] = round($secs) . 's';

        return implode(' ', $parts);
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $context);
        }
    }

    private function asegurarReglaCorte(Router $router): void
    {
        $reglaExistente = \App\Modules\Red\Models\Regla::where('router_id', $router->id)
            ->where('nombre', 'Regla de corte de servicio')
            ->first();

        if (!$reglaExistente) {
            \App\Modules\Red\Models\Regla::create([
                'router_id' => $router->id,
                'nombre' => 'Regla de corte de servicio',
                'tipo' => 'firewall',
                'configuracion' => [
                    'source_address_list' => 'CORTE',
                    'chain' => 'forward',
                    'comment' => 'Regla de corte creado desde Admin ISP',
                    'disabled' => false,
                ],
                'activo' => true,
                'exportado' => false,
                'notas' => null,
            ]);
        } else {
            $configuracion = $reglaExistente->configuracion;
            if (($configuracion['comment'] ?? '') !== 'Regla de corte creado desde Admin ISP') {
                $configuracion['comment'] = 'Regla de corte creado desde Admin ISP';
                $reglaExistente->update(['configuracion' => $configuracion]);
            }
        }
    }
}
