<?php

namespace App\Modules\Servicios\Events;

use App\Modules\Servicios\Models\Servicio;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServicioActualizado
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Servicio $servicio
    ) {}
}
