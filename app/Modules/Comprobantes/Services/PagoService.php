<?php

namespace App\Modules\Comprobantes\Services;

use App\Modules\Comprobantes\Models\Pago;
use App\Modules\Comprobantes\Models\Recibo;
use App\Modules\Comprobantes\Models\Comprobante;
use App\Core\Services\CacheService;
use App\Core\Contracts\Services\RouterServiceInterface;
use App\Modules\Red\Services\RouterOSScriptService;
use App\Core\Traits\NormalizesMacAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PagoService
{
    public function __construct(
        private CacheService $cacheService,
        private RouterServiceInterface $routerService,
        private RouterOSScriptService $scriptService
    ) {}

    use NormalizesMacAddress;

    public function procesarPago(Pago $pago): void
    {
        // Asegurar que el pago tenga la relación recibo cargada
        if (!$pago->relationLoaded('recibo')) {
            $pago->load('recibo');
        }

        $this->cacheService->invalidarEstadisticasCliente($pago->cliente_id);

        if ($pago->recibo_id && $pago->recibo) {
            $recibo = $pago->recibo;
            $estadoAnterior = $recibo->estado;

            // Actualizar estado del recibo (recalcula saldo y estado)
            $recibo->actualizarEstado();

            // Marcar promesa como cumplida si existe
            $promesaActiva = $recibo->promesaPagoActiva();
            if ($promesaActiva && in_array($promesaActiva->estado, [
                \App\Modules\Comprobantes\Models\PromesaPago::ESTADO_PENDIENTE,
                \App\Modules\Comprobantes\Models\PromesaPago::ESTADO_VENCIDA
            ])) {
                $promesaActiva->marcarComoCumplida();
                $this->logDebug("Promesa de pago marcada como cumplida automáticamente al registrar pago", [
                    'promesa_id' => $promesaActiva->id,
                    'recibo_id' => $recibo->id,
                    'pago_id' => $pago->id
                ]);
            }

            // Reactivar servicio automáticamente si está cortado y el recibo queda pagado
            // Se reactiva siempre que el recibo quede completamente pagado
            if ($recibo->estado === Recibo::ESTADO_PAGADO) {
                $this->reactivarServicioSiEsNecesario($recibo, $pago->id);
            }
        }

        // Generar comprobante automáticamente si no existe
        $this->generarComprobanteAutomatico($pago);
    }

    /**
     * Generar comprobante automáticamente para un pago
     */
    private function generarComprobanteAutomatico(Pago $pago): void
    {
        try {
            // Verificar si ya existe un comprobante
            $comprobanteExistente = Comprobante::where('pago_id', $pago->id)->first();
            if ($comprobanteExistente) {
                return; // Ya existe, no generar otro
            }

            // Cargar cliente si no está cargado
            if (!$pago->relationLoaded('cliente')) {
                $pago->load('cliente');
            }

            // Validar que el pago tenga cliente asociado
            if (!$pago->cliente) {
                Log::warning('No se puede generar comprobante: el pago no tiene cliente asociado', [
                    'pago_id' => $pago->id
                ]);
                return;
            }

            // Solo generar recibos (documentos internos)
            $tipoComprobante = Comprobante::TIPO_RECIBO;

            // Obtener serie desde configuración (por defecto R001)
            $serie = 'R001';

            // Obtener siguiente número
            $numero = Comprobante::obtenerSiguienteNumero($tipoComprobante, $serie);

            Comprobante::create([
                'pago_id' => $pago->id,
                'cliente_id' => $pago->cliente_id,
                'tipo' => $tipoComprobante,
                'serie' => $serie,
                'numero' => $numero,
                'numero_completo' => sprintf('%s-%08d', $serie, $numero),
                'fecha_emision' => $pago->fecha_pago ?? now(),
                'monto' => $pago->monto,
                'estado' => Comprobante::ESTADO_EMITIDO,
                'generado_por' => Auth::check() ? Auth::id() : null,
            ]);

            $this->logDebug('Comprobante generado automáticamente', [
                'pago_id' => $pago->id,
                'numero_completo' => "{$serie}-{$numero}",
                'tipo' => $tipoComprobante
            ]);
        } catch (\Exception $e) {
            // No fallar el proceso si hay error al generar comprobante
            Log::error('Error al generar comprobante automáticamente', [
                'pago_id' => $pago->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function reactivarServicioSiEsNecesario(Recibo $recibo, ?int $pagoId = null): void
    {
        // Cargar relación servicio si no está cargada
        if (!$recibo->relationLoaded('servicio')) {
            $recibo->load('servicio');
        }

        $servicio = $recibo->servicio;
        if (!$servicio || $servicio->estado !== 'cortado') {
            return;
        }

        try {
            $servicio->update(['estado' => 'activo', 'fecha_corte' => null]);

            // Cargar relaciones necesarias
            if (!$servicio->relationLoaded('router')) {
                $servicio->load('router');
            }

            $macAddress = $servicio->mac_address;
            $router = $servicio->router;

            if ($macAddress && $router) {
                // Normalizar MAC una sola vez
                $macNormalizada = $this->normalizarMacAddress($macAddress);

                // Eliminar script y scheduler de corte en MikroTik
                try {
                    $this->scriptService->removeCorteScriptAndScheduler($router, $macNormalizada);
                    $this->logDebug("✅ Script y scheduler de corte eliminados al reactivar servicio", [
                        'servicio_id' => $servicio->id,
                        'recibo_id' => $recibo->id,
                        'pago_id' => $pagoId,
                        'mac_address' => $macNormalizada
                    ]);
                } catch (\Exception $e) {
                    Log::error("Error al eliminar script/scheduler de corte al reactivar servicio", [
                        'servicio_id' => $servicio->id,
                        'error' => $e->getMessage()
                    ]);
                }

                // Eliminar IP de la lista CORTE
                try {
                    // Intentar eliminar por MAC (busca IP en conexiones activas primero)
                    $resultado = $this->routerService->removeAddressListItem($router, 'CORTE', null, null, $macAddress);

                    if ($resultado) {
                        $this->logDebug("✅ Servicio reactivado automáticamente al pagar recibo - IP eliminada de CORTE", [
                            'servicio_id' => $servicio->id,
                            'recibo_id' => $recibo->id,
                            'pago_id' => $pagoId,
                            'mac_address' => $macAddress,
                            'metodo' => 'por_mac_en_conexion_activa'
                        ]);
                    } else {
                        // Si no hay conexión activa, intentar buscar por comentario (MAC guardada como comentario)
                        $macConSeparadores = implode(':', str_split($macNormalizada, 2));

                        // Intentar con MAC con formato estándar (XX:XX:XX:XX:XX:XX)
                        $resultadoPorComentario = $this->routerService->removeAddressListItem($router, 'CORTE', null, $macConSeparadores, null);

                        if (!$resultadoPorComentario) {
                            // Intentar con MAC sin separadores
                            $resultadoPorComentario = $this->routerService->removeAddressListItem($router, 'CORTE', null, $macNormalizada, null);
                        }

                        if ($resultadoPorComentario) {
                            $this->logDebug("✅ Servicio reactivado automáticamente - IP eliminada de CORTE por comentario (MAC)", [
                                'servicio_id' => $servicio->id,
                                'recibo_id' => $recibo->id,
                                'pago_id' => $pagoId,
                                'mac_address' => $macAddress,
                                'metodo' => 'por_comentario_mac'
                            ]);
                        } else {
                            Log::warning("⚠️ No se pudo eliminar IP de lista CORTE - Cliente no conectado y sin comentario MAC", [
                                'servicio_id' => $servicio->id,
                                'recibo_id' => $recibo->id,
                                'pago_id' => $pagoId,
                                'mac_address' => $macAddress,
                                'nota' => 'El servicio fue reactivado en BD, pero la IP permanece en CORTE. Se eliminará cuando el cliente se conecte o se puede eliminar manualmente desde el router.'
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Error al eliminar IP de lista CORTE al reactivar servicio automáticamente", [
                        'servicio_id' => $servicio->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error al reactivar servicio automáticamente al pagar recibo", [
                'servicio_id' => $servicio->id ?? null,
                'recibo_id' => $recibo->id,
                'pago_id' => $pagoId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Procesar efectos después de eliminar un pago
     * Verifica el estado del recibo y corta/reactiva el servicio según corresponda
     */
    public function procesarEliminacionPago(int $reciboId): void
    {
        if (!$reciboId) {
            return;
        }

        $recibo = Recibo::find($reciboId);
        if (!$recibo) {
            return;
        }

        // Actualizar estado del recibo (recalcula saldo y estado)
        $recibo->actualizarEstado();

        // Cargar relación servicio
        if (!$recibo->relationLoaded('servicio')) {
            $recibo->load('servicio');
        }

        $servicio = $recibo->servicio;
        if (!$servicio) {
            return; // No hay servicio asociado
        }

        // Verificar si el servicio tiene recibos pasados de fecha de corte (vencimiento + días de gracia)
        $tieneRecibosPasadosFechaCorte = $servicio->recibos()->pasadosFechaCorte()->exists();

        // Si tiene recibos pasados de fecha de corte y está activo, cortarlo
        if ($tieneRecibosPasadosFechaCorte && $servicio->estado === 'activo') {
            $this->cortarServicioSiEsNecesario($recibo);
        }
        // Verificar si el recibo quedó pagado y el servicio está cortado
        // Solo reactivar si NO hay otros recibos vencidos
        elseif ($recibo->estado === Recibo::ESTADO_PAGADO && $servicio->estado === 'cortado') {
            // Verificar que no haya otros recibos vencidos antes de reactivar
            $tieneOtrosRecibosVencidos = $servicio->recibos()
                ->where('id', '!=', $recibo->id)
                ->where(function ($q) {
                    $q->where('estado', Recibo::ESTADO_VENCIDO)
                        ->orWhere(function ($q2) {
                            $q2->where('estado', Recibo::ESTADO_PENDIENTE)
                                ->whereDate('fecha_vencimiento', '<', now());
                        });
                })
                ->where('saldo', '>', 0)
                ->exists();

            // Solo reactivar si no hay otros recibos vencidos
            if (!$tieneOtrosRecibosVencidos) {
                $this->reactivarServicioSiEsNecesario($recibo, null);
            }
        }
    }

    /**
     * Verificar y cortar servicio si tiene recibos pasados de fecha de corte (vencimiento + días de gracia).
     * Útil cuando se elimina un pago sin recibo asociado.
     */
    public function verificarYCortarServicioPorRecibosVencidos(int $servicioId): void
    {
        $servicio = \App\Modules\Servicios\Models\Servicio::find($servicioId);
        if (!$servicio || $servicio->estado !== 'activo') {
            return;
        }

        // Recibos pasados de fecha de corte (vencimiento + días de gracia)
        $reciboPasadoCorte = $servicio->recibos()->pasadosFechaCorte()->first();

        if ($reciboPasadoCorte) {
            $this->cortarServicioSiEsNecesario($reciboPasadoCorte);
        }
    }

    /**
     * Cortar servicio si el recibo está pasado de la fecha de corte (vencimiento + días de gracia).
     */
    private function cortarServicioSiEsNecesario(Recibo $recibo): void
    {
        if (!$recibo->pasadoFechaCorte()) {
            return;
        }
        $servicio = $recibo->servicio;
        if (!$servicio || $servicio->estado !== 'activo') {
            return;
        }

        try {
            $servicio->update(['estado' => 'cortado', 'fecha_corte' => now()->toDateString()]);

            // Cargar relaciones necesarias
            if (!$servicio->relationLoaded('router')) {
                $servicio->load('router');
            }

            $macAddress = $servicio->mac_address;
            $router = $servicio->router;

            if ($macAddress && $router) {
                try {
                    $macNormalizada = $this->normalizarMacAddress($macAddress);

                    // Crear script y scheduler de corte en MikroTik
                    $scriptResult = $this->scriptService->createCorteScript($router, $macNormalizada);

                    if ($scriptResult['success']) {
                        $schedulerResult = $this->scriptService->createCorteScheduler(
                            $router,
                            $scriptResult['script_name']
                        );

                        if ($schedulerResult['success']) {
                            // El script ya fue ejecutado automáticamente por createCorteScript
                            $this->logDebug("✅ Servicio cortado automáticamente al eliminar pago - Recibo vencido", [
                                'servicio_id' => $servicio->id,
                                'recibo_id' => $recibo->id,
                                'mac_address' => $macNormalizada
                            ]);
                        } else {
                            Log::warning('Script creado pero scheduler falló al cortar servicio automáticamente', [
                                'servicio_id' => $servicio->id,
                                'script_result' => $scriptResult,
                                'scheduler_result' => $schedulerResult
                            ]);
                        }
                    } else {
                        Log::warning('No se pudo crear script de corte en MikroTik al eliminar pago', [
                            'servicio_id' => $servicio->id,
                            'error' => $scriptResult['message'] ?? 'Error desconocido'
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Error al cortar servicio automáticamente al eliminar pago", [
                        'servicio_id' => $servicio->id,
                        'recibo_id' => $recibo->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error al cortar servicio automáticamente al eliminar pago", [
                'servicio_id' => $servicio->id ?? null,
                'recibo_id' => $recibo->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function verificarDuplicado(string $codigoSeguridad, string $numeroOperacion, ?int $pagoId = null): ?array
    {
        if (empty($codigoSeguridad) || empty($numeroOperacion)) {
            return null;
        }

        $codigoSeguridad = trim($codigoSeguridad);
        $numeroOperacion = trim(preg_replace('/[^0-9]/', '', $numeroOperacion));

        $query = Pago::where('codigo_seguridad', $codigoSeguridad)
            ->where('numero_operacion', $numeroOperacion);

        if ($pagoId) {
            $query->where('id', '!=', $pagoId);
        }

        $pagoExistente = $query->with('cliente')->first();

        if ($pagoExistente) {
            return [
                'existe' => true,
                'mensaje' => "⚠️ Este código de seguridad y número de operación ya fueron usados en un pago registrado el " . $pagoExistente->fecha_pago->format('d/m/Y') . " para el cliente " . $pagoExistente->cliente->nombre . ".",
                'pago' => [
                    'id' => $pagoExistente->id,
                    'fecha' => $pagoExistente->fecha_pago->format('d/m/Y'),
                    'cliente' => $pagoExistente->cliente->nombre,
                    'monto' => $pagoExistente->monto
                ]
            ];
        }

        return ['existe' => false];
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $context);
        }
    }
}
