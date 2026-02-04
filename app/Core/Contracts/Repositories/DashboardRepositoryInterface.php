<?php

namespace App\Core\Contracts\Repositories;

interface DashboardRepositoryInterface
{
    public function getEstadisticas(): array;
    public function getEstadisticasClientes(): array;
    public function getEstadisticasServicios(): array;
    public function getEstadisticasComprobantes(): array;
    public function getEstadisticasRed(): array;
    public function getIngresosMensuales(int $meses = 6): array;
}
