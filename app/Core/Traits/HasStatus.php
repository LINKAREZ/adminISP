<?php

namespace App\Core\Traits;

/**
 * Trait para modelos con campo de estado
 */
trait HasStatus
{
    /**
     * Columna de estado
     */
    protected function getStatusColumn(): string
    {
        return 'estado';
    }

    /**
     * Valor para estado activo
     */
    protected function getActiveStatusValue(): string
    {
        return 'activo';
    }

    /**
     * Verificar si está activo
     */
    public function isActive(): bool
    {
        return $this->{$this->getStatusColumn()} === $this->getActiveStatusValue();
    }

    /**
     * Verificar si está inactivo
     */
    public function isInactive(): bool
    {
        return !$this->isActive();
    }

    /**
     * Activar el registro
     */
    public function activate(): bool
    {
        $this->{$this->getStatusColumn()} = $this->getActiveStatusValue();
        return $this->save();
    }

    /**
     * Desactivar el registro
     */
    public function deactivate(string $status = 'inactivo'): bool
    {
        $this->{$this->getStatusColumn()} = $status;
        return $this->save();
    }

    /**
     * Cambiar estado
     */
    public function setStatus(string $status): bool
    {
        $this->{$this->getStatusColumn()} = $status;
        return $this->save();
    }

    /**
     * Scope para activos
     */
    public function scopeActive($query)
    {
        return $query->where($this->getStatusColumn(), $this->getActiveStatusValue());
    }

    /**
     * Scope para inactivos
     */
    public function scopeInactive($query)
    {
        return $query->where($this->getStatusColumn(), '!=', $this->getActiveStatusValue());
    }

    /**
     * Scope para un estado específico
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where($this->getStatusColumn(), $status);
    }

    /**
     * Obtener etiqueta del estado
     */
    public function getStatusLabelAttribute(): string
    {
        $status = $this->{$this->getStatusColumn()};
        return ucfirst($status);
    }

    /**
     * Obtener color del estado (para badges)
     */
    public function getStatusColorAttribute(): string
    {
        $status = $this->{$this->getStatusColumn()};

        return match ($status) {
            'activo' => 'success',
            'inactivo', 'cortado', 'suspendido' => 'danger',
            'pendiente' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Obtener icono del estado
     */
    public function getStatusIconAttribute(): string
    {
        $status = $this->{$this->getStatusColumn()};

        return match ($status) {
            'activo' => 'fa-check-circle',
            'inactivo', 'cortado' => 'fa-times-circle',
            'suspendido' => 'fa-pause-circle',
            'pendiente' => 'fa-clock',
            default => 'fa-question-circle',
        };
    }
}
