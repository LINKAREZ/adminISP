<?php

namespace App\Core\Contracts\Repositories;

use App\Modules\Servicios\Models\Servicio;

interface ServicioRepositoryInterface extends RepositoryInterface
{
    public function buscarPorMac(string $macAddress): ?Servicio;
    public function buscarPorUsuarioPppoe(string $usuario): ?Servicio;
    public function obtenerServiciosActivos(int $clienteId): \Illuminate\Support\Collection;
    public function cambiarEstado(int $id, string $estado): bool;
}
