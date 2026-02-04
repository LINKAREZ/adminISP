<?php

namespace App\Core\Contracts\Repositories;

interface PagoRepositoryInterface extends RepositoryInterface
{
    public function verificarDuplicado(string $codigoSeguridad, string $numeroOperacion, ?int $pagoId = null): ?array;
    public function obtenerPagosPorCliente(int $clienteId): \Illuminate\Support\Collection;
    public function obtenerPagosPorRecibo(int $reciboId): \Illuminate\Support\Collection;
}
