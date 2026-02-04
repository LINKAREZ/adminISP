<?php

namespace App\Modules\Comprobantes\Repositories;

use App\Modules\Comprobantes\Models\Comprobante;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ComprobanteRepository
{
    /**
     * Obtener todos los comprobantes con paginación
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Comprobante::with(['pago', 'cliente', 'generadoPor'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obtener comprobante por ID
     */
    public function find(int $id): Comprobante
    {
        return Comprobante::with(['pago', 'cliente', 'generadoPor'])->findOrFail($id);
    }

    /**
     * Obtener comprobantes por cliente
     */
    public function getByCliente(int $clienteId): Collection
    {
        return Comprobante::where('cliente_id', $clienteId)
            ->with(['pago', 'generadoPor'])
            ->orderBy('fecha_emision', 'desc')
            ->get();
    }

    /**
     * Obtener siguiente número de comprobante
     */
    public function getSiguienteNumero(string $tipo, string $serie): int
    {
        return Comprobante::obtenerSiguienteNumero($tipo, $serie);
    }

    /**
     * Crear comprobante
     */
    public function create(array $data): Comprobante
    {
        return Comprobante::create($data);
    }

    /**
     * Eliminar comprobante
     */
    public function delete(Comprobante $comprobante): bool
    {
        return $comprobante->delete();
    }
}
