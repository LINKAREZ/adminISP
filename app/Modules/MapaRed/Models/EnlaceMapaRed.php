<?php

namespace App\Modules\MapaRed\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnlaceMapaRed extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'mapa_red_enlaces';

    public const TIPO_TRONCAL = 'troncal';
    public const TIPO_DISTRIBUCION = 'distribucion';
    public const TIPO_ACOMETIDA = 'acometida';
    public const TIPO_RESERVA = 'reserva';

    public const TIPOS = [
        self::TIPO_TRONCAL,
        self::TIPO_DISTRIBUCION,
        self::TIPO_ACOMETIDA,
        self::TIPO_RESERVA,
    ];

    protected $fillable = [
        'proyecto_id',
        'origen_id',
        'destino_id',
        'capa_id',
        'tipo',
        'atributos',
        'isp_id',
    ];

    protected $casts = [
        'atributos' => 'array',
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(ProyectoMapaRed::class, 'proyecto_id');
    }

    public function origen(): BelongsTo
    {
        return $this->belongsTo(NodoMapaRed::class, 'origen_id');
    }

    public function destino(): BelongsTo
    {
        return $this->belongsTo(NodoMapaRed::class, 'destino_id');
    }

    public function capa(): BelongsTo
    {
        return $this->belongsTo(CapaMapaRed::class, 'capa_id');
    }
}
