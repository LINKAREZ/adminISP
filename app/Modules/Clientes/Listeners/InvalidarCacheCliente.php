<?php

namespace App\Modules\Clientes\Listeners;

use App\Modules\Clientes\Events\ClienteActualizado;
use App\Modules\Clientes\Services\ClienteService;
use App\Modules\Servicios\Events\ServicioActualizado;
use App\Modules\Comprobantes\Events\PagoRegistrado;

/**
 * Listener que invalida el caché de estadísticas del cliente
 * Escucha múltiples eventos relacionados con cambios que afectan las estadísticas
 */
class InvalidarCacheCliente
{
    public function __construct(
        private ClienteService $clienteService
    ) {}

    /**
     * Manejar el evento ClienteActualizado
     */
    public function handle(ClienteActualizado|ServicioActualizado|PagoRegistrado $event): void
    {
        $clienteId = match (true) {
            $event instanceof ClienteActualizado => $event->cliente->id,
            // ✅ Obtener cliente_id desde ubicación
            $event instanceof ServicioActualizado => $this->obtenerClienteIdDesdeServicio($event->servicio),
            $event instanceof PagoRegistrado => $event->pago->cliente_id,
            default => null,
        };

        if ($clienteId) {
            $cliente = \App\Modules\Clientes\Models\Cliente::find($clienteId);
            if ($cliente) {
                $this->clienteService->invalidarEstadisticas($cliente);
            }
        }
    }

    /**
     * Obtener cliente_id desde servicio a través de ubicación
     */
    private function obtenerClienteIdDesdeServicio($servicio): ?int
    {
        // Intentar obtener desde ubicación cargada
        if ($servicio->relationLoaded('ubicacion') && $servicio->ubicacion) {
            return $servicio->ubicacion->cliente_id;
        }

        // Si no está cargada, usar el accessor que carga la relación
        if ($servicio->ubicacion_id) {
            return $servicio->ubicacion?->cliente_id;
        }

        return null;
    }
}
