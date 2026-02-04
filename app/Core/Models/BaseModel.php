<?php

namespace App\Core\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Clase base para modelos
 */
abstract class BaseModel extends Model
{
    use HasFactory, Auditable;

    /**
     * Campos de búsqueda por defecto
     */
    protected array $searchableFields = [];

    /**
     * Relaciones a cargar por defecto
     */
    protected array $defaultRelations = [];

    /**
     * Scope para búsqueda
     */
    public function scopeSearch($query, ?string $term)
    {
        if (empty($term) || empty($this->searchableFields)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            foreach ($this->searchableFields as $field) {
                if (str_contains($field, '.')) {
                    // Búsqueda en relación
                    [$relation, $relationField] = explode('.', $field, 2);
                    $q->orWhereHas($relation, function ($rq) use ($relationField, $term) {
                        $rq->where($relationField, 'LIKE', "%{$term}%");
                    });
                } else {
                    $q->orWhere($field, 'LIKE', "%{$term}%");
                }
            }
        });
    }

    /**
     * Scope para filtrar por estado activo
     */
    public function scopeActive($query)
    {
        if (in_array('estado', $this->fillable)) {
            return $query->where('estado', 'activo');
        }

        if (in_array('activo', $this->fillable)) {
            return $query->where('activo', true);
        }

        return $query;
    }

    /**
     * Scope para filtrar por estado inactivo
     */
    public function scopeInactive($query)
    {
        if (in_array('estado', $this->fillable)) {
            return $query->where('estado', '!=', 'activo');
        }

        if (in_array('activo', $this->fillable)) {
            return $query->where('activo', false);
        }

        return $query;
    }

    /**
     * Scope para ordenar por más recientes
     */
    public function scopeLatest($query, string $column = 'created_at')
    {
        return $query->orderBy($column, 'desc');
    }

    /**
     * Scope para ordenar por más antiguos
     */
    public function scopeOldest($query, string $column = 'created_at')
    {
        return $query->orderBy($column, 'asc');
    }

    /**
     * Scope para filtrar por fecha
     */
    public function scopeCreatedBetween($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeDateRange($query, string $column, $from, $to)
    {
        if ($from) {
            $query->where($column, '>=', $from);
        }

        if ($to) {
            $query->where($column, '<=', $to);
        }

        return $query;
    }

    /**
     * Scope para cargar relaciones por defecto
     */
    public function scopeWithDefaults($query)
    {
        if (!empty($this->defaultRelations)) {
            return $query->with($this->defaultRelations);
        }

        return $query;
    }

    /**
     * Obtener valor formateado de un atributo
     */
    public function getFormattedAttribute(string $attribute, string $format = null): mixed
    {
        $value = $this->getAttribute($attribute);

        if ($value === null) {
            return null;
        }

        if ($format === 'date') {
            return \Carbon\Carbon::parse($value)->format('d/m/Y');
        }

        if ($format === 'datetime') {
            return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
        }

        if ($format === 'money') {
            return 'S/. ' . number_format($value, 2);
        }

        return $value;
    }

    /**
     * Verificar si el modelo tiene un campo específico
     */
    public function hasAttribute(string $attribute): bool
    {
        return array_key_exists($attribute, $this->attributes)
            || in_array($attribute, $this->fillable);
    }

    /**
     * Obtener el nombre legible del modelo
     */
    public static function getModelLabel(): string
    {
        return class_basename(static::class);
    }

    /**
     * Obtener el nombre plural del modelo
     */
    public static function getModelLabelPlural(): string
    {
        return static::getModelLabel() . 's';
    }
}
