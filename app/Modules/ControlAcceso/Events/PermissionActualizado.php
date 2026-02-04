<?php

namespace App\Modules\ControlAcceso\Events;

use App\Modules\ControlAcceso\Models\Permission;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PermissionActualizado
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Permission $permission
    ) {}
}
