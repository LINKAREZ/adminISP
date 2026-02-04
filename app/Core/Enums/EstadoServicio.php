<?php

namespace App\Core\Enums;

/**
 * Estados posibles de un Servicio
 */
enum EstadoServicio: string
{
    case ACTIVO = 'activo';
    case CORTADO = 'cortado';

    /**
     * Obtener etiqueta legible
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVO => 'Activo',
            self::CORTADO => 'Cortado',
        };
    }

    /**
     * Obtener color para badges
     */
    public function color(): string
    {
        return match ($this) {
            self::ACTIVO => 'success',
            self::CORTADO => 'danger',
        };
    }

    /**
     * Obtener icono
     */
    public function icon(): string
    {
        return match ($this) {
            self::ACTIVO => 'fa-check-circle',
            self::CORTADO => 'fa-times-circle',
        };
    }

    /**
     * Verificar si está activo
     */
    public function isActivo(): bool
    {
        return $this === self::ACTIVO;
    }

    /**
     * Verificar si está cortado
     */
    public function isCortado(): bool
    {
        return $this === self::CORTADO;
    }

    /**
     * Obtener todos los valores como array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Obtener opciones para select
     */
    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn($case) => $case->label(), self::cases())
        );
    }
}
