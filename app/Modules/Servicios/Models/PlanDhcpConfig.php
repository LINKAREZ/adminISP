<?php

namespace App\Modules\Servicios\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanDhcpConfig extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'plan_dhcp_config';

    protected $fillable = [
        'plan_id',
        'isp_id',
        'nombre_servidor_routeros',
        'interfaz',
        'pool_nombre',
        'red_cidr',
        'rango_ip',
        'gateway',
        'dns',
        'domain',
        'lease_time',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
