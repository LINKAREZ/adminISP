<?php

namespace App\Modules\Sistema\Repositories;

use App\Core\Contracts\Repositories\RepositoryInterface;
use App\Core\Repositories\BaseRepository;
use App\Modules\Sistema\Models\MedioPago;
use Illuminate\Database\Eloquent\Collection;

class MedioPagoRepository extends BaseRepository implements RepositoryInterface
{
    public function __construct(MedioPago $model)
    {
        parent::__construct($model);
    }

    /**
     * Obtener medios de pago activos
     */
    public function getActivos(): Collection
    {
        return $this->model->where('activo', true)->orderBy('nombre')->get();
    }

    /**
     * Obtener medios de pago por tipo
     */
    public function getByTipo(string $tipo): Collection
    {
        return $this->model->where('tipo', $tipo)->where('activo', true)->get();
    }

    /**
     * Actualizar medio de pago
     */
    public function updateModel(MedioPago $medioPago, array $data): bool
    {
        return $medioPago->update($data);
    }

    /**
     * Eliminar medio de pago
     */
    public function deleteModel(MedioPago $medioPago): bool
    {
        return $medioPago->delete();
    }
}
