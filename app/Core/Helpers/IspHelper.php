<?php

namespace App\Core\Helpers;

use App\Modules\Sistema\Models\Isp;

class IspHelper
{
    /**
     * Obtener el ISP actual
     */
    public static function current(): ?Isp
    {
        if (auth()->check() && auth()->user()->isp_id) {
            return Isp::find(auth()->user()->isp_id);
        }

        if (session()->has('current_isp_id')) {
            return Isp::find(session('current_isp_id'));
        }

        return null;
    }

    /**
     * Obtener configuración del ISP actual
     *
     * @param string $key Key de configuración (ej: 'empresa.nombre', 'comprobantes.moneda')
     * @param mixed $default Valor por defecto si no se encuentra
     * @return mixed
     */
    public static function config(string $key, $default = null)
    {
        $isp = static::current();

        if (!$isp) {
            return config("isp.{$key}", $default);
        }

        // Mapear keys de config a campos del ISP
        $mapping = [
            'empresa.nombre' => 'nombre',
            'empresa.ruc' => 'ruc',
            'empresa.direccion' => 'direccion',
            'empresa.telefono' => 'telefono',
            'empresa.email' => 'email',
            'empresa.logo' => 'logo',
            'comprobantes.moneda' => 'moneda',
            'comprobantes.simbolo_moneda' => 'simbolo_moneda',
            'comprobantes.igv' => 'igv',
            'comprobantes.dia_emision' => 'dia_emision',
            'comprobantes.dias_gracia' => 'dias_gracia',
            'comprobantes.dias_vencimiento' => 'dias_vencimiento',
            'comprobantes.generar_recibos_automaticos' => 'generar_recibos_automaticos',
            'comprobantes.serie_boleta' => 'serie_boleta',
            'comprobantes.serie_factura' => 'serie_factura',
            'servicios.corte_automatico' => 'corte_automatico',
            'servicios.dias_antes_corte' => 'dias_antes_corte',
            'servicios.reactivacion_automatica' => 'reactivacion_automatica',
            'servicios.notificar_vencimiento' => 'notificar_vencimiento',
            'servicios.dias_notificacion_vencimiento' => 'dias_notificacion_vencimiento',
            'notificaciones.whatsapp_habilitado' => 'whatsapp_habilitado',
            'notificaciones.email_habilitado' => 'email_habilitado',
            'notificaciones.sms_habilitado' => 'sms_habilitado',
        ];

        if (isset($mapping[$key])) {
            return $isp->{$mapping[$key]} ?? $default;
        }

        return config("isp.{$key}", $default);
    }
}
