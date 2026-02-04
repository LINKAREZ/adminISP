<?php

namespace App\Core\Traits;

/**
 * Trait para agregar funcionalidad de búsqueda a modelos Eloquent
 *
 * Este trait proporciona un scope reutilizable para realizar búsquedas
 * en múltiples columnas de una tabla, evitando duplicación de código.
 *
 * @package App\Core\Traits
 */
trait Searchable
{
    /**
     * Scope para buscar en múltiples columnas
     *
     * Ejemplo de uso:
     * Cliente::search($request->buscar, ['nombre', 'documento', 'telefonos'])->get();
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $search Término de búsqueda
     * @param array $columns Columnas donde buscar
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, ?string $search, array $columns)
    {
        if (empty($search) || empty($columns)) {
            return $query;
        }

        return $query->where(function ($q) use ($search, $columns) {
            foreach ($columns as $index => $column) {
                if ($index === 0) {
                    $q->where($column, 'like', "%{$search}%");
                } else {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            }
        });
    }

    /**
     * Scope para buscar en relaciones usando whereHas
     *
     * Ejemplo de uso:
     * Servicio::searchInRelation($request->buscar, 'cliente', ['nombre', 'documento'])->get();
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $search Término de búsqueda
     * @param string $relation Nombre de la relación
     * @param array $columns Columnas donde buscar en la relación
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchInRelation($query, ?string $search, string $relation, array $columns)
    {
        if (empty($search) || empty($columns)) {
            return $query;
        }

        return $query->whereHas($relation, function ($q) use ($search, $columns) {
            $q->where(function ($subQuery) use ($search, $columns) {
                foreach ($columns as $index => $column) {
                    if ($index === 0) {
                        $subQuery->where($column, 'like', "%{$search}%");
                    } else {
                        $subQuery->orWhere($column, 'like', "%{$search}%");
                    }
                }
            });
        });
    }
}
