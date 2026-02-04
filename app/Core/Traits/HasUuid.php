<?php

namespace App\Core\Traits;

use Illuminate\Support\Str;

/**
 * Trait para modelos con UUID
 */
trait HasUuid
{
    /**
     * Boot del trait
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getUuidColumn()})) {
                $model->{$model->getUuidColumn()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Obtener columna UUID
     */
    public function getUuidColumn(): string
    {
        return 'uuid';
    }

    /**
     * Scope para buscar por UUID
     */
    public function scopeWhereUuid($query, string $uuid)
    {
        return $query->where($this->getUuidColumn(), $uuid);
    }

    /**
     * Buscar por UUID o fallar
     */
    public static function findByUuidOrFail(string $uuid)
    {
        return static::whereUuid($uuid)->firstOrFail();
    }

    /**
     * Buscar por UUID
     */
    public static function findByUuid(string $uuid)
    {
        return static::whereUuid($uuid)->first();
    }

    /**
     * Obtener la clave de ruta (usar UUID en lugar de ID)
     */
    public function getRouteKeyName(): string
    {
        return $this->getUuidColumn();
    }
}
