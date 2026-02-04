<?php

namespace App\Core\Enums;

/**
 * Estados posibles de un Recibo
 */
enum EstadoRecibo: string
{
    case PENDIENTE = 'pendiente';
    case VENCIDO = 'vencido';
    case PAGADO = 'pagado';

    /**
     * Obtener etiqueta legible
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::VENCIDO => 'Vencido',
            self::PAGADO => 'Pagado',
        };
    }

    /**
     * Obtener color para badges
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDIENTE => 'warning',
            self::VENCIDO => 'danger',
            self::PAGADO => 'success',
        };
    }

    /**
     * Obtener icono
     */
    public function icon(): string
    {
        return match ($this) {
            self::PENDIENTE => 'fa-clock',
            self::VENCIDO => 'fa-exclamation-triangle',
            self::PAGADO => 'fa-check',
        };
    }

    /**
     * Verificar si está pagado
     */
    public function isPagado(): bool
    {
        return $this === self::PAGADO;
    }

    /**
     * Verificar si está pendiente de pago
     */
    public function isPendienteDePago(): bool
    {
        return in_array($this, [self::PENDIENTE, self::VENCIDO]);
    }

    /**
     * Obtener todos los valores como array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Obtener estados pendientes de pago
     */
    public static function pendientesDePago(): array
    {
        return [self::PENDIENTE->value, self::VENCIDO->value];
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
