<?php

namespace App\Modules\Infraestructura\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Cable extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'cables';

    protected $fillable = [
        'tipo_origen',
        'id_origen',
        'tipo_destino',
        'id_destino',
        'nombre',
        'metros',
        'isp_id',
    ];

    public const TIPO_POSTE = 'poste';
    public const TIPO_CAJA_NAP = 'caja_nap';
    public const TIPO_MUFA = 'mufa';

    public static function tiposValidos(): array
    {
        return [self::TIPO_POSTE, self::TIPO_CAJA_NAP, self::TIPO_MUFA];
    }
}
