<?php

use App\Core\Helpers\FormatHelper;

if (!function_exists('formato_fecha')) {
    /**
     * Formatear fecha en formato corto
     */
    function formato_fecha(?string $fecha): string
    {
        return FormatHelper::fechaCorta($fecha);
    }
}

if (!function_exists('formato_fecha_larga')) {
    /**
     * Formatear fecha en formato largo
     */
    function formato_fecha_larga(?string $fecha): string
    {
        return FormatHelper::fechaLarga($fecha);
    }
}

if (!function_exists('formato_fecha_hora')) {
    /**
     * Formatear fecha con hora
     */
    function formato_fecha_hora(?string $fecha): string
    {
        return FormatHelper::fechaHora($fecha);
    }
}

if (!function_exists('formato_fecha_relativa')) {
    /**
     * Formatear fecha relativa (hace X tiempo)
     */
    function formato_fecha_relativa(?string $fecha): string
    {
        return FormatHelper::fechaRelativa($fecha);
    }
}

if (!function_exists('formato_monto')) {
    /**
     * Formatear monto con símbolo de moneda
     */
    function formato_monto(?float $monto, string $moneda = 'PEN'): string
    {
        return FormatHelper::monto($monto, $moneda);
    }
}

if (!function_exists('formato_soles')) {
    /**
     * Formatear monto en soles
     */
    function formato_soles(?float $monto): string
    {
        return FormatHelper::soles($monto);
    }
}

if (!function_exists('formato_telefono')) {
    /**
     * Formatear número de teléfono
     */
    function formato_telefono(?string $telefono): string
    {
        return FormatHelper::telefono($telefono);
    }
}

if (!function_exists('formato_dni')) {
    /**
     * Formatear DNI
     */
    function formato_dni(?string $dni): string
    {
        return FormatHelper::dni($dni);
    }
}

if (!function_exists('formato_ruc')) {
    /**
     * Formatear RUC
     */
    function formato_ruc(?string $ruc): string
    {
        return FormatHelper::ruc($ruc);
    }
}

if (!function_exists('formato_mac')) {
    /**
     * Formatear dirección MAC
     */
    function formato_mac(?string $mac): string
    {
        return FormatHelper::mac($mac);
    }
}

if (!function_exists('formato_ip')) {
    /**
     * Formatear dirección IP
     */
    function formato_ip(?string $ip): string
    {
        return FormatHelper::ip($ip);
    }
}

if (!function_exists('formato_bytes')) {
    /**
     * Formatear bytes a unidad legible
     */
    function formato_bytes(int $bytes, int $precision = 2): string
    {
        return FormatHelper::bytes($bytes, $precision);
    }
}

if (!function_exists('formato_velocidad')) {
    /**
     * Formatear velocidad de internet
     */
    function formato_velocidad(int $mbps): string
    {
        return FormatHelper::velocidad($mbps);
    }
}

if (!function_exists('formato_periodo')) {
    /**
     * Formatear período (mes/año)
     */
    function formato_periodo(string $mes, int $ano): string
    {
        return FormatHelper::periodo($mes, $ano);
    }
}

if (!function_exists('formato_porcentaje')) {
    /**
     * Formatear porcentaje
     */
    function formato_porcentaje(float $valor, int $decimales = 1): string
    {
        return FormatHelper::porcentaje($valor, $decimales);
    }
}

if (!function_exists('formato_truncar')) {
    /**
     * Truncar texto con puntos suspensivos
     */
    function formato_truncar(?string $texto, int $longitud = 50): string
    {
        return FormatHelper::truncar($texto, $longitud);
    }
}

if (!function_exists('formato_nombre')) {
    /**
     * Capitalizar nombre completo
     */
    function formato_nombre(?string $nombre): string
    {
        return FormatHelper::nombrePropio($nombre);
    }
}

if (!function_exists('formato_iniciales')) {
    /**
     * Obtener iniciales de un nombre
     */
    function formato_iniciales(?string $nombre): string
    {
        return FormatHelper::iniciales($nombre);
    }
}

if (!function_exists('estado_servicio')) {
    /**
     * Obtener información de estado de servicio
     */
    function estado_servicio(string $estado): array
    {
        return FormatHelper::estadoServicio($estado);
    }
}

if (!function_exists('estado_recibo')) {
    /**
     * Obtener información de estado de recibo
     */
    function estado_recibo(string $estado): array
    {
        return FormatHelper::estadoRecibo($estado);
    }
}
