<?php

namespace App\Modules\Comprobantes\Services;

use App\Modules\Comprobantes\Models\PromesaPago;
use App\Modules\Comprobantes\Models\Recibo;
use App\Core\Services\CacheService;
use App\Core\Contracts\Services\RouterServiceInterface;
use App\Modules\Red\Services\RouterOSScriptService;
use App\Core\Traits\NormalizesMacAddress;
use Illuminate\Support\Facades\Log;

class PromesaPagoService
{
    use NormalizesMacAddress;

    public function __construct(
        private CacheService $cacheService,
        private RouterServiceInterface $routerService,
        private RouterOSScriptService $scriptService
    ) {}

    /**
     * Procesar la creación de una promesa de pago
     * Activa el servicio si está cortado
     */
    public function procesarPromesaCreada(PromesaPago $promesa): void
    {
        // Cargar relación recibo si no está cargada
        if (!$promesa->relationLoaded('recibo')) {
            $promesa->load('recibo');
        }

        $recibo = $promesa->recibo;
        if (!$recibo) {
            return;
        }

        // Activar servicio automáticamente si está cortado
        $this->activarServicioSiEsNecesario($recibo, $promesa->id);
    }

    /**
     * Activar servicio si está cortado
     * Reutiliza la lógica de PagoService::reactivarServicioSiEsNecesario
     */
    private function activarServicioSiEsNecesario(Recibo $recibo, ?int $promesaId = null): void
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
                    $this->logDebug("✅ Script y scheduler de corte eliminados al activar servicio por promesa de pago", [
                        'servicio_id' => $servicio->id,
                        'recibo_id' => $recibo->id,
                        'promesa_id' => $promesaId,
                        'mac_address' => $macNormalizada
                    ]);
                } catch (\Exception $e) {
                    Log::error("Error al eliminar script/scheduler de corte al activar servicio por promesa", [
                        'servicio_id' => $servicio->id,
                        'error' => $e->getMessage()
                    ]);
                }

                // Eliminar IP de la lista CORTE
                try {
                    // Intentar eliminar por MAC (busca IP en conexiones activas primero)
                    $resultado = $this->routerService->removeAddressListItem($router, 'CORTE', null, null, $macAddress);

                    if ($resultado) {
                        $this->logDebug("✅ Servicio activado automáticamente al crear promesa de pago - IP eliminada de CORTE", [
                            'servicio_id' => $servicio->id,
                            'recibo_id' => $recibo->id,
                            'promesa_id' => $promesaId,
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
                            $this->logDebug("✅ Servicio activado automáticamente - IP eliminada de CORTE por comentario (MAC)", [
                                'servicio_id' => $servicio->id,
                                'recibo_id' => $recibo->id,
                                'promesa_id' => $promesaId,
                                'mac_address' => $macAddress,
                                'metodo' => 'por_comentario_mac'
                            ]);
                        } else {
                            Log::warning("⚠️ No se pudo eliminar IP de lista CORTE - Cliente no conectado y sin comentario MAC", [
                                'servicio_id' => $servicio->id,
                                'recibo_id' => $recibo->id,
                                'promesa_id' => $promesaId,
                                'mac_address' => $macAddress,
                                'nota' => 'El servicio fue activado en BD, pero la IP permanece en CORTE. Se eliminará cuando el cliente se conecte o se puede eliminar manualmente desde el router.'
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Error al eliminar IP de lista CORTE al activar servicio automáticamente por promesa", [
                        'servicio_id' => $servicio->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error al activar servicio automáticamente al crear promesa de pago", [
                'servicio_id' => $servicio->id ?? null,
                'recibo_id' => $recibo->id,
                'promesa_id' => $promesaId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Procesar la eliminación de una promesa de pago.
     * Corta el servicio si tiene recibos pasados de fecha de corte (vencimiento + días de gracia).
     */
    public function procesarPromesaEliminada(PromesaPago $promesa): void
    {
        // Cargar relación recibo si no está cargada
        if (!$promesa->relationLoaded('recibo')) {
            $promesa->load('recibo');
        }

        $recibo = $promesa->recibo;
        if (!$recibo) {
            // Si no hay recibo, verificar si el servicio tiene recibos vencidos
            if ($promesa->servicio_id) {
                $this->verificarYCortarServicioPorRecibosVencidos($promesa->servicio_id, $promesa->id);
            }
            return;
        }

        // Actualizar estado del recibo para verificar si está vencido
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
            $this->cortarServicioSiEsNecesario($recibo, $promesa->id);
        }
    }

    /**
     * Verificar y cortar servicio si tiene recibos pasados de fecha de corte (vencimiento + días de gracia).
     * Útil cuando se elimina una promesa sin recibo asociado.
     */
    private function verificarYCortarServicioPorRecibosVencidos(int $servicioId, ?int $promesaId = null): void
    {
        $servicio = \App\Modules\Servicios\Models\Servicio::find($servicioId);
        if (!$servicio || $servicio->estado !== 'activo') {
            return;
        }

        // Recibos pasados de fecha de corte (vencimiento + días de gracia)
        $reciboPasadoCorte = $servicio->recibos()->pasadosFechaCorte()->first();

        if ($reciboPasadoCorte) {
            $this->cortarServicioSiEsNecesario($reciboPasadoCorte, $promesaId);
        }
    }

    /**
     * Cortar servicio si el recibo está pasado de la fecha de corte (vencimiento + días de gracia).
     */
    private function cortarServicioSiEsNecesario(Recibo $recibo, ?int $promesaId = null): void
    {
        if (!$recibo->pasadoFechaCorte()) {
            return;
        }
        // Cargar relación servicio si no está cargada
        if (!$recibo->relationLoaded('servicio')) {
            $recibo->load('servicio');
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
                            $this->logDebug("✅ Servicio cortado automáticamente al eliminar promesa de pago - Recibo vencido", [
                                'servicio_id' => $servicio->id,
                                'recibo_id' => $recibo->id,
                                'promesa_id' => $promesaId,
                                'mac_address' => $macNormalizada
                            ]);
                        } else {
                            Log::warning('Script creado pero scheduler falló al cortar servicio automáticamente al eliminar promesa', [
                                'servicio_id' => $servicio->id,
                                'promesa_id' => $promesaId,
                                'script_result' => $scriptResult,
                                'scheduler_result' => $schedulerResult
                            ]);
                        }
                    } else {
                        Log::warning('No se pudo crear script de corte en MikroTik al eliminar promesa de pago', [
                            'servicio_id' => $servicio->id,
                            'promesa_id' => $promesaId,
                            'error' => $scriptResult['message'] ?? 'Error desconocido'
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Error al cortar servicio automáticamente al eliminar promesa de pago", [
                        'servicio_id' => $servicio->id,
                        'recibo_id' => $recibo->id,
                        'promesa_id' => $promesaId,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error al cortar servicio automáticamente al eliminar promesa de pago", [
                'servicio_id' => $servicio->id ?? null,
                'recibo_id' => $recibo->id,
                'promesa_id' => $promesaId,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $context);
        }
    }
}
