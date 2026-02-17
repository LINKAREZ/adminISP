<?php

namespace App\Modules\Clientes\Models;

use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMensaje extends Model
{
    use UsesTenantConnection;

    protected $table = 'ticket_mensajes';

    protected $fillable = ['ticket_id', 'user_id', 'mensaje', 'adjunto'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\ControlAcceso\Models\User::class);
    }

    public function esDelCliente(): bool
    {
        return $this->user_id === null;
    }
}
