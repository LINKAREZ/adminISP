<?php

namespace App\Modules\Comprobantes\Events;

use App\Modules\Comprobantes\Models\Pago;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PagoRegistrado
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Pago $pago
    ) {}
}
