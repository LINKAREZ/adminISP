<?php

namespace App\Modules\Instalaciones\Services;

use App\Modules\Almacen\Models\OrdenInstalacionMaterial;
use App\Modules\Almacen\Services\AlmacenService;
use App\Modules\Clientes\Models\Ubicacion;
use App\Modules\Instalaciones\Models\OrdenInstalacion;
use App\Modules\Servicios\Models\Servicio;
use App\Modules\Servicios\Services\ServicioService;
use App\Core\Traits\NormalizesMacAddress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstalacionService
{
    use NormalizesMacAddress;

    public function __construct(
        private ServicioService $servicioService,
        private AlmacenService $almacenService
    ) {}

    /**
     * Completa una orden de instalación: crea ubicación (si no existe), servicio y actualiza la orden.
     */
    public function completarOrden(OrdenInstalacion $orden, array $datosServicio): array
    {
        return DB::transaction(function () use ($orden, $datosServicio) {
            $ubicacion = $this->servicioService->obtenerOCrearUbicacion([
                'direccion' => $orden->direccion,
                'referencia' => $orden->referencia,
                'distrito' => $orden->distrito,
                'provincia' => $orden->provincia,
                'departamento' => $orden->departamento,
            ], $orden->cliente_id);

            if ($orden->router_id && $ubicacion->router_id !== $orden->router_id) {
                $ubicacion->update(['router_id' => $orden->router_id]);
            }

            $macAddress = isset($datosServicio['mac_address']) && $datosServicio['mac_address'] !== ''
                ? $this->normalizarMacAddress($datosServicio['mac_address'])
                : null;

            $servicioData = [
                'ubicacion_id' => $ubicacion->id,
                'router_id' => $orden->router_id ?? $ubicacion->router_id,
                'plan_id' => $orden->plan_id,
                'tipo_pppoe' => $datosServicio['tipo_pppoe'] ?? 'usuario_unico',
                'usuario_pppoe' => $datosServicio['usuario_pppoe'] ?? null,
                'password_pppoe' => $datosServicio['password_pppoe'] ?? null,
                'mac_address' => $macAddress,
                'estado' => 'activo',
                'fecha_instalacion' => now()->toDateString(),
                'notas' => $orden->notas,
                'es_provisional' => false,
            ];

            $servicioData = $this->servicioService->procesarCredencialesProvisionales(
                $servicioData,
                $datosServicio['onu_id'] ?? null
            );

            $servicio = Servicio::create($servicioData);

            if (!empty($datosServicio['onu_id'])) {
                $this->servicioService->asociarOnuAServicio(
                    $servicio,
                    (int) $datosServicio['onu_id'],
                    $macAddress
                );
            }

            $orden->update([
                'estado' => OrdenInstalacion::ESTADO_COMPLETADA,
                'fecha_completada' => now(),
                'ubicacion_id' => $ubicacion->id,
                'servicio_id' => $servicio->id,
            ]);

            $materiales = $datosServicio['materiales'] ?? [];
            $materiales = array_filter($materiales, fn ($m) => !empty($m['articulo_id']) && !empty($m['almacen_id']) && (float) ($m['cantidad'] ?? 0) > 0);
            foreach ($materiales as $m) {
                $articuloId = (int) $m['articulo_id'];
                $almacenId = (int) $m['almacen_id'];
                $cantidad = (float) $m['cantidad'];
                OrdenInstalacionMaterial::create([
                    'orden_instalacion_id' => $orden->id,
                    'articulo_id' => $articuloId,
                    'almacen_id' => $almacenId,
                    'cantidad' => $cantidad,
                    'isp_id' => $orden->isp_id,
                ]);
                $this->almacenService->registrarConsumoInstalacion(
                    $almacenId,
                    $articuloId,
                    $cantidad,
                    'orden_instalacion',
                    $orden->id
                );
            }

            $this->servicioService->invalidarCache($servicio);

            Log::info('Orden de instalación completada', [
                'orden_id' => $orden->id,
                'servicio_id' => $servicio->id,
                'ubicacion_id' => $ubicacion->id,
            ]);

            return ['ubicacion' => $ubicacion, 'servicio' => $servicio];
        });
    }
}
