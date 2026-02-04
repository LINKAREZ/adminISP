<?php

namespace App\Core\Utils;

/**
 * Utilidades para manipulación de números
 */
class NumberUtils
{
    /**
     * Formatear monto con símbolo de moneda
     */
    public static function formatCurrency(float $amount, string $currency = 'PEN'): string
    {
        $symbols = [
            'PEN' => 'S/.',
            'USD' => '$',
            'EUR' => '€',
        ];

        $symbol = $symbols[$currency] ?? $currency;
        return $symbol . ' ' . number_format($amount, 2, '.', ',');
    }

    /**
     * Formatear monto en soles
     */
    public static function formatSoles(float $amount): string
    {
        return self::formatCurrency($amount, 'PEN');
    }

    /**
     * Formatear porcentaje
     */
    public static function formatPercentage(float $value, int $decimals = 1): string
    {
        return number_format($value, $decimals, '.', ',') . '%';
    }

    /**
     * Formatear bytes a unidad legible
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Formatear velocidad de internet
     */
    public static function formatSpeed(int $mbps): string
    {
        if ($mbps >= 1000) {
            return round($mbps / 1000, 1) . ' Gbps';
        }
        return $mbps . ' Mbps';
    }

    /**
     * Convertir a número con precisión específica
     */
    public static function toFixed(float $number, int $decimals = 2): float
    {
        return round($number, $decimals);
    }

    /**
     * Calcular porcentaje
     */
    public static function percentage(float $value, float $total): float
    {
        if ($total == 0) {
            return 0;
        }

        return round(($value / $total) * 100, 2);
    }

    /**
     * Calcular variación porcentual
     */
    public static function percentageChange(float $oldValue, float $newValue): float
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }

        return round((($newValue - $oldValue) / $oldValue) * 100, 2);
    }

    /**
     * Verificar si está en rango
     */
    public static function inRange(float $value, float $min, float $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    /**
     * Limitar valor a un rango
     */
    public static function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }

    /**
     * Formatear número con separadores
     */
    public static function formatNumber(float $number, int $decimals = 0): string
    {
        return number_format($number, $decimals, '.', ',');
    }

    /**
     * Convertir a número ordinal
     */
    public static function ordinal(int $number): string
    {
        if ($number === 1) return '1ero';
        if ($number === 2) return '2do';
        if ($number === 3) return '3ero';

        return $number . 'to';
    }

    /**
     * Redondear al múltiplo más cercano
     */
    public static function roundToNearest(float $number, float $nearest): float
    {
        return round($number / $nearest) * $nearest;
    }

    /**
     * Parsear string a número
     */
    public static function parseNumber(?string $value): float
    {
        if (!$value) {
            return 0;
        }

        // Remover todo excepto números, punto y signo negativo
        $cleaned = preg_replace('/[^0-9.\-]/', '', $value);

        return (float) $cleaned;
    }

    /**
     * Verificar si es un número válido
     */
    public static function isValidNumber($value): bool
    {
        return is_numeric($value);
    }

    /**
     * Generar número aleatorio en rango
     */
    public static function randomInRange(int $min, int $max): int
    {
        return random_int($min, $max);
    }
}
