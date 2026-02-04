<?php

namespace App\Core\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Scope global para filtrar solo registros activos
 */
class ActiveScope implements Scope
{
    /**
     * Columna a verificar
     */
    protected string $column;

    /**
     * Valor que indica activo
     */
    protected mixed $activeValue;

    public function __construct(string $column = 'estado', mixed $activeValue = 'activo')
    {
        $this->column = $column;
        $this->activeValue = $activeValue;
    }

    /**
     * Aplicar el scope al query
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($model->qualifyColumn($this->column), $this->activeValue);
    }

    /**
     * Extender el builder con métodos adicionales
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withInactive', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        $builder->macro('onlyInactive', function (Builder $builder) {
            return $builder->withoutGlobalScope($this)
                ->where($this->column, '!=', $this->activeValue);
        });
    }
}
