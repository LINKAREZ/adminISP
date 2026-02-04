<?php

namespace App\Modules\Servicios\Repositories;

use App\Modules\Servicios\Models\Plan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PlanRepository
{
    /**
     * Obtener todos los planes con paginación
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Plan::with('router')
            ->orderBy('nombre')
            ->paginate($perPage);
    }

    /**
     * Obtener todos los planes
     */
    public function getAll(): Collection
    {
        return Plan::with('router')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Obtener planes activos
     */
    public function getActivos(): Collection
    {
        return Plan::where('estado', true)
            ->with('router')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Obtener plan por ID
     */
    public function find(int $id): ?Plan
    {
        return Plan::with(['router', 'servicios'])->find($id);
    }

    /**
     * Obtener planes por router
     */
    public function getByRouter(int $routerId): Collection
    {
        return Plan::where('router_id', $routerId)
            ->where('estado', true)
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Crear plan
     */
    public function create(array $data): Plan
    {
        return Plan::create($data);
    }

    /**
     * Actualizar plan
     */
    public function update(Plan $plan, array $data): bool
    {
        return $plan->update($data);
    }

    /**
     * Eliminar plan
     */
    public function delete(Plan $plan): bool
    {
        return $plan->delete();
    }
}
