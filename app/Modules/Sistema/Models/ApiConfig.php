<?php

namespace App\Modules\Sistema\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class ApiConfig extends Model
{
    use Auditable;
    protected $table = 'api_configs';

    protected $fillable = [
        'nombre',
        'descripcion',
        'token',
        'activo',
        'configuracion_extra',
        'url_base',
        'timeout',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'configuracion_extra' => 'array',
        'timeout' => 'integer',
    ];

    public static function getToken(string $nombre): ?string
    {
        $api = self::where('nombre', $nombre)
            ->where('activo', true)
            ->first();

        return $api?->token;
    }
}
