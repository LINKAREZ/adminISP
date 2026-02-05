<?php

namespace App\Modules\Servicios\Services;

use App\Modules\Servicios\Models\Servicio;
use App\Modules\Servicios\Models\Onu;
use App\Core\Services\CacheService;
use App\Core\Traits\NormalizesMacAddress;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para lógica de negocio relacionada con servicios
 *
 * Maneja operaciones complejas relacionadas con servicios como:
 * - Gestión de ubicaciones
 * - Asociación de ONUs
 * - Procesamiento de credenciales provisionales
 * - Invalidación de caché
 */
class ServicioService
{
    use NormalizesMacAddress;

    public function __construct(
        private CacheService $cacheService
    ) {}

    /**
     * Obtener o crear una ubicación para un cliente
     *
     * Busca si existe una ubicación con la misma dirección para el cliente.
     * Si existe, la retorna. Si no existe, crea una nueva ubicación.
     *
     * @param array $data Datos de la ubicación (direccion, referencia, distrito, provincia, departamento)
     * @param int $clienteId ID del cliente
     * @return \App\Modules\Clientes\Models\Ubicacion Ubicación existente o nueva
     */
    public function obtenerOCrearUbicacion(array $data, int $clienteId): \App\Modules\Clientes\Models\Ubicacion
    {
        $direccionNormalizada = trim(strtolower(preg_replace('/\s+/', ' ', $data['direccion'])));

        $ubicacionesCliente = \App\Modules\Clientes\Models\Ubicacion::where('cliente_id', $clienteId)->get();

        foreach ($ubicacionesCliente as $ubicacion) {
            $direccionExistenteNormalizada = trim(strtolower(preg_replace('/\s+/', ' ', $ubicacion->direccion)));
            if ($direccionExistenteNormalizada === $direccionNormalizada) {
                $this->logDebug('Usando ubicación existente para el servicio', [
                    'ubicacion_id' => $ubicacion->id,
                    'cliente_id' => $clienteId,
                    'direccion' => $data['direccion']
                ]);
                return $ubicacion;
            }
        }

        $ubicacionData = [
            'cliente_id' => $clienteId,
            'direccion' => trim($data['direccion']),
            'referencia' => $data['referencia'] ?? null,
            'distrito' => $data['distrito'] ?? 'San Juan de Lurigancho',
            'provincia' => $data['provincia'] ?? 'Lima',
            'departamento' => $data['departamento'] ?? 'Lima',
        ];

        $ubicacion = \App\Modules\Clientes\Models\Ubicacion::create($ubicacionData);

        $this->logDebug('Ubicación creada automáticamente para el servicio', [
            'ubicacion_id' => $ubicacion->id,
            'cliente_id' => $clienteId,
            'direccion' => $data['direccion']
        ]);

        return $ubicacion;
    }

    /**
     * Procesar credenciales provisionales para un servicio
     *
     * Si el servicio es de tipo "usuario_compartido" y tiene una ONU asociada con credenciales por defecto,
     * asigna esas credenciales y marca el servicio como provisional.
     *
     * @param array $data Array de datos del servicio
     * @param int|null $onuId ID de la ONU asociada (opcional)
     * @return array Array de datos procesados con credenciales asignadas si aplica
     */
    public function procesarCredencialesProvisionales(array $data, ?int $onuId): array
    {
        if ($data['tipo_pppoe'] !== 'usuario_compartido') {
            $data['es_provisional'] = false;
            return $data;
        }

        if (!$onuId) {
            $data['usuario_pppoe'] = null;
            $data['password_pppoe'] = null;
            $data['es_provisional'] = false;
            return $data;
        }

        $onu = Onu::find($onuId);
        if (!$onu || !$onu->modelo) {
            $data['usuario_pppoe'] = null;
            $data['password_pppoe'] = null;
            $data['es_provisional'] = false;
            return $data;
        }

        $modelo = \App\Modules\Servicios\Models\OnuModelo::where('nombre', $onu->modelo)->first();

        if ($modelo && $modelo->usuario_pppoe_default && $modelo->password_pppoe_default) {
            $data['usuario_pppoe'] = $modelo->usuario_pppoe_default;
            $data['password_pppoe'] = $modelo->password_pppoe_default;
            $data['es_provisional'] = true;

            if ($modelo->vlan_default) {
                $notaVlan = "VLAN por defecto: {$modelo->vlan_default}";
                $data['notas'] = ($data['notas'] ?? '') . ($data['notas'] ? "\n" : '') . $notaVlan;
            }

            $this->logDebug('Servicio creado con credenciales por defecto', [
                'onu_id' => $onu->id,
                'modelo' => $onu->modelo,
                'usuario_pppoe' => $data['usuario_pppoe'],
                'es_provisional' => true
            ]);
        } else {
            $data['usuario_pppoe'] = null;
            $data['password_pppoe'] = null;
            $data['es_provisional'] = false;
        }

        return $data;
    }

