<?php

namespace App\Modules\Red\Repositories;

use App\Core\Contracts\Repositories\RepositoryInterface;
use App\Core\Repositories\BaseRepository;
use App\Modules\Red\Models\Nodo;

class NodoRepository extends BaseRepository implements RepositoryInterface
{
    public function __construct(Nodo $model)
    {
        parent::__construct($model);
    }

    public function obtenerNodosPorRouter(int $routerId)
    {
        return $this->model->where('router_id', $routerId)->get();
    }
}
