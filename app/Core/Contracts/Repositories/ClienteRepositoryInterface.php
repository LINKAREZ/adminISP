<?php

namespace App\Core\Contracts\Repositories;

use App\Modules\Clientes\Models\Cliente;

interface ClienteRepositoryInterface extends RepositoryInterface
{
    public function buscarPorDocumento(string $documento): ?Cliente;
    public function buscarPorNombre(string $nombre): \Illuminate\Support\Collection;
    public function obtenerConServicios(int $id): ?Cliente;
    public function obtenerConRecibos(int $id): ?Cliente;
    /**
     * @deprecated Use obtenerConRecibos() instead
     */
    public function obtenerConDeudas(int $id): ?Cliente;
}
