<?php

namespace App\Modules\Comprobantes\Repositories;

use App\Modules\Comprobantes\Models\Pago;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PagoRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Pago::with(['cliente', 'servicio', 'recibo'])
            ->latest('fecha_pago')
            ->paginate($perPage);
    }

    public function findByCodigoYOperacion(string $codigoSeguridad, string $numeroOperacion, ?int $exceptId = null): ?Pago
    {
        $query = Pago::where('codigo_seguridad', $codigoSeguridad)
            ->where('numero_operacion', $numeroOperacion);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->first();
    }

    public function obtenerPagosPorCliente(int $clienteId): Collection
    {
        return Pago::where('cliente_id', $clienteId)
            ->with(['servicio', 'recibo'])
            ->latest('fecha_pago')
            ->get();
    }

    public function obtenerPagosPorRecibo(int $reciboId): Collection
    {
        return Pago::where('recibo_id', $reciboId)
            ->with(['cliente', 'servicio', 'medioPago'])
            ->latest('fecha_pago')
            ->get();
    }
}
