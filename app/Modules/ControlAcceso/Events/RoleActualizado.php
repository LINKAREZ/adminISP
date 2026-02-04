<?php

namespace App\Modules\ControlAcceso\Events;

use App\Modules\ControlAcceso\Models\Role;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleActualizado
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Role $role
    ) {}
}