    /**
     * Asociar una ONU a un servicio
     *
     * Asocia una ONU a un servicio. Busca la ONU por ID o por MAC address si no se proporciona ID.
     * Solo asocia ONUs que no estén ya asociadas a otro servicio.
     *
     * @param Servicio $servicio Servicio al cual asociar la ONU
     * @param int|null $onuId ID de la ONU (prioridad)
     * @param string|null $macAddress MAC address de la ONU (alternativa)
     * @return void
     */
    public function asociarOnuAServicio(Servicio $servicio, ?int $onuId, ?string $macAddress): void
    {
        if ($onuId) {
            $onu = Onu::find($onuId);
            if ($onu) {
                $onu->update(['servicio_id' => $servicio->id]);
                $this->logDebug('ONU asociada al servicio', [
                    'servicio_id' => $servicio->id,
                    'onu_id' => $onu->id
                ]);
            }
        } elseif ($macAddress) {
            $macNormalizada = $this->normalizarMacAddress($macAddress);
            $onu = Onu::where('mac_address', $macNormalizada)
                ->whereNull('servicio_id')
                ->first();

            if ($onu) {
                $onu->update(['servicio_id' => $servicio->id]);
                $this->logDebug('ONU encontrada por MAC y asociada', [
                    'servicio_id' => $servicio->id,
                    'onu_id' => $onu->id
                ]);
            }
        }
    }

    /**
     * Invalidar caché relacionado con un servicio
     *
     * Invalida el caché de estadísticas del cliente asociado al servicio
     * para que se recalcule cuando se consulte nuevamente.
     *
     * @param Servicio $servicio Servicio que ha sido modificado
     * @return void
     */
    public function invalidarCache(Servicio $servicio): void
    {
        // ✅ Obtener cliente_id desde ubicación
        if ($servicio->ubicacion_id && $servicio->relationLoaded('ubicacion')) {
            $clienteId = $servicio->ubicacion->cliente_id;
            $this->cacheService->invalidarEstadisticasCliente($clienteId);
        } elseif ($servicio->ubicacion_id) {
            // Si no está cargado, cargarlo
            $servicio->load('ubicacion');
            $clienteId = $servicio->ubicacion->cliente_id;
            $this->cacheService->invalidarEstadisticasCliente($clienteId);
        }
    }

    /**
     * Verificar si un servicio puede ser eliminado
     *
     * Un servicio NO puede ser eliminado si:
     * - Tiene recibos con saldo por pagar (saldo > 0)
     * - Tiene promesas de pago pendientes o vencidas
     *
     * Si los recibos tienen saldo 0 (pagados o anulados) sí se permite eliminar.
     *
     * @param Servicio $servicio Servicio a verificar
     * @return array Array con las siguientes claves:
     *   - puede_eliminar: bool Indica si el servicio puede ser eliminado
     *   - razones: array Lista de razones por las cuales no puede ser eliminado (vacío si puede eliminarse)
     */
    public function puedeEliminar(Servicio $servicio): array
    {
        $puedeEliminar = true;
        $razones = [];

        $tieneDeuda = $servicio->recibos()->where('saldo', '>', 0)->exists();

        if ($tieneDeuda) {
            $puedeEliminar = false;
            $razones[] = 'tiene recibos con saldo por pagar';
        }

        $promesasPendientes = \App\Modules\Comprobantes\Models\PromesaPago::where('servicio_id', $servicio->id)
            ->whereIn('estado', ['pendiente', 'vencida'])
            ->exists();

        if ($promesasPendientes) {
            $puedeEliminar = false;
            $razones[] = 'tiene promesas de pago pendientes';
        }

        return [
            'puede_eliminar' => $puedeEliminar,
            'razones' => $razones,
        ];
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $context);
        }
    }
}
