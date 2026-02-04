<?php

namespace App\Core\Enums;

/**
 * Estados posibles de una Promesa de Pago
 */
enum EstadoPromesaPago: string
{
    case PENDIENTE = 'pendiente';
    case VENCIDA = 'vencida';
    case CUMPLIDA = 'cumplida';
    case CANCELADA = 'cancelada';

    /**
     * Obtener etiqueta legible
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::VENCIDA => 'Vencida',
            self::CUMPLIDA => 'Cumplida',
            self::CANCELADA => 'Cancelada',
        };
    }

    /**
     * Obtener color para badges
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDIENTE => 'warning',
            self::VENCIDA => 'danger',
            self::CUMPLIDA => 'success',
            self::CANCELADA => 'secondary',
        };
    }

    /**
     * Obtener icono
     */
    public function icon(): string
    {
        return match ($this) {
            self::PENDIENTE => 'fa-clock',
            self::VENCIDA => 'fa-exclamation-triangle',
            self::CUMPLIDA => 'fa-check',
            self::CANCELADA => 'fa-ban',
        };
    }

    /**
     * Verificar si está activa (pendiente o vencida)
     */
    public function isActiva(): bool
    {
        return in_array($this, [self::PENDIENTE, self::VENCIDA]);
    }

    /**
     * Verificar si está finalizada (cumplida o cancelada)
     */
    public function isFinalizada(): bool
    {
        return in_array($this, [self::CUMPLIDA, self::CANCELADA]);
    }

    /**
     * Obtener todos los valores como array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Obtener estados activos
     */
    public static function activos(): array
    {
        return [self::PENDIENTE->value, self::VENCIDA->value];
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
