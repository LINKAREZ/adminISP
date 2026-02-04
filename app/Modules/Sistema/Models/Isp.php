<?php

namespace App\Modules\Sistema\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Isp extends Model
{
    use Auditable;

    protected $connection = 'mysql';

    protected $fillable = [
        'database_name',
        'nombre',
        'ruc',
        'direccion',
        'telefono',
        'email',
        'logo',
        'activo',
        // Configuración de comprobantes (moneda, IGV, series, etc.)
        'moneda',
        'simbolo_moneda',
        'igv',
        'dia_emision',
        'dias_gracia',
        'dias_vencimiento',
        'generar_recibos_automaticos',
        'serie_boleta',
        'serie_factura',
        // Configuración de servicios
        'corte_automatico',
        'dias_antes_corte',
        'reactivacion_automatica',
        'notificar_vencimiento',
        'dias_notificacion_vencimiento',
        // Configuración de notificaciones
        'whatsapp_habilitado',
        'email_habilitado',
        'sms_habilitado',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'igv' => 'decimal:2',
        'generar_recibos_automaticos' => 'boolean',
        'corte_automatico' => 'boolean',
        'reactivacion_automatica' => 'boolean',
        'notificar_vencimiento' => 'boolean',
        'whatsapp_habilitado' => 'boolean',
        'email_habilitado' => 'boolean',
        'sms_habilitado' => 'boolean',
    ];

    /**
     * Relación con usuarios
     */
    public function users(): HasMany
    {
        return $this->hasMany(\App\Modules\ControlAcceso\Models\User::class);
    }

    /**
     * Relación con nodos
     */
    public function nodos(): HasMany
    {
        return $this->hasMany(\App\Modules\Red\Models\Nodo::class);
    }

    /**
     * Relación con clientes
     */
    public function clientes(): HasMany
    {
        return $this->hasMany(\App\Modules\Clientes\Models\Cliente::class);
    }
}
