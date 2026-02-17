<?php

namespace App\Modules\Clientes\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    const ESTADO_ABIERTO = 'abierto';
    const ESTADO_EN_PROGRESO = 'en_progreso';
    const ESTADO_CERRADO = 'cerrado';

    protected $table = 'tickets';

    protected $fillable = ['cliente_id', 'asunto', 'estado', 'asignado_a', 'isp_id'];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function asignadoA(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\ControlAcceso\Models\User::class, 'asignado_a');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(TicketMensaje::class)->orderBy('created_at');
    }
}
