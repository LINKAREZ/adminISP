<?php

namespace App\Core\Utils;

use Carbon\Carbon;

/**
 * Utilidades para manipulación de fechas
 */
class DateUtils
{
    private static array $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    ];

    private static array $mesesCortos = [
        1 => 'ene', 2 => 'feb', 3 => 'mar', 4 => 'abr',
        5 => 'may', 6 => 'jun', 7 => 'jul', 8 => 'ago',
        9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dic'
    ];

    private static array $diasSemana = [
        0 => 'domingo', 1 => 'lunes', 2 => 'martes', 3 => 'miércoles',
        4 => 'jueves', 5 => 'viernes', 6 => 'sábado'
    ];

    /**
     * Obtener nombre del mes
     */
    public static function getMonthName(int $month, bool $short = false): string
    {
        $months = $short ? self::$mesesCortos : self::$meses;
        return $months[$month] ?? '';
    }

    /**
     * Obtener nombre del día de la semana
     */
    public static function getDayName(int $dayOfWeek): string
    {
        return self::$diasSemana[$dayOfWeek] ?? '';
    }

    /**
     * Formatear fecha en español
     */
    public static function formatSpanish($date, string $format = 'long'): string
    {
        if (!$date) {
            return '-';
        }

        $carbon = Carbon::parse($date);

        return match ($format) {
            'short' => $carbon->format('d/m/Y'),
            'medium' => $carbon->day . ' ' . self::getMonthName($carbon->month, true) . ' ' . $carbon->year,
            'long' => $carbon->day . ' de ' . self::getMonthName($carbon->month) . ' de ' . $carbon->year,
            'full' => self::getDayName($carbon->dayOfWeek) . ', ' . $carbon->day . ' de ' . self::getMonthName($carbon->month) . ' de ' . $carbon->year,
            'datetime' => $carbon->format('d/m/Y H:i'),
            default => $carbon->format('d/m/Y'),
        };
    }

    /**
     * Obtener período (mes/año) formateado
     */
    public static function formatPeriod(string $month, int $year): string
    {
        $monthNum = (int) $month;
        return ucfirst(self::getMonthName($monthNum)) . ' ' . $year;
    }

    /**
     * Verificar si una fecha es hoy
     */
    public static function isToday($date): bool
    {
        if (!$date) {
            return false;
        }

        return Carbon::parse($date)->isToday();
    }

    /**
     * Verificar si una fecha es pasada
     */
    public static function isPast($date): bool
    {
        if (!$date) {
            return false;
        }

        return Carbon::parse($date)->isPast();
    }

    /**
     * Verificar si una fecha es futura
     */
    public static function isFuture($date): bool
    {
        if (!$date) {
            return false;
        }

        return Carbon::parse($date)->isFuture();
    }

    /**
     * Obtener diferencia en días
     */
    public static function diffInDays($from, $to = null): int
    {
        $from = Carbon::parse($from);
        $to = $to ? Carbon::parse($to) : Carbon::now();

        return $from->diffInDays($to);
    }

    /**
     * Obtener texto de tiempo relativo
     */
    public static function timeAgo($date): string
    {
        if (!$date) {
            return '-';
        }

        Carbon::setLocale('es');
        return Carbon::parse($date)->diffForHumans();
    }

    /**
     * Obtener inicio del mes
     */
    public static function startOfMonth($date = null): Carbon
    {
        return Carbon::parse($date)->startOfMonth();
    }

    /**
     * Obtener fin del mes
     */
    public static function endOfMonth($date = null): Carbon
    {
        return Carbon::parse($date)->endOfMonth();
    }

    /**
     * Obtener array de meses para selects
     */
    public static function getMonthsForSelect(): array
    {
        $months = [];

        for ($i = 1; $i <= 12; $i++) {
            $key = str_pad($i, 2, '0', STR_PAD_LEFT);
            $months[$key] = ucfirst(self::$meses[$i]);
        }

        return $months;
    }

    /**
     * Obtener array de años para selects
     */
    public static function getYearsForSelect(int $from = null, int $to = null): array
    {
        $from = $from ?? (date('Y') - 5);
        $to = $to ?? (date('Y') + 1);

        $years = [];

        for ($i = $to; $i >= $from; $i--) {
            $years[$i] = $i;
        }

        return $years;
    }

    /**
     * Verificar si es día laborable
     */
    public static function isWorkday($date = null): bool
    {
        $carbon = Carbon::parse($date);
        return $carbon->isWeekday();
    }

    /**
     * Obtener próximo día laborable
     */
    public static function nextWorkday($date = null): Carbon
    {
        $carbon = Carbon::parse($date);

        while (!$carbon->isWeekday()) {
            $carbon->addDay();
        }

        return $carbon;
    }

    /**
     * Calcular edad a partir de fecha de nacimiento
     */
    public static function calculateAge($birthDate): int
    {
        if (!$birthDate) {
            return 0;
        }

        return Carbon::parse($birthDate)->age;
    }
}
