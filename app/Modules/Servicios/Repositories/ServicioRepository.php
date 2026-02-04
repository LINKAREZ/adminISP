<?php

namespace App\Modules\Servicios\Repositories;

use App\Modules\Servicios\Models\Servicio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ServicioRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        // ✅ Cargar cliente a través de ubicación
        return Servicio::with(['ubicacion.cliente', 'router', 'plan'])
            ->latest()
            ->paginate($perPage);
    }

    public function findByMacAddress(string $macAddress): ?Servicio
    {
        return Servicio::with('ubicacion.cliente')->where('mac_address', $macAddress)->first();
    }

    public function obtenerServiciosPorCliente(int $clienteId): Collection
    {
        // ✅ Usar whereHas para obtener servicios del cliente a través de ubicaciones
        return Servicio::whereHas('ubicacion', function ($query) use ($clienteId) {
                $query->where('cliente_id', $clienteId);
            })
            ->with(['router', 'plan', 'onu', 'ubicacion.cliente'])
            ->get();
    }
}
