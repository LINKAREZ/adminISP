<?php

namespace App\Modules\Almacen\Services;

use App\Modules\Almacen\Models\Almacen;
use App\Modules\Almacen\Models\Articulo;
use App\Modules\Almacen\Models\MovimientoInventario;
use App\Modules\Almacen\Models\Stock;
use Illuminate\Support\Facades\DB;

class AlmacenService
{
    /**
     * Obtener o crear almacén central del ISP.
     */
    public function obtenerAlmacenCentral(): Almacen
    {
        $ispId = session('current_isp_id') ?? auth()->user()?->isp_id;
        $almacen = Almacen::where('isp_id', $ispId)->where('tipo', Almacen::TIPO_CENTRAL)->first();
        if (!$almacen) {
            $almacen = Almacen::create([
                'nombre' => 'Almacén Central',
                'tipo' => Almacen::TIPO_CENTRAL,
                'user_id' => null,
                'isp_id' => $ispId,
            ]);
        }
        return $almacen;
    }

    /**
     * Obtener o crear almacén vehículo para un usuario (técnico).
     */
    public function obtenerAlmacenVehiculo(int $userId, string $nombreTecnico): Almacen
    {
        $ispId = session('current_isp_id') ?? auth()->user()?->isp_id;
        $almacen = Almacen::where('isp_id', $ispId)->where('tipo', Almacen::TIPO_VEHICULO)->where('user_id', $userId)->first();
        if (!$almacen) {
            $almacen = Almacen::create([
                'nombre' => 'Vehículo ' . $nombreTecnico,
                'tipo' => Almacen::TIPO_VEHICULO,
                'user_id' => $userId,
                'isp_id' => $ispId,
            ]);
        }
        return $almacen;
    }

    /**
     * Trasladar ítems del almacén origen al destino (entrega a técnico).
     * items: [ ['articulo_id' => 1, 'cantidad' => 2], ... ]
     */
    public function trasladar(int $almacenOrigenId, int $almacenDestinoId, array $items, ?string $observacion = null): void
    {
        DB::transaction(function () use ($almacenOrigenId, $almacenDestinoId, $items, $observacion) {
            foreach ($items as $item) {
                $articuloId = (int) ($item['articulo_id'] ?? 0);
                $cantidad = (float) ($item['cantidad'] ?? 0);
                if ($articuloId <= 0 || $cantidad <= 0) {
                    continue;
                }
                $this->descontarStock($almacenOrigenId, $articuloId, $cantidad);
                $this->agregarStock($almacenDestinoId, $articuloId, $cantidad);
                MovimientoInventario::create([
                    'almacen_origen_id' => $almacenOrigenId,
                    'almacen_destino_id' => $almacenDestinoId,
                    'articulo_id' => $articuloId,
                    'cantidad' => $cantidad,
                    'tipo' => MovimientoInventario::TIPO_TRASLADO,
                    'user_id' => auth()->id(),
                    'observacion' => $observacion,
                    'isp_id' => session('current_isp_id') ?? auth()->user()?->isp_id,
                ]);
            }
        });
    }

    /**
     * Registrar consumo en instalación: sale del almacén del técnico.
     */
    public function registrarConsumoInstalacion(int $almacenId, int $articuloId, float $cantidad, string $referenciaTipo, int $referenciaId): void
    {
        DB::transaction(function () use ($almacenId, $articuloId, $cantidad, $referenciaTipo, $referenciaId) {
            $this->descontarStock($almacenId, $articuloId, $cantidad);
            MovimientoInventario::create([
                'almacen_origen_id' => $almacenId,
                'almacen_destino_id' => null,
                'articulo_id' => $articuloId,
                'cantidad' => $cantidad,
                'tipo' => MovimientoInventario::TIPO_CONSUMO_INSTALACION,
                'referencia_tipo' => $referenciaTipo,
                'referencia_id' => $referenciaId,
                'user_id' => auth()->id(),
                'isp_id' => session('current_isp_id') ?? auth()->user()?->isp_id,
            ]);
        });
    }

    private function descontarStock(int $almacenId, int $articuloId, float $cantidad): void
    {
        $stock = Stock::where('almacen_id', $almacenId)->where('articulo_id', $articuloId)->first();
        if (!$stock || $stock->cantidad < $cantidad) {
            throw new \InvalidArgumentException('Stock insuficiente para el artículo en el almacén.');
        }
        $stock->decrement('cantidad', $cantidad);
    }

    private function agregarStock(int $almacenId, int $articuloId, float $cantidad): void
    {
        $ispId = session('current_isp_id') ?? auth()->user()?->isp_id;
        $stock = Stock::firstOrCreate(
            ['almacen_id' => $almacenId, 'articulo_id' => $articuloId],
            ['cantidad' => 0, 'isp_id' => $ispId]
        );
        $stock->increment('cantidad', $cantidad);
    }

    /**
     * Obtener cantidad disponible de un artículo en un almacén.
     */
    public function cantidadDisponible(int $almacenId, int $articuloId): float
    {
        $stock = Stock::where('almacen_id', $almacenId)->where('articulo_id', $articuloId)->first();
        return $stock ? (float) $stock->cantidad : 0.0;
    }
}
