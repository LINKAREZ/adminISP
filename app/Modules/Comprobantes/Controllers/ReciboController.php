<?php

namespace App\Modules\Comprobantes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Comprobantes\Requests\StoreReciboRequest;
use App\Modules\Comprobantes\Requests\UpdateReciboRequest;
use App\Modules\Comprobantes\Models\Recibo;
use App\Modules\Comprobantes\Services\ReciboService;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\Servicios\Models\Servicio;
use App\Core\Traits\RespondsWithJson;
use App\Core\Traits\ValidatesDebtOperations;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ReciboController extends Controller
{
    use RespondsWithJson, ValidatesDebtOperations;

    public function __construct(
        private ReciboService $reciboService
    ) {}

    /**
     * Obtener servicios activos del cliente para el formulario
     * Con caché para mejorar rendimiento
     */
    private function obtenerServiciosActivosFormateados(Cliente $cliente): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            "cliente.{$cliente->id}.servicios.activos",
            now()->addMinutes(5), // Cache por 5 minutos
            function () use ($cliente) {
                return $cliente->servicios()
                    ->with(['plan:id,nombre,precio_mensual', 'ubicacion:id,direccion'])
                    ->where('servicios.estado', 'activo')
                    ->get();
            }
        );
    }

    private function obtenerTodosLosServicios(Cliente $cliente): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            "cliente.{$cliente->id}.servicios.todos",
            now()->addMinutes(5), // Cache por 5 minutos
            function () use ($cliente) {
                return $cliente->servicios()
                    ->with(['plan:id,nombre,precio_mensual', 'ubicacion:id,direccion'])
                    ->orderBy('servicios.estado', 'desc') // Activos primero
                    ->orderBy('servicios.created_at', 'desc')
                    ->get();
            }
        );
    }

    public function create(Cliente $cliente)
    {
        if (!\App\Core\Services\TenantConnectionService::currentTenantConnectionName()) {
            return redirect()->route('dashboard')->with('warning', 'No hay ISP configurado. Seleccione un ISP para crear recibos.');
        }
        $this->authorize('create', Recibo::class);
        // Cargar TODOS los servicios del cliente (activos e inactivos)
        $servicios = $this->obtenerTodosLosServicios($cliente);

        $servicioInicial = request()->input('servicio_id');
        $montoInicial = 0;

        if ($servicioInicial) {
            // Buscar el servicio en la colección
            $servicioEncontrado = $servicios->firstWhere('id', $servicioInicial);
            if ($servicioEncontrado && $servicioEncontrado->plan) {
                $montoInicial = $servicioEncontrado->plan->precio_mensual ?? 0;
            }
        } elseif ($servicios->count() === 1) {
            // Si solo hay un servicio, seleccionarlo automáticamente
            $primerServicio = $servicios->first();
            if ($primerServicio && $primerServicio->plan) {
                $servicioInicial = $primerServicio->id;
                $montoInicial = $primerServicio->plan->precio_mensual ?? 0;
            }
        }

        return view('clientes.recibos.create', compact(
            'cliente',
            'servicios',
            'servicioInicial',
            'montoInicial'
        ));
    }

    public function show(Cliente $cliente, Recibo $recibo)
    {
        $this->authorize('view', $recibo);
        // Cargar relaciones necesarias
        $recibo->load([
            'servicio.plan',
            'servicio.ubicacion',
            'pagos.medioPago',
            'pagos.registradoPor',
            'promesasPago'
        ]);

        return view('clientes.recibos.show', compact('cliente', 'recibo'));
    }

    public function edit(Cliente $cliente, Recibo $recibo)
    {
        $this->authorize('update', $recibo);
        $servicios = $this->obtenerServiciosActivosFormateados($cliente);

        return view('clientes.recibos.edit', compact('cliente', 'recibo', 'servicios'));
    }

    public function store(StoreReciboRequest $request, Cliente $cliente)
    {
        $this->authorize('create', Recibo::class);
        try {
            $validated = $request->validated();

            // Usar servicio para crear recibo (centraliza lógica y valores por defecto)
            $recibo = DB::transaction(function () use ($validated, $cliente) {
                return $this->reciboService->crearRecibo($validated, $cliente);
            });

            // Invalidar cachés relacionados
            Cache::forget("cliente.{$cliente->id}.servicios.activos.formateados");
            if (isset($validated['servicio_id'])) {
                Cache::forget("servicio.{$validated['servicio_id']}.recibos.formateados");
            }

            $this->logDebug('Recibo creado correctamente', [
                'recibo_id' => $recibo->id,
                'cliente_id' => $cliente->id,
                'servicio_id' => $validated['servicio_id'] ?? null,
                'periodo' => $validated['periodo'],
                'monto' => $validated['monto']
            ]);

            return redirect()
                ->route('clientes.show', $cliente)
                ->with('success', 'Recibo creado correctamente.')
                ->with('active_tab', 'pagos');
        } catch (\Exception $e) {
            Log::error('Error al crear recibo', [
                'cliente_id' => $cliente->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->withInput()
                ->with('error', 'Error al crear el recibo: ' . $e->getMessage());
        }
    }

    public function update(UpdateReciboRequest $request, Cliente $cliente, Recibo $recibo)
    {
        $this->authorize('update', $recibo);
        $validacion = $this->validateDebtForEdit($recibo, $cliente);
        if ($validacion) {
            return $validacion;
        }

        try {
            $validated = $request->validated();

            // Usar transacción para garantizar consistencia
            DB::transaction(function () use ($validated, $recibo) {
                // Usar servicio para actualizar (centraliza lógica y recalcula estado)
                $this->reciboService->actualizarRecibo($recibo, $validated);
            });

            // Invalidar cachés relacionados
            Cache::forget("cliente.{$cliente->id}.servicios.activos.formateados");
            if ($recibo->servicio_id) {
                Cache::forget("servicio.{$recibo->servicio_id}.recibos.formateados");
            }

            $this->logDebug('Recibo actualizado correctamente', [
                'recibo_id' => $recibo->id,
                'cliente_id' => $cliente->id,
                'cambios' => $recibo->getChanges()
            ]);

            return redirect()
                ->route('clientes.show', $cliente)
                ->with('success', 'Recibo actualizado correctamente.')
                ->with('active_tab', 'pagos');
        } catch (\Exception $e) {
            Log::error('Error al actualizar recibo', [
                'recibo_id' => $recibo->id ?? null,
                'cliente_id' => $cliente->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el recibo: ' . $e->getMessage());
        }
    }

    public function destroy(Recibo $recibo)
    {
        $this->authorize('delete', $recibo);
        if ($recibo->pagos()->exists()) {
            Log::warning('Intento de eliminar recibo con pagos registrados', [
                'recibo_id' => $recibo->id,
                'cliente_id' => $recibo->cliente_id,
                'pagos_count' => $recibo->pagos()->count()
            ]);
            return back()
                ->with('error', 'No se puede eliminar el recibo porque tiene pagos registrados.');
        }

        $clienteId = $recibo->cliente_id;
        $servicioId = $recibo->servicio_id;
        $reciboData = [
            'id' => $recibo->id,
            'periodo' => $recibo->periodo,
            'monto' => $recibo->monto,
        ];

        $recibo->delete();

        // Invalidar cachés relacionados
        Cache::forget("cliente.{$clienteId}.servicios.activos.formateados");
        if ($servicioId) {
            Cache::forget("servicio.{$servicioId}.recibos.formateados");
        }

        $this->logDebug('Recibo eliminado correctamente', [
            'recibo_id' => $reciboData['id'],
            'cliente_id' => $clienteId,
            'periodo' => $reciboData['periodo']
        ]);

        return redirect()
            ->route('clientes.show', $clienteId)
            ->with('success', 'Recibo eliminado correctamente.')
            ->with('active_tab', 'pagos');
    }

    public function getRecibosByServicio(Servicio $servicio)
    {
        $this->authorize('view', $servicio);
        // Cachear resultados por 2 minutos para mejorar rendimiento
        $recibos = Cache::remember(
            "servicio.{$servicio->id}.recibos.formateados",
            now()->addMinutes(2),
            function () use ($servicio) {
                return $servicio->recibos()
                    ->select(['id', 'periodo', 'monto', 'saldo', 'estado', 'fecha_vencimiento', 'servicio_id'])
                    ->withCount(['pagos', 'promesasPago'])
                    ->orderBy('periodo', 'desc')
                    ->get()
                    ->map(function ($recibo) {
                        return [
                            'id' => $recibo->id,
                            'periodo' => $recibo->periodo,
                            'monto' => $recibo->monto,
                            'saldo' => $recibo->saldo,
                            'estado' => $recibo->estado,
                            'fecha_vencimiento' => $recibo->fecha_vencimiento->format('Y-m-d'),
                            'pagos_count' => $recibo->pagos_count,
                            'promesas_count' => $recibo->promesas_pago_count,
                        ];
                    });
            }
        );

        return response()->json([
            'success' => true,
            'recibos' => $recibos
        ]);
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $context);
        }
    }

    /**
     * Obtener recibos de un servicio por servicio_id (para formularios)
     */
    public function getRecibosByServicioId(Cliente $cliente, Request $request)
    {
        $this->authorize('viewAny', Recibo::class);
        $servicioId = $request->input('servicio_id');

        // Si no se proporciona servicio_id, obtener todos los recibos del cliente
        if (!$servicioId) {
            $recibos = Recibo::where('cliente_id', $cliente->id)
                ->whereIn('estado', ['pendiente', 'vencido'])
                ->with(['servicio:id,mac_address', 'servicio.plan:id,nombre'])
                ->select(['id', 'codigo', 'periodo', 'monto', 'saldo', 'estado', 'fecha_vencimiento', 'servicio_id'])
                ->orderBy('periodo', 'desc')
                ->get()
                ->map(function ($recibo) {
                    $servicioInfo = '';
                    if ($recibo->servicio) {
                        $servicioInfo = $recibo->servicio->mac_address;
                        if ($recibo->servicio && $recibo->servicio->plan) {
                            $servicioInfo .= ' - ' . $recibo->servicio->plan->nombre;
                        }
                    }
                    return [
                        'id' => $recibo->id,
                        'codigo' => $recibo->codigo,
                        'periodo' => $recibo->periodo,
                        'monto' => $recibo->monto,
                        'saldo' => $recibo->saldo,
                        'estado' => $recibo->estado,
                        'fecha_vencimiento' => $recibo->fecha_vencimiento->format('Y-m-d'),
                        'servicio_info' => $servicioInfo,
                    ];
                });

            return response()->json([
                'success' => true,
                'recibos' => $recibos
            ]);
        }

        try {
            $servicio = Servicio::with('ubicacion')->findOrFail($servicioId);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio no encontrado o no pertenece al cliente',
                'recibos' => []
            ], 404);
        }

        if (!$servicio->ubicacion || $servicio->ubicacion->cliente_id !== $cliente->id) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio no encontrado o no pertenece al cliente',
                'recibos' => []
            ], 404);
        }

        // Obtener recibos pendientes y vencidos del servicio
        $recibos = $servicio->recibos()
            ->whereIn('estado', ['pendiente', 'vencido'])
            ->select(['id', 'codigo', 'periodo', 'monto', 'saldo', 'estado', 'fecha_vencimiento', 'servicio_id'])
            ->orderBy('periodo', 'desc')
            ->get()
            ->map(function ($recibo) {
                return [
                    'id' => $recibo->id,
                    'codigo' => $recibo->codigo,
                    'periodo' => $recibo->periodo,
                    'monto' => $recibo->monto,
                    'saldo' => $recibo->saldo,
                    'estado' => $recibo->estado,
                    'fecha_vencimiento' => $recibo->fecha_vencimiento->format('Y-m-d'),
                ];
            });

        return response()->json([
            'success' => true,
            'recibos' => $recibos
        ]);
    }
}
