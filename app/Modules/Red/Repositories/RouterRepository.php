<?php

namespace App\Modules\Red\Repositories;

use App\Core\Contracts\Repositories\RepositoryInterface;
use App\Core\Repositories\BaseRepository;
use App\Modules\Red\Models\Router;

class RouterRepository extends BaseRepository implements RepositoryInterface
{
    public function __construct(Router $model)
    {
        parent::__construct($model);
    }

    public function obtenerRoutersActivos()
    {
        return $this->model->where('estado', true)->get();
    }

    public function buscarPorIp(string $ip)
    {
        return $this->model->where('ip_url', $ip)->first();
    }
}
