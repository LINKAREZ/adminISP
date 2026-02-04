<?php

namespace App\Core\Traits;

use App\Modules\ControlAcceso\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait para modelos con campo created_by/updated_by
 */
trait HasCreatedBy
{
    /**
     * Boot del trait
     */
    protected static function bootHasCreatedBy(): void
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = $model->created_by ?? auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check() && $model->hasUpdatedByColumn()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    /**
     * Verificar si tiene columna updated_by
     */
    protected function hasUpdatedByColumn(): bool
    {
        return in_array('updated_by', $this->fillable)
            || $this->getConnection()->getSchemaBuilder()->hasColumn($this->getTable(), 'updated_by');
    }

    /**
     * Relación con usuario creador
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación con usuario actualizador
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope para registros creados por un usuario
     */
    public function scopeCreatedBy($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope para registros del usuario actual
     */
    public function scopeMine($query)
    {
        return $query->where('created_by', auth()->id());
    }

    /**
     * Verificar si el usuario actual es el creador
     */
    public function isCreatedByCurrentUser(): bool
    {
        return $this->created_by === auth()->id();
    }

    /**
     * Obtener nombre del creador
     */
    public function getCreatorNameAttribute(): ?string
    {
        return $this->creator?->name;
    }
}
