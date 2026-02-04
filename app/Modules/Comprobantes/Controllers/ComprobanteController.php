<?php

namespace App\Modules\Comprobantes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Comprobantes\Models\Comprobante;
use App\Modules\Comprobantes\Models\Pago;
use App\Modules\Comprobantes\Models\SerieComprobante;
use App\Modules\Comprobantes\Services\ComprobanteService;
use App\Modules\Comprobantes\Requests\StoreComprobanteRequest;
use App\Modules\Comprobantes\Requests\UpdateComprobanteRequest;
use App\Modules\Comprobantes\Requests\AnularComprobanteRequest;
use App\Modules\Comprobantes\Requests\GenerarMasivosRequest;
use App\Modules\Comprobantes\Requests\EliminarMasivosRequest;
use App\Modules\Clientes\Models\Cliente;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ComprobanteController extends Controller
{
    public function __construct(
        private ComprobanteService $comprobanteService
    ) {}
    /**
     * Listar todos los comprobantes
     */
    public function index(Request $request)
    {
        $request->validate([
            'tipo' => ['sometimes', 'string', 'max:20'],
            'serie' => ['sometimes', 'string', 'max:10'],
            'cliente_id' => ['sometimes', 'integer', 'exists:clientes,id'],
            'numero_completo' => ['sometimes', 'string', 'max:30'],
            'fecha_desde' => ['sometimes', 'date'],
            'fecha_hasta' => ['sometimes', 'date'],
        ]);

        // Cargar comprobantes con relaciones (incluyendo recibos sin pago)
        $query = Comprobante::with(['pago.cliente', 'cliente', 'generadoPor']);

        // Filtros
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('serie')) {
            $query->where('serie', $request->serie);
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('numero_completo')) {
            $query->where('numero_completo', 'like', '%' . $request->numero_completo . '%');
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha_emision', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_emision', '<=', $request->fecha_hasta);
        }

        if (config('app.debug')) {
            Log::debug('Listando comprobantes', [
                'filtros' => $request->only([
                    'tipo',
                    'serie',
                    'cliente_id',
                    'numero_completo',
                    'fecha_desde',
                    'fecha_hasta',
                ]),
            ]);
        }

        $comprobantes = $query->orderBy('fecha_emision', 'desc')
            ->orderBy('numero', 'desc')
            ->paginate(25);

        $clientes = $this->obtenerClientesParaSelect();

        return view('comprobantes.comprobantes.index', compact('comprobantes', 'clientes'));
    }

    /**
     * Formulario para crear comprobante manual
     */
    public function create()
    {
        $clientes = $this->obtenerClientesParaSelect();
        $series = $this->obtenerSeriesActivas();

        return view('comprobantes.comprobantes.create', compact('clientes', 'series'));
    }

    /**
     * Guardar comprobante manual
     */
    public function store(StoreComprobanteRequest $request)
    {
        try {
            // Preparar datos
            $datos = $request->validated();

            // Calcular período
            if ($request->filled('mes') && $request->filled('ano')) {
                $datos['periodo_servicio'] = $request->ano . '-' . $request->mes;
            }

            $comprobante = $this->comprobanteService->crearManual($datos);

            $mensaje = "Comprobante {$comprobante->numero_completo} creado correctamente.";

            // Si se solicita ver PDF
            if ($request->input('action') === 'save_and_pdf') {
                return redirect()->route('comprobantes.ver', $comprobante)
                    ->with('success', $mensaje);
            }

            return redirect()->route('comprobantes.index')
                ->with('success', $mensaje);
        } catch (\Exception $e) {
            $context = ['error' => $e->getMessage()];
            if (config('app.debug')) {
                $context['datos'] = $request->validated();
                $context['exception'] = $e;
            }
            Log::error('Error al crear comprobante', $context);

            return back()->withInput()
                ->with('error', 'Error al crear comprobante: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalle de comprobante
     */
    public function show(Comprobante $comprobante)
    {
        $comprobante->load(['cliente', 'pago', 'items', 'generadoPor', 'comprobanteReferencia']);

        return view('comprobantes.comprobantes.show', compact('comprobante'));
    }

    /**
     * Formulario de edición
     */
    public function edit(Comprobante $comprobante)
    {
        if ($comprobante->anulado) {
            return back()->with('error', 'No se puede editar un comprobante anulado.');
        }

        $clientes = $this->obtenerClientesParaSelect();
        $series = $this->obtenerSeriesActivas();

        return view('comprobantes.comprobantes.edit', compact('comprobante', 'clientes', 'series'));
    }

    /**
     * Actualizar comprobante
     */
    public function update(UpdateComprobanteRequest $request, Comprobante $comprobante)
    {
        if ($comprobante->anulado) {
            return back()->with('error', 'No se puede editar un comprobante anulado.');
        }

        try {
            $comprobante->update($request->validated());

            return redirect()->route('comprobantes.show', $comprobante)
                ->with('success', 'Comprobante actualizado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar comprobante', [
                'comprobante_id' => $comprobante->id,
                'error' => $e->getMessage()
            ]);

            return back()->withInput()
                ->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Anular comprobante
     */
    public function anular(AnularComprobanteRequest $request, Comprobante $comprobante)
    {
        try {
            $this->comprobanteService->anular($comprobante, $request->validated()['motivo']);

            return redirect()->route('comprobantes.index')
                ->with('success', "Comprobante {$comprobante->numero_completo} anulado correctamente.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error al anular: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar comprobante (solo si no fue enviado a SUNAT)
     */
    public function destroy(Comprobante $comprobante)
    {
        if ($comprobante->enviado_sunat) {
            return back()->with('error', 'No se puede eliminar un comprobante enviado a SUNAT.');
        }

        try {
            $numero = $comprobante->numero_completo;
            $comprobante->delete();

            return redirect()->route('comprobantes.index')
                ->with('success', "Comprobante {$numero} eliminado correctamente.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    /**
     * Ver listado de series
     */
    public function series()
    {
        $series = Cache::remember('series_comprobantes.todas', 600, function () {
            return SerieComprobante::orderBy('tipo')->orderBy('serie')->get();
        });

        return view('comprobantes.comprobantes.series', compact('series'));
    }

    /**
     * Ver recibo directamente (sin pago)
     */
    public function ver(Comprobante $comprobante)
    {
        try {
            // Cargar relaciones necesarias
            $comprobante->load(['cliente', 'generadoPor']);

            // Generar PDF
            return $this->generarPDFDirecto($comprobante);
        } catch (\Exception $e) {
            Log::error('Error al generar recibo', [
                'comprobante_id' => $comprobante->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Error al generar el recibo: ' . $e->getMessage());
        }
    }

    /**
     * Obtener clientes para select (id + nombre).
     */
    private function obtenerClientesParaSelect()
    {
        $ispId = Auth::user()?->isp_id;
        $cacheKey = 'clientes.select.' . ($ispId ?? 'global');

        return Cache::remember($cacheKey, 600, function () use ($ispId) {
            $query = Cliente::orderBy('nombre')->select(['id', 'nombre']);
            if ($ispId) {
                $query->where('isp_id', $ispId);
            }
            return $query->get();
        });
    }

    private function obtenerSeriesActivas()
    {
        return Cache::remember('series_comprobantes.activas', 600, function () {
            return SerieComprobante::where('activo', true)
                ->orderBy('tipo')
                ->get();
        });
    }

    /**
     * Descargar recibo directamente (sin pago)
     */
    public function descargarRecibo(Comprobante $comprobante)
    {
        try {
            // Cargar relaciones necesarias
            $comprobante->load(['cliente', 'generadoPor']);

            $pdf = $this->generarPDFDirecto($comprobante, true);

            $nombreArchivo = "Recibo-{$comprobante->numero_completo}.pdf";

            return $pdf->download($nombreArchivo);
        } catch (\Exception $e) {
            Log::error('Error al descargar recibo', [
                'comprobante_id' => $comprobante->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error al descargar el recibo: ' . $e->getMessage());
        }
    }

    /**
     * Generar PDF directamente desde comprobante (sin pago)
     */
    private function generarPDFDirecto(Comprobante $comprobante, bool $download = false)
    {
        // Obtener datos de la empresa
        $empresa = [
            'nombre' => config('app.name', 'Admin ISP'),
            'ruc' => config('services.comprobantes.empresa.ruc', ''),
            'direccion' => config('services.comprobantes.empresa.direccion', ''),
            'telefono' => config('services.comprobantes.empresa.telefono', ''),
            'email' => config('services.comprobantes.empresa.email', ''),
        ];

        // Obtener información del servicio si existe
        $servicio = null;
        $periodo = null;

        // Intentar obtener el período desde las notas o la fecha de emisión
        if ($comprobante->notas && preg_match('/período\s+(\d{4}-\d{2})/i', $comprobante->notas, $matches)) {
            $periodo = $matches[1];
        } else {
            $periodo = $comprobante->fecha_emision->format('Y-m');
        }

        // Validar que el comprobante tenga cliente asociado
        if (!$comprobante->cliente) {
            throw new \Exception('El comprobante no tiene cliente asociado.');
        }

        $pdf = Pdf::loadView('comprobantes.comprobante', [
            'comprobante' => $comprobante,
            'pago' => null, // Sin pago
            'cliente' => $comprobante->cliente,
            'empresa' => $empresa,
            'servicio' => $servicio,
            'periodo' => $periodo,
        ]);

        // Configurar opciones del PDF
        // Formato ticket 80mm (226.77 points = 80mm)
        $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('enable-local-file-access', true);
        $pdf->setOption('dpi', 150);

        if ($download) {
            return $pdf;
        }

        // Agregar timestamp para evitar caché del navegador
        $timestamp = time();
        return $pdf->stream("Recibo-{$comprobante->numero_completo}-{$timestamp}.pdf");
    }

    /**
     * Generar y mostrar comprobante en PDF
     */
    public function generar(Pago $pago)
    {
        try {
            // Cargar relaciones necesarias (incluyendo código de recibo y ubicación)
            $pago->load([
                'cliente',
                'recibo.servicio.plan',
                'recibo.servicio.ubicacion',
                'medioPago',
                'registradoPor'
            ]);

            // Verificar si ya existe un comprobante para este pago
            $comprobante = Comprobante::where('pago_id', $pago->id)->first();

            if (!$comprobante) {
                // Generar nuevo comprobante
                $comprobante = $this->crearComprobante($pago);
            }

            // Generar PDF (siempre regenerar para mostrar cambios en la vista)
            return $this->generarPDF($comprobante);
        } catch (\Exception $e) {
            Log::error('Error al generar comprobante', [
                'pago_id' => $pago->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Si hay una página anterior, redirigir con error
            if (url()->previous() !== url()->current()) {
                return back()->with('error', 'Error al generar el comprobante: ' . $e->getMessage());
            }

            // Si no hay página anterior, redirigir al listado de comprobantes
            return redirect()
                ->route('comprobantes.index')
                ->with('error', 'Error al generar el comprobante: ' . $e->getMessage());
        }
    }

    /**
     * Descargar comprobante en PDF
     */
    public function descargar(Pago $pago)
    {
        try {
            // Cargar relaciones necesarias (incluyendo código de recibo)
            $pago->load(['cliente', 'recibo.servicio.plan', 'medioPago', 'registradoPor']);

            $comprobante = Comprobante::where('pago_id', $pago->id)->first();

            if (!$comprobante) {
                $comprobante = $this->crearComprobante($pago);
            }

            $pdf = $this->generarPDF($comprobante, true);

            $nombreArchivo = "Comprobante-{$comprobante->numero_completo}.pdf";

            return $pdf->download($nombreArchivo);
        } catch (\Exception $e) {
            Log::error('Error al descargar comprobante', [
                'pago_id' => $pago->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error al descargar el comprobante: ' . $e->getMessage());
        }
    }

    /**
     * Crear comprobante para un pago
     */
    private function crearComprobante(Pago $pago): Comprobante
    {
        // Validar que el pago tenga cliente asociado
        if (!$pago->cliente) {
            throw new \Exception('El pago no tiene cliente asociado.');
        }

        // Cargar recibo si existe
        if (!$pago->relationLoaded('recibo') && $pago->recibo_id) {
            $pago->load('recibo.servicio.plan');
        }

        // Solo generar recibos (documentos internos)
        $tipoComprobante = Comprobante::TIPO_RECIBO;

        // Obtener serie desde configuración (por defecto R001)
        $serie = 'R001';

        // Obtener siguiente número
        $numero = Comprobante::obtenerSiguienteNumero($tipoComprobante, $serie);

        // Calcular montos (para recibos internos, generalmente exonerados de IGV)
        $monto = $pago->monto;
        $subtotal = $monto;
        $igv = 0;
        $exoneradoIgv = true; // Los servicios de internet generalmente están exonerados

        // Crear comprobante
        $comprobante = Comprobante::create([
            'pago_id' => $pago->id,
            'cliente_id' => $pago->cliente_id,
            'tipo' => $tipoComprobante,
            'serie' => $serie,
            'numero' => $numero,
            'numero_completo' => sprintf('%s-%08d', $serie, $numero),
            'fecha_emision' => $pago->fecha_pago ?? now(),
            'monto' => $monto,
            'moneda' => 'PEN',
            'subtotal' => $subtotal,
            'igv' => $igv,
            'exonerado_igv' => $exoneradoIgv,
            'estado' => Comprobante::ESTADO_EMITIDO,
            'generado_por' => Auth::id(),
            'periodo_servicio' => $pago->recibo->periodo ?? null,
        ]);

        // Guardar snapshot del cliente
        $comprobante->guardarSnapshotCliente();

        // Crear item del comprobante
        $descripcion = 'Servicio de Internet';
        $descripcionDetalle = null;

        if ($pago->recibo && $pago->recibo->servicio && $pago->recibo->servicio->plan) {
            $plan = $pago->recibo->servicio->plan;
            $descripcion = "Servicio de Internet - Plan {$plan->nombre}";
            if ($plan->velocidad_bajada_mbps) {
                $descripcionDetalle = "Velocidad: {$plan->velocidad_bajada_mbps} Mbps";
            }
        }

        \App\Modules\Comprobantes\Models\ComprobanteItem::create([
            'comprobante_id' => $comprobante->id,
            'descripcion' => $descripcion,
            'descripcion_detalle' => $descripcionDetalle,
            'cantidad' => 1,
            'valor_unitario' => $monto,
            'precio_unitario' => $monto,
            'subtotal' => $subtotal,
            'igv' => $igv,
            'total' => $monto,
            'tipo_afectacion_igv' => $exoneradoIgv ? \App\Modules\Comprobantes\Models\ComprobanteItem::TIPO_EXONERADO : \App\Modules\Comprobantes\Models\ComprobanteItem::TIPO_GRAVADO,
            'codigo_producto' => 'SERV001',
            'unidad_medida' => \App\Modules\Comprobantes\Models\ComprobanteItem::UNIDAD_SERVICIO,
            'periodo' => $pago->recibo->periodo ?? null,
            'recibo_id' => $pago->recibo_id,
            'servicio_id' => $pago->servicio_id ?? ($pago->recibo->servicio_id ?? null),
            'orden' => 1,
        ]);

        // Recalcular totales
        $comprobante->calcularTotales();

        $this->logDebug('Comprobante creado', [
            'comprobante_id' => $comprobante->id,
            'pago_id' => $pago->id,
            'numero_completo' => $comprobante->numero_completo
        ]);

        return $comprobante;
    }

    /**
     * Generar PDF del comprobante
     */
    private function generarPDF(Comprobante $comprobante, bool $download = false)
    {
        // Recargar con relaciones (incluyendo código de recibo, servicio, plan y ubicación)
        $comprobante->loadMissing([
            'pago.cliente',
            'pago.recibo.servicio.plan',
            'pago.recibo.servicio.ubicacion',
            'pago.medioPago',
            'pago.registradoPor',
            'items',
            'cliente',
            'generadoPor',
        ]);

        // Obtener datos de la empresa (configuración del sistema)
        $empresa = [
            'nombre' => config('app.name', 'Admin ISP'),
            'ruc' => config('services.comprobantes.empresa.ruc', ''),
            'direccion' => config('services.comprobantes.empresa.direccion', ''),
            'telefono' => config('services.comprobantes.empresa.telefono', ''),
            'email' => config('services.comprobantes.empresa.email', ''),
        ];

        // Obtener cliente (puede venir de pago o directamente del comprobante)
        $cliente = $comprobante->pago
            ? $comprobante->pago->cliente
            : $comprobante->cliente;

        // Validar que el cliente exista
        if (!$cliente) {
            throw new \Exception('No se pudo obtener el cliente del comprobante.');
        }

        $pdf = Pdf::loadView('comprobantes.comprobante', [
            'comprobante' => $comprobante,
            'pago' => $comprobante->pago,
            'cliente' => $cliente,
            'empresa' => $empresa,
        ]);

        // Configurar opciones del PDF
        // Formato ticket 80mm (226.77 points = 80mm)
        $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('enable-local-file-access', true);
        $pdf->setOption('dpi', 150);

        if ($download) {
            return $pdf;
        }

        // Agregar timestamp para evitar caché del navegador
        $timestamp = time();
        return $pdf->stream("Comprobante-{$comprobante->numero_completo}-{$timestamp}.pdf");
    }

    /**
     * Generar recibos masivos para un mes específico
     */
    public function generarMasivos(GenerarMasivosRequest $request)
    {
        $mes = $request->mes;
        $ano = $request->ano;
        $periodo = "{$ano}-{$mes}";
        $fechaVencimiento = \Carbon\Carbon::parse($request->fecha_vencimiento);

        try {
            // Buscar servicios activos para generar recibos
            // (siempre buscamos servicios, no recibos existentes)
            $servicios = \App\Modules\Servicios\Models\Servicio::where('estado', 'activo')
                ->whereHas('ubicacion', function ($query) {
                    $query->whereNotNull('cliente_id');
                })
                ->whereHas('plan', function ($query) {
                    $query->whereNotNull('precio_mensual')
                          ->where('precio_mensual', '>', 0);
                })
                ->with(['ubicacion.cliente', 'plan'])
                ->get();

            $this->logDebug('Buscando servicios activos para generar recibos', [
                'periodo' => $periodo,
                'servicios_activos' => $servicios->count(),
                'servicios_con_ubicacion' => $servicios->filter(function ($s) {
                    return $s->ubicacion && $s->ubicacion->cliente_id;
                })->count(),
                'servicios_con_plan' => $servicios->filter(function ($s) {
                    return $s->plan && $s->plan->precio_mensual > 0;
                })->count()
            ]);

            if ($servicios->isEmpty()) {
                $totalServicios = \App\Modules\Servicios\Models\Servicio::where('estado', 'activo')->count();
                $serviciosSinUbicacion = \App\Modules\Servicios\Models\Servicio::where('estado', 'activo')
                    ->whereDoesntHave('ubicacion', function ($query) {
                        $query->whereNotNull('cliente_id');
                    })->count();
                $serviciosSinPlan = \App\Modules\Servicios\Models\Servicio::where('estado', 'activo')
                    ->whereDoesntHave('plan', function ($query) {
                        $query->whereNotNull('precio_mensual')
                              ->where('precio_mensual', '>', 0);
                    })->count();
                
                $mensaje = "No se encontraron servicios activos válidos para generar recibos del período {$periodo}. ";
                $mensaje .= "Total de servicios activos: {$totalServicios}. ";
                if ($serviciosSinUbicacion > 0) {
                    $mensaje .= "Servicios sin ubicación válida: {$serviciosSinUbicacion}. ";
                }
                if ($serviciosSinPlan > 0) {
                    $mensaje .= "Servicios sin plan o con precio 0: {$serviciosSinPlan}.";
                }
                return back()->with('warning', $mensaje);
            }

            // Validar que la fecha de vencimiento sea mayor o igual a la fecha de emisión
            $fechaEmisionBase = now()->setDate($ano, (int)$mes, 1)->startOfDay();
            if ($fechaVencimiento->startOfDay()->lt($fechaEmisionBase)) {
                return back()->with('error', 'La fecha de vencimiento debe ser mayor o igual a la fecha de emisión (' . $fechaEmisionBase->format('d/m/Y') . ').');
            }

            $generados = 0;
            $errores = 0;
            $omitidos = 0;
            $erroresDetalle = [];

            // Procesar servicios activos
            $items = $servicios;

            $this->logDebug('Iniciando generación de recibos masivos', [
                'total_servicios' => $items->count(),
                'periodo' => $periodo
            ]);

            $clienteIds = $items->map(function ($item) {
                return $item->ubicacion->cliente_id ?? null;
            })->filter()->unique()->values();

            $clientes = \App\Modules\Clientes\Models\Cliente::whereIn('id', $clienteIds)
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                try {
                    // Validar que el servicio tenga las relaciones necesarias
                    if (!$item->ubicacion || !$item->ubicacion->cliente_id) {
                        $errores++;
                        $erroresDetalle[] = "Servicio ID {$item->id} no tiene ubicación válida con cliente";
                        continue;
                    }
                    if (!$item->plan) {
                        $errores++;
                        $erroresDetalle[] = "Servicio ID {$item->id} no tiene plan asociado";
                        continue;
                    }
                    if (!$item->plan->precio_mensual || $item->plan->precio_mensual <= 0) {
                        $errores++;
                        $erroresDetalle[] = "Servicio ID {$item->id} tiene plan sin precio mensual válido";
                        continue;
                    }

                    // Obtener datos del servicio
                    $clienteId = $item->ubicacion->cliente_id;
                    $servicioId = $item->id;
                    $monto = $item->plan->precio_mensual;
                    $fechaEmision = now()->setDate($ano, (int)$mes, 1);

                    $this->logDebug('Procesando servicio para recibo', [
                        'servicio_id' => $servicioId,
                        'cliente_id' => $clienteId,
                        'monto' => $monto,
                        'periodo' => $periodo
                    ]);

                    if (!$clienteId) {
                        Log::warning('Item sin cliente_id, omitiendo', ['item_id' => $item->id]);
                        continue; // Saltar si no hay cliente
                    }

                    if (!$servicioId) {
                        Log::warning('Item sin servicio_id, omitiendo', ['item_id' => $item->id]);
                        continue; // Saltar si no hay servicio
                    }

                    if ($monto <= 0) {
                        Log::warning('Item con monto 0 o negativo, omitiendo', [
                            'item_id' => $item->id,
                            'monto' => $monto
                        ]);
                        continue; // Saltar si no hay monto
                    }

                    // Verificar si ya existe un recibo para este servicio del mismo período
                    // (un cliente puede tener múltiples servicios, cada uno con su recibo)
                    $reciboExistente = \App\Modules\Comprobantes\Models\Recibo::where('periodo', $periodo)
                        ->where('servicio_id', $servicioId)
                        ->first();
                    
                    if ($reciboExistente) {
                        $omitidos++;
                        $this->logDebug('Servicio ya tiene recibo del período, omitiendo', [
                            'cliente_id' => $clienteId,
                            'servicio_id' => $servicioId,
                            'periodo' => $periodo,
                            'recibo_existente_id' => $reciboExistente->id
                        ]);
                        continue; // Saltar si ya tiene recibo del mismo período para este servicio
                    }

                    // Crear recibo usando el servicio, igual que desde la ruta de crear
                    $cliente = $clientes->get($clienteId);
                    if (!$cliente) {
                        Log::warning('Cliente no encontrado, omitiendo', ['cliente_id' => $clienteId]);
                        continue;
                    }

                    // Usar la fecha de vencimiento proporcionada por el usuario
                    // Ya está parseada arriba: $fechaVencimiento

                    // Preparar datos para crear recibo
                    $datosRecibo = [
                        'servicio_id' => $servicioId,
                        'periodo' => $periodo,
                        'fecha_emision' => $fechaEmision->format('Y-m-d'),
                        'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                        'monto' => $monto,
                        'notas' => 'Recibo generado masivamente para período ' . $periodo,
                    ];

                    $this->logDebug('Creando recibo masivo', [
                        'cliente_id' => $clienteId,
                        'servicio_id' => $servicioId,
                        'periodo' => $periodo,
                        'monto' => $monto,
                        'fecha_emision' => $fechaEmision->format('Y-m-d'),
                        'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d')
                    ]);

                    // Usar el servicio ReciboService para crear el recibo (igual que desde la ruta)
                    $reciboService = app(\App\Modules\Comprobantes\Services\ReciboService::class);
                    
                    try {
                        $recibo = \Illuminate\Support\Facades\DB::transaction(function () use ($datosRecibo, $cliente, $reciboService) {
                            return $reciboService->crearRecibo($datosRecibo, $cliente);
                        });

                        if (!$recibo || !$recibo->id) {
                            throw new \Exception('El recibo no se creó correctamente');
                        }

                        $generados++;
                    } catch (\Exception $e) {
                        throw $e; // Re-lanzar para que se capture en el catch externo
                    }

                    $this->logDebug('Recibo creado exitosamente', [
                        'recibo_id' => $recibo->id,
                        'codigo' => $recibo->codigo,
                        'periodo' => $periodo,
                        'cliente_id' => $clienteId,
                        'servicio_id' => $servicioId
                    ]);
                } catch (\Exception $e) {
                    $errores++;
                    $clienteNombre = $item->ubicacion && $item->ubicacion->cliente 
                        ? $item->ubicacion->cliente->nombre 
                        : 'Sin cliente';
                    $erroresDetalle[] = "Servicio ID {$item->id} (Cliente: {$clienteNombre}): " . $e->getMessage();
                    Log::error('Error al crear recibo masivo', [
                        'servicio_id' => $item->id,
                        'cliente_id' => $clienteId,
                        'periodo' => $periodo,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            $totalItems = $servicios->count();
            $tipoItem = 'servicios';

            // Invalidar cachés de clientes afectados
            if ($generados > 0) {
                $clientesAfectados = collect();
                foreach ($items as $item) {
                    $clienteId = $item->ubicacion && $item->ubicacion->cliente_id 
                        ? $item->ubicacion->cliente_id 
                        : null;
                    if ($clienteId) {
                        $clientesAfectados->push($clienteId);
                    }
                }
                $clientesAfectados->unique()->each(function ($clienteId) {
                    \Illuminate\Support\Facades\Cache::forget("cliente.{$clienteId}.servicios.activos.formateados");
                });
            }

            $this->logDebug('Resumen de generación de recibos masivos', [
                'periodo' => $periodo,
                'generados' => $generados,
                'omitidos' => $omitidos,
                'errores' => $errores,
                'total_items' => $totalItems,
                'tipo_item' => $tipoItem
            ]);

            if ($generados === 0 && $omitidos === 0 && $errores === 0) {
                $mensaje = "No se generaron recibos para el período {$periodo}. ";
                $mensaje .= "Total de {$tipoItem} encontrados: {$totalItems}. ";
                $mensaje .= "Verifique los logs de Laravel para más detalles.";
                return back()->with('warning', $mensaje);
            }

            $mensaje = "Se generaron {$generados} recibos para el período {$periodo}. ";
            $mensaje .= "Total de {$tipoItem} procesados: {$totalItems}. ";
            if ($omitidos > 0) {
                $mensaje .= ucfirst($tipoItem) . " omitidos (ya tenían recibo del período): {$omitidos}. ";
            }
            if ($errores > 0) {
                $mensaje .= " Hubo {$errores} errores.";
                if (count($erroresDetalle) <= 5) {
                    $mensaje .= " Detalles: " . implode('; ', $erroresDetalle);
                }
            }

            return back()->with($errores > 0 ? 'warning' : 'success', $mensaje);
        } catch (\Exception $e) {
            Log::error('Error al generar recibos masivos', [
                'periodo' => $periodo,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Error al generar recibos masivos: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar recibos masivos de un período específico
     */
    public function eliminarMasivos(EliminarMasivosRequest $request)
    {
        // Log completo del request para diagnóstico
        Log::info('=== INICIO ELIMINACIÓN MASIVA ===', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'all_data' => $request->all(),
            'mes' => $request->input('mes'),
            'ano' => $request->input('ano'),
            'confirmar' => $request->input('confirmar'),
            'headers' => $request->headers->all(),
        ]);

        $mes = $request->mes;
        $ano = $request->ano;
        $periodo = "{$ano}-{$mes}";

        Log::info('Período formateado', ['periodo' => $periodo]);

        try {
            // Buscar todos los recibos del período con relación de pagos cargada
            // El scope BelongsToIsp ya filtra por isp_id del usuario autenticado
            $recibos = \App\Modules\Comprobantes\Models\Recibo::where('periodo', $periodo)
                ->withCount('pagos')
                ->get();
            
            Log::info('Recibos encontrados después de query', [
                'periodo' => $periodo,
                'total' => $recibos->count(),
                'isp_id_usuario' => auth()->user()->isp_id ?? 'N/A'
            ]);

            Log::info('Recibos encontrados después de query', [
                'periodo' => $periodo,
                'total' => $recibos->count(),
                'isp_id_usuario' => auth()->user()->isp_id ?? 'N/A',
                'recibos_ids' => $recibos->pluck('id')->toArray()
            ]);

            if ($recibos->isEmpty()) {
                Log::warning('No se encontraron recibos para el período', ['periodo' => $periodo]);
                return redirect()->route('clientes.index')->with('warning', "No se encontraron recibos para el período {$periodo}.");
            }

            $totalRecibos = $recibos->count();
            $eliminadas = 0;
            $errores = 0;
            $erroresDetalle = [];

            // Verificar si hay pagos asociados usando withCount
            $recibosConPagos = $recibos->filter(function ($recibo) {
                return $recibo->pagos_count > 0;
            });

            if ($recibosConPagos->count() > 0) {
                $mensaje = "No se pueden eliminar {$recibosConPagos->count()} recibos porque tienen pagos asociados. ";
                $mensaje .= "Total de recibos del período: {$totalRecibos}.";
                return redirect()->route('clientes.index')->with('error', $mensaje);
            }

            // Eliminar recibos sin pagos
            foreach ($recibos as $recibo) {
                try {
                    // Verificar nuevamente que no tenga pagos usando withCount
                    if ($recibo->pagos_count > 0) {
                        $erroresDetalle[] = "Recibo {$recibo->codigo} (ID: {$recibo->id}) tiene pagos asociados";
                        $errores++;
                        continue;
                    }

                    $recibo->delete();
                    $eliminadas++;

                    Log::info('Recibo eliminado masivamente', [
                        'recibo_id' => $recibo->id,
                        'codigo' => $recibo->codigo,
                        'periodo' => $periodo
                    ]);
                } catch (\Exception $e) {
                    $errores++;
                    $erroresDetalle[] = "Recibo {$recibo->codigo} (ID: {$recibo->id}): " . $e->getMessage();
                    Log::error('Error al eliminar recibo masivo', [
                        'recibo_id' => $recibo->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Invalidar cachés de clientes afectados
            if ($eliminadas > 0) {
                $clientesAfectados = $recibos->pluck('cliente_id')->unique();
                $clientesAfectados->each(function ($clienteId) {
                    \Illuminate\Support\Facades\Cache::forget("cliente.{$clienteId}.servicios.activos.formateados");
                });
            }

            Log::info('Resumen de eliminación de recibos masivos', [
                'periodo' => $periodo,
                'eliminadas' => $eliminadas,
                'errores' => $errores,
                'total_recibos' => $totalRecibos
            ]);

            $mensaje = "Se eliminaron {$eliminadas} recibos del período {$periodo}. ";
            if ($errores > 0) {
                $mensaje .= "Hubo {$errores} errores. ";
                if (count($erroresDetalle) <= 5) {
                    $mensaje .= "Detalles: " . implode('; ', $erroresDetalle);
                }
            }

            return redirect()->route('clientes.index')->with($errores > 0 ? 'warning' : 'success', $mensaje);
        } catch (\Exception $e) {
            Log::error('Error al eliminar recibos masivos', [
                'periodo' => $periodo,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('clientes.index')->with('error', 'Error al eliminar recibos masivos: ' . $e->getMessage());
        }
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $context);
        }
    }
}
