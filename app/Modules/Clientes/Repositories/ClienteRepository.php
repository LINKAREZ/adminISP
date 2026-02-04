<?php

namespace App\Modules\Clientes\Repositories;

use App\Modules\Clientes\Models\Cliente;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ClienteRepository
{
    /**
     * Obtener todos los clientes con paginación
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Cliente::latest()->paginate($perPage);
    }

    /**
     * Buscar clientes por término usando el trait Searchable
     */
    public function search(string $term): Collection
    {
        return Cliente::search($term, ['nombre', 'documento', 'telefonos'])
            ->get();
    }

    /**
     * Buscar cliente por documento
     */
    public function findByDocumento(string $documento): ?Cliente
    {
        return Cliente::where('documento', $documento)->first();
    }

    /**
     * Crear cliente
     */
    public function create(array $data): Cliente
    {
        return Cliente::create($data);
    }

    /**
     * Actualizar cliente
     */
    public function update(Cliente $cliente, array $data): bool
    {
        return $cliente->update($data);
    }

    /**
     * Eliminar cliente
     */
    public function delete(Cliente $cliente): bool
    {
        return $cliente->delete();
    }

    /**
     * Obtener cliente con relaciones
     */
    public function findWithRelations(int $id, array $relations = []): ?Cliente
    {
        return Cliente::with($relations)->find($id);
    }
}
