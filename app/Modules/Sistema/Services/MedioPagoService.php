<?php

namespace App\Modules\Sistema\Services;

use App\Modules\Sistema\Models\MedioPago;
use App\Modules\Sistema\Repositories\MedioPagoRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MedioPagoService
{
    public function __construct(
        protected MedioPagoRepository $repository
    ) {}

    /**
     * Obtener medios de pago paginados
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Obtener todos los medios de pago activos
     */
    public function getActivos(): Collection
    {
        return $this->repository->getActivos();
    }

    /**
     * Obtener medio de pago por ID
     */
    public function find(int $id): ?MedioPago
    {
        return $this->repository->find($id);
    }

    /**
     * Crear medio de pago
     */
    public function create(array $data): MedioPago
    {
        return $this->repository->create($data);
    }

    /**
     * Actualizar medio de pago
     */
    public function update(MedioPago $medioPago, array $data): bool
    {
        return $this->repository->updateModel($medioPago, $data);
    }

    /**
     * Eliminar medio de pago
     */
    public function delete(MedioPago $medioPago): bool
    {
        return $this->repository->deleteModel($medioPago);
    }

    /**
     * Obtener medios de pago por tipo
     */
    public function getByTipo(string $tipo): Collection
    {
        return $this->repository->getByTipo($tipo);
    }
}
