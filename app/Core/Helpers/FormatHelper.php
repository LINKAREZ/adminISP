<?php

namespace App\Core\Helpers;

use Carbon\Carbon;

class FormatHelper
{
    /**
     * Formatear fecha en formato largo español
     * Ejemplo: "31 de diciembre de 2025"
     */
    public static function fechaLarga(?string $fecha): string
    {
        if (!$fecha) {
            return '-';
        }

        $carbon = Carbon::parse($fecha);

        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];

        return $carbon->day . ' de ' . $meses[$carbon->month] . ' de ' . $carbon->year;
    }

    /**
     * Formatear fecha en formato corto
     * Ejemplo: "31/12/2025"
     */
    public static function fechaCorta(?string $fecha): string
    {
        if (!$fecha) {
            return '-';
        }

        return Carbon::parse($fecha)->format('d/m/Y');
    }

    /**
     * Formatear fecha con hora
     * Ejemplo: "31/12/2025 14:30"
     */
    public static function fechaHora(?string $fecha): string
    {
        if (!$fecha) {
            return '-';
        }

        return Carbon::parse($fecha)->format('d/m/Y H:i');
    }

    /**
     * Formatear fecha relativa
     * Ejemplo: "hace 2 horas", "ayer", "hace 3 días"
     */
    public static function fechaRelativa(?string $fecha): string
    {
        if (!$fecha) {
            return '-';
        }

        Carbon::setLocale('es');
        return Carbon::parse($fecha)->diffForHumans();
    }

    /**
     * Formatear monto en soles
     * Ejemplo: "S/. 150.00"
     */
    public static function monto(?float $monto, string $moneda = 'PEN'): string
    {
        if ($monto === null) {
            return '-';
        }

        $simbolos = [
            'PEN' => 'S/.',
            'USD' => '$',
            'EUR' => '€',
        ];

        $simbolo = $simbolos[$moneda] ?? $moneda;
        return $simbolo . ' ' . number_format($monto, 2, '.', ',');
    }

    /**
     * Formatear monto en soles (alias)
     */
    public static function soles(?float $monto): string
    {
        return self::monto($monto, 'PEN');
    }

    /**
     * Formatear monto en dólares
     */
    public static function dolares(?float $monto): string
    {
        return self::monto($monto, 'USD');
    }

    /**
     * Formatear número de teléfono
     * Ejemplo: "+51 912 345 678"
     */
    public static function telefono(?string $telefono): string
    {
        if (!$telefono) {
            return '-';
        }

        // Si ya tiene formato, devolverlo
        if (str_contains($telefono, ' ')) {
            return $telefono;
        }

        // Formatear número peruano
        if (strlen($telefono) === 12 && str_starts_with($telefono, '+51')) {
            return substr($telefono, 0, 3) . ' ' . substr($telefono, 3, 3) . ' ' . substr($telefono, 6, 3) . ' ' . substr($telefono, 9, 3);
        }

        if (strlen($telefono) === 9) {
            return substr($telefono, 0, 3) . ' ' . substr($telefono, 3, 3) . ' ' . substr($telefono, 6, 3);
        }

        return $telefono;
    }

    /**
     * Formatear DNI
     * Ejemplo: "12.345.678"
     */
    public static function dni(?string $dni): string
    {
        if (!$dni) {
            return '-';
        }

        if (strlen($dni) === 8) {
            return substr($dni, 0, 2) . '.' . substr($dni, 2, 3) . '.' . substr($dni, 5, 3);
        }

        return $dni;
    }

    /**
     * Formatear RUC
     * Ejemplo: "20.123.456.789"
     */
    public static function ruc(?string $ruc): string
    {
        if (!$ruc) {
            return '-';
        }

        if (strlen($ruc) === 11) {
            return substr($ruc, 0, 2) . '.' . substr($ruc, 2, 3) . '.' . substr($ruc, 5, 3) . '.' . substr($ruc, 8, 3);
        }

        return $ruc;
    }

    /**
     * Formatear dirección MAC
     * Ejemplo: "AA:BB:CC:DD:EE:FF"
     */
    public static function mac(?string $mac): string
    {
        if (!$mac) {
            return '-';
        }

        return strtoupper($mac);
    }

    /**
     * Formatear dirección IP
     */
    public static function ip(?string $ip): string
    {
        return $ip ?? '-';
    }

    /**
     * Formatear bytes a unidad legible
     * Ejemplo: "1.5 GB", "256 MB"
     */
    public static function bytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Formatear velocidad de internet
     * Ejemplo: "100 Mbps"
     */
    public static function velocidad(int $mbps): string
    {
        if ($mbps >= 1000) {
            return ($mbps / 1000) . ' Gbps';
        }
        return $mbps . ' Mbps';
    }

    /**
     * Formatear período (mes/año)
     * Ejemplo: "Diciembre 2025"
     */
    public static function periodo(string $mes, int $ano): string
    {
        $meses = [
            '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
            '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
            '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
        ];

        return ($meses[$mes] ?? $mes) . ' ' . $ano;
    }

    /**
     * Formatear porcentaje
     * Ejemplo: "75.5%"
     */
    public static function porcentaje(float $valor, int $decimales = 1): string
    {
        return number_format($valor, $decimales, '.', ',') . '%';
    }

    /**
     * Truncar texto con puntos suspensivos
     */
    public static function truncar(?string $texto, int $longitud = 50, string $sufijo = '...'): string
    {
        if (!$texto) {
            return '-';
        }

        if (mb_strlen($texto) <= $longitud) {
            return $texto;
        }

        return mb_substr($texto, 0, $longitud) . $sufijo;
    }

    /**
     * Capitalizar nombre completo
     * Ejemplo: "JUAN CARLOS PÉREZ" -> "Juan Carlos Pérez"
     */
    public static function nombrePropio(?string $nombre): string
    {
        if (!$nombre) {
            return '-';
        }

        return mb_convert_case(mb_strtolower($nombre), MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Obtener iniciales de un nombre
     * Ejemplo: "Juan Carlos Pérez" -> "JCP"
     */
    public static function iniciales(?string $nombre): string
    {
        if (!$nombre) {
            return '-';
        }

        $partes = explode(' ', trim($nombre));
        $iniciales = '';

        foreach ($partes as $parte) {
            if (!empty($parte)) {
                $iniciales .= mb_strtoupper(mb_substr($parte, 0, 1));
            }
        }

        return $iniciales;
    }

    /**
     * Formatear estado de servicio con color
     */
    public static function estadoServicio(string $estado): array
    {
        $estados = [
            'activo' => ['label' => 'Activo', 'color' => 'success', 'icon' => 'fa-check-circle'],
            'cortado' => ['label' => 'Cortado', 'color' => 'danger', 'icon' => 'fa-times-circle'],
            'suspendido' => ['label' => 'Suspendido', 'color' => 'warning', 'icon' => 'fa-pause-circle'],
        ];

        return $estados[$estado] ?? ['label' => ucfirst($estado), 'color' => 'secondary', 'icon' => 'fa-question-circle'];
    }

    /**
     * Formatear estado de recibo con color
     */
    public static function estadoRecibo(string $estado): array
    {
        $estados = [
            'pendiente' => ['label' => 'Pendiente', 'color' => 'info', 'icon' => 'fa-clock'],
            'vencido' => ['label' => 'Vencido', 'color' => 'danger', 'icon' => 'fa-exclamation-circle'],
            'pagado' => ['label' => 'Pagado', 'color' => 'success', 'icon' => 'fa-check-circle'],
        ];

        return $estados[$estado] ?? ['label' => ucfirst($estado), 'color' => 'secondary', 'icon' => 'fa-question-circle'];
    }

    /**
     * Convertir número a letras (para montos en comprobantes)
     * Ejemplo: 150.50 -> "ciento cincuenta con 50/100"
     */
    public static function numeroALetras(float $numero): string
    {
        $entero = (int) floor($numero);
        $decimales = (int) round(($numero - $entero) * 100);

        $letras = self::convertirNumeroALetras($entero);

        if ($decimales > 0) {
            $letras .= " con {$decimales}/100";
        } else {
            $letras .= " con 00/100";
        }

        return $letras;
    }

    /**
     * Convertir parte entera del número a letras
     */
    private static function convertirNumeroALetras(int $numero): string
    {
        $unidades = [
            '', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve',
            'diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete',
            'dieciocho', 'diecinueve', 'veinte', 'veintiuno', 'veintidós', 'veintitrés',
            'veinticuatro', 'veinticinco', 'veintiséis', 'veintisiete', 'veintiocho', 'veintinueve'
        ];

        $decenas = [
            '', '', '', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'
        ];

        $centenas = [
            '', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos',
            'seiscientos', 'setecientos', 'ochocientos', 'novecientos'
        ];

        if ($numero == 0) {
            return 'cero';
        }

        if ($numero == 100) {
            return 'cien';
        }

        if ($numero < 30) {
            return $unidades[$numero];
        }

        if ($numero < 100) {
            $decena = (int) floor($numero / 10);
            $unidad = $numero % 10;
            return $decenas[$decena] . ($unidad > 0 ? ' y ' . $unidades[$unidad] : '');
        }

        if ($numero < 1000) {
            $centena = (int) floor($numero / 100);
            $resto = $numero % 100;
            return $centenas[$centena] . ($resto > 0 ? ' ' . self::convertirNumeroALetras($resto) : '');
        }

        if ($numero < 1000000) {
            $miles = (int) floor($numero / 1000);
            $resto = $numero % 1000;

            if ($miles == 1) {
                $milesLetras = 'mil';
            } else {
                $milesLetras = self::convertirNumeroALetras($miles) . ' mil';
            }

            return $milesLetras . ($resto > 0 ? ' ' . self::convertirNumeroALetras($resto) : '');
        }

        if ($numero < 1000000000) {
            $millones = (int) floor($numero / 1000000);
            $resto = $numero % 1000000;

            if ($millones == 1) {
                $millonesLetras = 'un millón';
            } else {
                $millonesLetras = self::convertirNumeroALetras($millones) . ' millones';
            }

            return $millonesLetras . ($resto > 0 ? ' ' . self::convertirNumeroALetras($resto) : '');
        }

        return (string) $numero;
    }
}
