<?php

/**
 * Configuración específica del Admin ISP
 *
 * NOTA: A partir de la versión multi-tenant, estos valores son por defecto.
 * Los valores reales se obtienen del modelo Isp en la base de datos.
 * Si no hay ISP configurado, se usan estos valores como fallback.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Información de la Empresa
    |--------------------------------------------------------------------------
    | Valores por defecto. Los valores reales se obtienen del modelo Isp.
    */
    'empresa' => [
        'nombre' => env('ISP_EMPRESA_NOMBRE', 'Admin ISP'),
        'ruc' => env('ISP_EMPRESA_RUC', ''),
        'direccion' => env('ISP_EMPRESA_DIRECCION', ''),
        'telefono' => env('ISP_EMPRESA_TELEFONO', ''),
        'email' => env('ISP_EMPRESA_EMAIL', ''),
        'logo' => env('ISP_EMPRESA_LOGO', '/images/logo.png'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Comprobantes (moneda, IGV, series, etc.)
    |--------------------------------------------------------------------------
    */
    'comprobantes' => [
        'moneda' => env('ISP_MONEDA', 'PEN'),
        'simbolo_moneda' => env('ISP_SIMBOLO_MONEDA', 'S/.'),
        'igv' => env('ISP_IGV', 18),
        // Día del mes en que se genera/emite el recibo (ej. 20 = día 20; el cron puede correr ese día)
        'dia_emision' => env('ISP_DIA_EMISION', 20),
        // Días de gracia después del vencimiento para efectuar el corte (ej. 7 = corte el día 7 del mes siguiente si vence 31)
        'dias_gracia' => env('ISP_DIAS_GRACIA', 7),
        // Días desde fecha_emision hasta fecha_vencimiento (ej. 11 con emisión 20 → vence día 31)
        'dias_vencimiento' => env('ISP_DIAS_VENCIMIENTO', 11),
        'generar_recibos_automaticos' => env('ISP_GENERAR_RECIBOS_AUTO', true),
        'serie_boleta' => env('ISP_SERIE_BOLETA', 'B001'),
        'serie_factura' => env('ISP_SERIE_FACTURA', 'F001'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Servicios
    |--------------------------------------------------------------------------
    */
    'servicios' => [
        'corte_automatico' => env('ISP_CORTE_AUTOMATICO', true),
        'dias_antes_corte' => env('ISP_DIAS_ANTES_CORTE', 0),
        'reactivacion_automatica' => env('ISP_REACTIVACION_AUTO', true),
        'notificar_vencimiento' => env('ISP_NOTIFICAR_VENCIMIENTO', true),
        'dias_notificacion_vencimiento' => env('ISP_DIAS_NOTIF_VENC', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de MikroTik
    |--------------------------------------------------------------------------
    */
    'mikrotik' => [
        'puerto_api' => env('MIKROTIK_API_PORT', 8728),
        'puerto_api_ssl' => env('MIKROTIK_API_SSL_PORT', 8729),
        'timeout' => env('MIKROTIK_TIMEOUT', 10),
        'usar_ssl' => env('MIKROTIK_USE_SSL', false),
        'prefijo_script' => env('MIKROTIK_SCRIPT_PREFIX', 'ISP_'),
        'prefijo_scheduler' => env('MIKROTIK_SCHEDULER_PREFIX', 'CORTE_'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Notificaciones
    |--------------------------------------------------------------------------
    */
    'recordatorio_pago' => [
        'dias_antes' => (int) (env('ISP_RECORDATORIO_DIAS_ANTES') ?: 3),
        'enabled' => env('ISP_RECORDATORIO_CORREO_ENABLED', true),
    ],

    'notificaciones' => [
        'whatsapp_habilitado' => env('ISP_WHATSAPP_ENABLED', false),
        'email_habilitado' => env('ISP_EMAIL_ENABLED', false),
        'sms_habilitado' => env('ISP_SMS_ENABLED', false),
        'notificar_nuevo_pago' => env('ISP_NOTIF_NUEVO_PAGO', true),
        'notificar_servicio_cortado' => env('ISP_NOTIF_CORTE', true),
        'notificar_servicio_activado' => env('ISP_NOTIF_ACTIVACION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Paginación
    |--------------------------------------------------------------------------
    */
    'paginacion' => [
        'default' => env('ISP_PAGINACION_DEFAULT', 15),
        'clientes' => env('ISP_PAGINACION_CLIENTES', 20),
        'servicios' => env('ISP_PAGINACION_SERVICIOS', 15),
        'recibos' => env('ISP_PAGINACION_RECIBOS', 20),
        'auditoria' => env('ISP_PAGINACION_AUDITORIA', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Archivos
    |--------------------------------------------------------------------------
    */
    'archivos' => [
        'max_size_kb' => env('ISP_MAX_FILE_SIZE', 5120),
        'max_image_size_kb' => env('ISP_MAX_IMAGE_SIZE', 2048),
        'allowed_images' => ['jpeg', 'jpg', 'png', 'gif', 'webp'],
        'allowed_documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
        'disk_capturas' => env('ISP_DISK_CAPTURAS', 'public'),
        'disk_comprobantes' => env('ISP_DISK_COMPROBANTES', 'public'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Cache
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'ttl_corto' => 300,      // 5 minutos
        'ttl_medio' => 1800,     // 30 minutos
        'ttl_largo' => 3600,     // 1 hora
        'ttl_dia' => 86400,      // 1 día
        'prefijo' => 'isp_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Dashboard
    |--------------------------------------------------------------------------
    */
    'dashboard' => [
        'mostrar_servicios_cortados' => true,
        'mostrar_recibos_vencidos' => true,
        'mostrar_pagos_duplicados' => true,
        'mostrar_conexiones_activas' => true,
        'dias_recibos_recientes' => 30,
    ],
];
