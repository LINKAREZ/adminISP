<?php

namespace App\Modules\Comprobantes\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Traits\LogsContext;
use App\Modules\Comprobantes\Requests\StorePagoRequest;
use App\Modules\Comprobantes\Requests\UpdatePagoRequest;
use App\Modules\Comprobantes\Models\Pago;
use App\Modules\Comprobantes\Services\PagoService;
use App\Modules\Comprobantes\Repositories\PagoRepository;
use App\Modules\Clientes\Models\Cliente;
use App\Core\Traits\RespondsWithJson;
use App\Core\Traits\ValidatesDebtOperations;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class PagoController extends Controller
{
    use RespondsWithJson, ValidatesDebtOperations, LogsContext;

    public function __construct(
        private PagoService $pagoService,
        private PagoRepository $pagoRepository
    ) {}

    /**
     * Obtener servicios activos del cliente con plan cargado
     */
    private function obtenerServiciosActivos(Cliente $cliente): \Illuminate\Database\Eloquent\Collection
    {
        return $cliente->servicios()
            ->where('estado', 'activo')
            ->with('plan')
            ->get();
    }

    /**
     * Obtener medios de pago activos ordenados con caché
     *
     * Se cachean por 1 hora ya que se consultan frecuentemente en formularios.
     */
    private function obtenerMediosPagoActivos(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('medios_pago.activos', 3600, function () {
            return \App\Modules\Sistema\Models\MedioPago::activos()
                ->orderBy('tipo')
                ->orderBy('nombre')
                ->get();
        });
    }

    private function validarCaptura(UploadedFile $file): ?string
    {
        if (!$file->isValid()) {
            return 'La captura subida no es válida.';
        }

        $mime = $file->getMimeType();
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowedMimes, true)) {
            return 'El tipo de archivo no es válido. Solo se permiten imágenes JPG, PNG o WEBP.';
        }

        return null;
    }

    public function create(Cliente $cliente, Request $request)
    {
        $this->authorize('create', Pago::class);
        $servicios = $this->obtenerServiciosActivos($cliente);

        $servicioId = $request->input('servicio_id');
        $reciboId = $request->input('recibo_id');
        $recibo = null;
        $servicioFijo = null;

        if ($reciboId) {
            $recibo = \App\Modules\Comprobantes\Models\Recibo::with(['servicio.plan', 'promesasPago'])
                ->find($reciboId);
            if ($recibo && $recibo->servicio) {
                $servicioFijo = $recibo->servicio;
                $servicioId = $servicioFijo->id;
            }
        } elseif ($servicioId) {
            // Buscar en la colección ya cargada antes de hacer otra consulta
            $servicioFijo = $servicios->firstWhere('id', $servicioId);
            if (!$servicioFijo) {
                $servicioFijo = \App\Modules\Servicios\Models\Servicio::with('plan')->find($servicioId);
            }
        }

        $mediosPago = $this->obtenerMediosPagoActivos();
        $pago = null; // Siempre es null en create (nuevo pago)

        return view('clientes.pagos.create', compact(
            'cliente',
            'servicios',
            'servicioId',
            'recibo',
            'servicioFijo',
            'mediosPago',
            'pago'
        ));
    }

    public function edit(Cliente $cliente, Pago $pago)
    {
        $this->authorize('update', $pago);
        // Cargar relaciones necesarias con eager loading
        $pago->load(['servicio.plan', 'recibo', 'medioPago']);

        $servicios = $this->obtenerServiciosActivos($cliente);

        $servicioFijo = $pago->servicio;
        $recibo = $pago->recibo;
        $mediosPago = $this->obtenerMediosPagoActivos();

        return view('clientes.pagos.edit', compact(
            'cliente',
            'servicios',
            'pago',
            'servicioFijo',
            'recibo',
            'mediosPago'
        ));
    }

    public function store(StorePagoRequest $request, Cliente $cliente)
    {
        $this->authorize('create', Pago::class);
        try {
            $validated = $request->validated();
            $diskCapturas = config('isp.archivos.disk_capturas', 'public');
            $timeZone = config('app.timezone', 'America/Lima');
            $validated['cliente_id'] = $cliente->id;
            $validated['registrado_por'] = Auth::id();

            // Si hay recibo, validar que no esté pagado y obtener servicio_id del recibo
            $reciboId = $validated['recibo_id'] ?? null;
            if (!empty($reciboId)) {
                $recibo = \App\Modules\Comprobantes\Models\Recibo::find($reciboId);
                if ($recibo) {
                    $validacion = $this->validateDebtForPayment($recibo, $cliente);
                    if ($validacion) {
                        return $validacion;
                    }
                    // Obtener servicio_id del recibo si no se proporcionó
                    if (empty($validated['servicio_id']) && $recibo->servicio_id) {
                        $validated['servicio_id'] = $recibo->servicio_id;
                    }
                    // Cargar relación para evitar consulta en procesarPago
                    $validated['_recibo'] = $recibo;
                }
            }

            // Procesar captura si existe
            if ($request->hasFile('captura')) {
                $file = $request->file('captura');
                $error = $this->validarCaptura($file);
                if ($error) {
                    return back()
                        ->withInput()
                        ->with('error', $error);
                }
                $path = $file->store('pagos/capturas', $diskCapturas);
                $validated['captura'] = $path;
            }

            // Procesar fecha_hora si viene (ya procesado en prepareForValidation del FormRequest)
            if ($request->filled('fecha_hora')) {
                // Parsear la fecha/hora asumiendo que está en zona horaria de Perú (America/Lima)
                // y convertirla a UTC para guardar en la base de datos
                $fechaHora = Carbon::parse($request->fecha_hora, $timeZone);
                $validated['fecha_hora'] = $fechaHora->utc();
            } elseif ($request->filled(['hora', 'minuto', 'periodo']) && $request->filled('fecha_pago')) {
                // Procesar hora, minuto, periodo (formato 12 horas) y combinarlo con fecha_pago
                $fecha = $validated['fecha_pago']->format('Y-m-d');
                $hora = (int)$request->hora;
                $minuto = (int)$request->minuto;
                $periodo = $request->periodo;

                // Convertir hora AM/PM a formato 24 horas
                $hora24 = $hora;
                if ($periodo === 'PM' && $hora !== 12) {
                    $hora24 = $hora + 12;
                } elseif ($periodo === 'AM' && $hora === 12) {
                    $hora24 = 0;
                }

                // Crear fecha/hora en zona horaria de Perú y convertir a UTC
                $fechaHora = Carbon::createFromFormat('Y-m-d H:i', sprintf('%s %02d:%02d', $fecha, $hora24, $minuto), $timeZone);
                $validated['fecha_hora'] = $fechaHora->utc();
            }

            // Usar transacción para garantizar consistencia atómica
            $pago = DB::transaction(function () use ($validated, $reciboId) {
                $pago = Pago::create($validated);

                // Cargar relación recibo si existe para evitar consulta en procesarPago
                if (!empty($reciboId) && isset($validated['_recibo'])) {
                    $pago->setRelation('recibo', $validated['_recibo']);
                }

                // Procesar pago (actualizar recibo, reactivar servicio si es necesario)
                $this->pagoService->procesarPago($pago);

                return $pago;
            });

            return redirect()
                ->route('clientes.show', $cliente)
                ->with('success', 'Pago registrado correctamente.')
                ->with('active_tab', 'pagos');
        } catch (\Exception $e) {
            $context = [
                'cliente_id' => $cliente->id,
                'servicio_id' => $validated['servicio_id'] ?? null,
                'recibo_id' => $validated['recibo_id'] ?? null,
            ];
            if (config('app.debug')) {
                $context['exception'] = $e;
            } else {
                $context['error'] = $e->getMessage();
            }
            Log::error('Error al crear pago', $context);
            return back()
                ->withInput()
                ->with('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
    }

    public function update(UpdatePagoRequest $request, Cliente $cliente, Pago $pago)
    {
        $this->authorize('update', $pago);
        try {
            $validated = $request->validated();
            $diskCapturas = config('isp.archivos.disk_capturas', 'public');
            $timeZone = config('app.timezone', 'America/Lima');

            // Si hay recibo, validar que no esté pagado
            $reciboId = $validated['recibo_id'] ?? null;
            if (!empty($reciboId)) {
                $recibo = \App\Modules\Comprobantes\Models\Recibo::find($reciboId);
                if ($recibo) {
                    $validacion = $this->validateDebtForPayment($recibo, $cliente);
                    if ($validacion) {
                        return $validacion;
                    }
                    // Cargar relación para evitar consulta en procesarPago
                    $validated['_recibo'] = $recibo;
                }
            }

            // Procesar captura si existe
            if ($request->hasFile('captura')) {
                $file = $request->file('captura');
                $error = $this->validarCaptura($file);
                if ($error) {
                    return back()
                        ->withInput()
                        ->with('error', $error);
                }
                // Eliminar captura anterior si existe
                if ($pago->captura && Storage::disk($diskCapturas)->exists($pago->captura)) {
                    Storage::disk($diskCapturas)->delete($pago->captura);
                }
                $path = $file->store('pagos/capturas', $diskCapturas);
                $validated['captura'] = $path;
            }

            // Procesar fecha_hora (ya procesado en prepareForValidation del FormRequest)
            if ($request->filled('fecha_hora')) {
                // Parsear la fecha/hora asumiendo que está en zona horaria de Perú (America/Lima)
                // y convertirla a UTC para guardar en la base de datos
                $fechaHora = Carbon::parse($request->fecha_hora, $timeZone);
                $validated['fecha_hora'] = $fechaHora->utc();
            } elseif ($request->filled(['hora', 'minuto', 'periodo']) && $request->filled('fecha_pago')) {
                // Procesar hora, minuto, periodo (formato 12 horas) y combinarlo con fecha_pago
                $fecha = $validated['fecha_pago']->format('Y-m-d');
                $hora = (int)$request->hora;
                $minuto = (int)$request->minuto;
                $periodo = $request->periodo;

                // Convertir hora AM/PM a formato 24 horas
                $hora24 = $hora;
                if ($periodo === 'PM' && $hora !== 12) {
                    $hora24 = $hora + 12;
                } elseif ($periodo === 'AM' && $hora === 12) {
                    $hora24 = 0;
                }

                // Crear fecha/hora en zona horaria de Perú y convertir a UTC
                $fechaHora = Carbon::createFromFormat('Y-m-d H:i', sprintf('%s %02d:%02d', $fecha, $hora24, $minuto), $timeZone);
                $validated['fecha_hora'] = $fechaHora->utc();
            }

            // Usar transacción para garantizar consistencia atómica
            DB::transaction(function () use ($validated, $pago, $reciboId) {
                $pago->update($validated);

                // Cargar relación recibo si existe para evitar consulta en procesarPago
                if (!empty($reciboId) && isset($validated['_recibo'])) {
                    $pago->setRelation('recibo', $validated['_recibo']);
                } elseif ($pago->recibo_id) {
                    // Si ya tiene recibo_id pero no está cargada, cargarla
                    $pago->load('recibo');
                }

                // Reprocesar pago
                $this->pagoService->procesarPago($pago);
            });

            return redirect()
                ->route('clientes.show', $cliente)
                ->with('success', 'Pago actualizado correctamente.')
                ->with('active_tab', 'pagos');
        } catch (\Exception $e) {
            $context = [
                'pago_id' => $pago->id,
                'cliente_id' => $cliente->id,
            ];
            if (config('app.debug')) {
                $context['exception'] = $e;
            } else {
                $context['error'] = $e->getMessage();
            }
            Log::error('Error al actualizar pago', $context);
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el pago: ' . $e->getMessage());
        }
    }

    public function destroy(Pago $pago)
    {
        $this->authorize('delete', $pago);
        try {
            // Cargar relaciones necesarias antes de la transacción
            $cliente = $pago->cliente;
            $reciboId = $pago->recibo_id;
            $diskCapturas = config('isp.archivos.disk_capturas', 'public');

            // Cargar servicio antes de eliminar el pago
            $servicioId = $pago->servicio_id;

            // Usar transacción para garantizar consistencia
            DB::transaction(function () use ($pago, $reciboId, $servicioId, $diskCapturas) {
                // Eliminar captura si existe
                if ($pago->captura && Storage::disk($diskCapturas)->exists($pago->captura)) {
                    Storage::disk($diskCapturas)->delete($pago->captura);
                }

                $pago->delete();

                // Procesar efectos de la eliminación (actualizar recibo, cortar/reactivar servicio)
                if ($reciboId) {
                    $this->pagoService->procesarEliminacionPago($reciboId);
                } elseif ($servicioId) {
                    // Si no hay recibo pero hay servicio, verificar si el servicio tiene recibos vencidos
                    $this->pagoService->verificarYCortarServicioPorRecibosVencidos($servicioId);
                }
            });

            return redirect()
                ->route('clientes.show', $cliente)
                ->with('success', 'Pago eliminado correctamente.')
                ->with('active_tab', 'pagos');
        } catch (\Exception $e) {
            $context = [
                'pago_id' => $pago->id ?? null,
                'cliente_id' => $cliente->id ?? null,
            ];
            if (config('app.debug')) {
                $context['exception'] = $e;
            } else {
                $context['error'] = $e->getMessage();
            }
            Log::error('Error al eliminar pago', $context);
            return back()
                ->with('error', 'Error al eliminar el pago: ' . $e->getMessage());
        }
    }

    public function mostrarCaptura(Cliente $cliente, Pago $pago)
    {
        $this->authorize('view', $pago);
        if (!$pago->captura) {
            abort(404, 'Captura no encontrada');
        }

        $diskCapturas = config('isp.archivos.disk_capturas', 'public');

        if (!Storage::disk($diskCapturas)->exists($pago->captura)) {
            abort(404, 'Archivo no encontrado');
        }

        return response()->file(Storage::disk($diskCapturas)->path($pago->captura));
    }

    public function show(Cliente $cliente, Pago $pago)
    {
        $this->authorize('view', $pago);
        // Cargar relaciones necesarias
        $pago->load(['servicio.plan', 'recibo', 'medioPago', 'registradoPor']);

        return view('clientes.pagos.show', compact('cliente', 'pago'));
    }

    public function verificarDuplicado(Request $request)
    {
        $this->authorize('create', Pago::class);
        $validated = $request->validate([
            'codigo_seguridad' => ['required', 'string', 'max:10'],
            'numero_operacion' => ['required', 'string', 'max:50'],
            'pago_id' => ['nullable', 'integer'],
        ]);
        if ($request->filled('pago_id') && ! Pago::where('id', $request->pago_id)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['pago_id' => [__('validation.exists', ['attribute' => 'pago'])]]);
        }

        $codigoSeguridad = $validated['codigo_seguridad'];
        $numeroOperacion = $validated['numero_operacion'];
        $pagoId = $validated['pago_id'] ?? null;

        if (empty($codigoSeguridad) || empty($numeroOperacion)) {
            return response()->json([
                'success' => false,
                'message' => 'Código de seguridad y número de operación son requeridos'
            ], 400);
        }

        $resultado = $this->pagoService->verificarDuplicado($codigoSeguridad, $numeroOperacion, $pagoId);

        return response()->json([
            'success' => true,
            'existe' => $resultado['existe'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? null,
            'pago' => $resultado['pago'] ?? null,
        ]);
    }

    /**
     * Verificar si un número de operación ya existe
     */
    public function verificarNumeroOperacion(Request $request)
    {
        $this->authorize('create', Pago::class);
        $validated = $request->validate([
            'numero_operacion' => ['required', 'string', 'max:50'],
            'pago_id' => ['nullable', 'integer'],
        ]);
        if ($request->filled('pago_id') && ! Pago::where('id', $request->pago_id)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['pago_id' => [__('validation.exists', ['attribute' => 'pago'])]]);
        }

        $numeroOperacion = trim($validated['numero_operacion']);
        $pagoId = $validated['pago_id'] ?? null; // Para excluir el pago actual en edición

        $query = Pago::with(['cliente', 'recibo.cliente', 'servicio.ubicacion.cliente'])
            ->where('numero_operacion', $numeroOperacion);

        if ($pagoId) {
            $query->where('id', '!=', $pagoId);
        }

        $pagoExistente = $query->first();

        if ($pagoExistente) {
            $fecha = $pagoExistente->fecha_pago ? $pagoExistente->fecha_pago->format('d/m/Y') : 'N/A';

            // Intentar obtener el cliente de diferentes formas
            $clienteNombre = 'N/A';
            if ($pagoExistente->cliente) {
                $clienteNombre = $pagoExistente->cliente->nombre ?? 'Cliente sin nombre';
            } elseif ($pagoExistente->recibo && $pagoExistente->recibo->cliente) {
                $clienteNombre = $pagoExistente->recibo->cliente->nombre ?? 'Cliente sin nombre';
            } elseif ($pagoExistente->servicio && $pagoExistente->servicio->ubicacion && $pagoExistente->servicio->ubicacion->cliente) {
                $clienteNombre = $pagoExistente->servicio->ubicacion->cliente->nombre ?? 'Cliente sin nombre';
            }

            return response()->json([
                'existe' => true,
                'mensaje' => "Este número de operación ya fue registrado el {$fecha} para el cliente {$clienteNombre}.",
                'pago' => [
                    'id' => $pagoExistente->id,
                    'fecha' => $fecha,
                    'cliente' => $clienteNombre,
                    'monto' => $pagoExistente->monto
                ]
            ]);
        }

        return response()->json([
            'existe' => false,
            'mensaje' => null
        ]);
    }
}
