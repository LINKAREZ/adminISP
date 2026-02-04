<?php

namespace App\Modules\ControlAcceso\Events;

use App\Modules\ControlAcceso\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserActualizado
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user
    ) {}
}
