<?php

namespace App\Modules\Instalaciones\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComisionVendedor extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'comisiones_vendedor';

    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_PAGADO = 'pagado';

    protected $fillable = [
        'vendedor_id',
        'orden_instalacion_id',
        'monto',
        'fecha_cumplimiento_3mes',
        'estado',
        'fecha_pago',
        'comprobante_id',
        'isp_id',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_cumplimiento_3mes' => 'date',
        'fecha_pago' => 'date',
    ];

    public function ordenInstalacion(): BelongsTo
    {
        return $this->belongsTo(OrdenInstalacion::class);
    }

    public function getVendedorAttribute(): ?\App\Modules\ControlAcceso\Models\User
    {
        if (!$this->vendedor_id) {
            return null;
        }
        return \App\Modules\ControlAcceso\Models\User::on(\App\Core\Services\TenantConnectionService::CENTRAL_CONNECTION)->find($this->vendedor_id);
    }
}
