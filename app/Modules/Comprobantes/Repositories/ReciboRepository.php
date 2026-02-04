<?php

namespace App\Modules\Comprobantes\Repositories;

use App\Modules\Comprobantes\Models\Recibo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ReciboRepository
{
    /**
     * Obtener todos los recibos con paginación
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Recibo::with(['cliente', 'servicio'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obtener recibo por ID
     */
    public function find(int $id): Recibo
    {
        return Recibo::with(['cliente', 'servicio', 'pagos'])->findOrFail($id);
    }

    /**
     * Obtener recibos por cliente
     */
    public function getByCliente(int $clienteId): Collection
    {
        return Recibo::where('cliente_id', $clienteId)
            ->with(['servicio', 'pagos'])
            ->orderBy('fecha_emision', 'desc')
            ->get();
    }

    /**
     * Obtener recibos por servicio
     */
    public function getByServicio(int $servicioId): Collection
    {
        return Recibo::where('servicio_id', $servicioId)
            ->with(['cliente', 'pagos'])
            ->orderBy('fecha_emision', 'desc')
            ->get();
    }

    /**
     * Obtener recibos pendientes
     */
    public function getPendientes(): Collection
    {
        return Recibo::pendientes()
            ->with(['cliente', 'servicio'])
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();
    }

    /**
     * Obtener recibos vencidos
     */
    public function getVencidos(): Collection
    {
        return Recibo::vencidos()
            ->with(['cliente', 'servicio'])
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();
    }

    /**
     * Crear recibo
     */
    public function create(array $data): Recibo
    {
        return Recibo::create($data);
    }

    /**
     * Actualizar recibo
     */
    public function update(Recibo $recibo, array $data): bool
    {
        return $recibo->update($data);
    }

    /**
     * Eliminar recibo
     */
    public function delete(Recibo $recibo): bool
    {
        return $recibo->delete();
    }
}
