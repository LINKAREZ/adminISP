<?php

namespace App\Modules\Clientes\Events;

use App\Modules\Clientes\Models\Cliente;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento que se dispara cuando un cliente es actualizado
 * Permite desacoplar acciones secundarias como invalidación de caché
 */
class ClienteActualizado
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Cliente $cliente
    ) {}
}
