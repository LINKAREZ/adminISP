<?php

namespace App\Core\Constants;

/**
 * Constantes globales de la aplicación
 */
class AppConstants
{
    // Paginación
    public const PAGINATION_DEFAULT = 15;
    public const PAGINATION_SMALL = 10;
    public const PAGINATION_LARGE = 25;
    public const PAGINATION_MAX = 100;

    // Cache TTL (en segundos)
    public const CACHE_SHORT = 300;       // 5 minutos
    public const CACHE_MEDIUM = 1800;     // 30 minutos
    public const CACHE_LONG = 3600;       // 1 hora
    public const CACHE_DAY = 86400;       // 1 día
    public const CACHE_WEEK = 604800;     // 1 semana

    // Estados de servicio
    public const ESTADO_SERVICIO_ACTIVO = 'activo';
    public const ESTADO_SERVICIO_CORTADO = 'cortado';
    public const ESTADO_SERVICIO_SUSPENDIDO = 'suspendido';

    // Estados de recibo
    public const ESTADO_RECIBO_PENDIENTE = 'pendiente';
    public const ESTADO_RECIBO_VENCIDO = 'vencido';
    public const ESTADO_RECIBO_PAGADO = 'pagado';

    // Estados de promesa de pago
    public const ESTADO_PROMESA_PENDIENTE = 'pendiente';
    public const ESTADO_PROMESA_VENCIDA = 'vencida';
    public const ESTADO_PROMESA_CUMPLIDA = 'cumplida';
    public const ESTADO_PROMESA_CANCELADA = 'cancelada';

    // Tipos de documento
    public const TIPO_DOCUMENTO_DNI = 'dni';
    public const TIPO_DOCUMENTO_RUC = 'ruc';
    public const TIPO_DOCUMENTO_CE = 'ce';
    public const TIPO_DOCUMENTO_OTRO = 'otro';

    // Tipos de medio de pago
    public const TIPO_PAGO_EFECTIVO = 'efectivo';
    public const TIPO_PAGO_YAPE = 'yape';
    public const TIPO_PAGO_PLIN = 'plin';
    public const TIPO_PAGO_TRANSFERENCIA = 'transferencia';
    public const TIPO_PAGO_DEPOSITO = 'deposito';

    // Tipos de comprobante
    public const TIPO_COMPROBANTE_BOLETA = 'boleta';
    public const TIPO_COMPROBANTE_FACTURA = 'factura';
    public const TIPO_COMPROBANTE_NOTA_VENTA = 'nota_venta';

    // Tipos de conexión
    public const TIPO_CONEXION_PPPOE = 'pppoe';
    public const TIPO_CONEXION_IP_FIJA = 'ip_fija';
    public const TIPO_CONEXION_DHCP = 'dhcp';

    // Meses
    public const MESES = [
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre',
    ];

    // Días de la semana
    public const DIAS_SEMANA = [
        0 => 'Domingo',
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
    ];

    // Formatos de fecha
    public const FORMATO_FECHA = 'd/m/Y';
    public const FORMATO_FECHA_HORA = 'd/m/Y H:i';
    public const FORMATO_FECHA_LARGA = 'd \d\e F \d\e Y';
    public const FORMATO_HORA = 'H:i';

    // Moneda
    public const MONEDA_CODIGO = 'PEN';
    public const MONEDA_SIMBOLO = 'S/.';
    public const MONEDA_NOMBRE = 'Soles';

    // Límites de archivos
    public const MAX_FILE_SIZE_KB = 5120;      // 5 MB
    public const MAX_IMAGE_SIZE_KB = 2048;     // 2 MB
    public const ALLOWED_IMAGE_TYPES = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
    public const ALLOWED_DOCUMENT_TYPES = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];

    // MikroTik
    public const MIKROTIK_DEFAULT_PORT = 8728;
    public const MIKROTIK_API_SSL_PORT = 8729;
    public const MIKROTIK_TIMEOUT = 10;

    // Obtener estados como array para selects
    public static function getEstadosServicio(): array
    {
        return [
            self::ESTADO_SERVICIO_ACTIVO => 'Activo',
            self::ESTADO_SERVICIO_CORTADO => 'Cortado',
            self::ESTADO_SERVICIO_SUSPENDIDO => 'Suspendido',
        ];
    }

    public static function getEstadosRecibo(): array
    {
        return [
            self::ESTADO_RECIBO_PENDIENTE => 'Pendiente',
            self::ESTADO_RECIBO_VENCIDO => 'Vencido',
            self::ESTADO_RECIBO_PAGADO => 'Pagado',
        ];
    }

    public static function getTiposDocumento(): array
    {
        return [
            self::TIPO_DOCUMENTO_DNI => 'DNI',
            self::TIPO_DOCUMENTO_RUC => 'RUC',
            self::TIPO_DOCUMENTO_CE => 'Carné de Extranjería',
            self::TIPO_DOCUMENTO_OTRO => 'Otro',
        ];
    }

    public static function getTiposMedioPago(): array
    {
        return [
            self::TIPO_PAGO_EFECTIVO => 'Efectivo',
            self::TIPO_PAGO_YAPE => 'Yape',
            self::TIPO_PAGO_PLIN => 'Plin',
            self::TIPO_PAGO_TRANSFERENCIA => 'Transferencia',
            self::TIPO_PAGO_DEPOSITO => 'Depósito',
        ];
    }
}
