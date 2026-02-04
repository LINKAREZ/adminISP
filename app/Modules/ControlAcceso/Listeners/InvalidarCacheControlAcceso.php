<?php

namespace App\Modules\ControlAcceso\Listeners;

use App\Modules\ControlAcceso\Events\PermissionActualizado;
use App\Modules\ControlAcceso\Events\RoleActualizado;
use App\Modules\ControlAcceso\Events\UserActualizado;
use App\Core\Services\CacheService;

class InvalidarCacheControlAcceso
{
    /**
     * Handle the event.
     */
    public function handle(UserActualizado|RoleActualizado|PermissionActualizado $event): void
    {
        if ($event instanceof UserActualizado) {
            CacheService::invalidateUsersCache();
        } elseif ($event instanceof RoleActualizado) {
            CacheService::invalidateRolesCache();
        } elseif ($event instanceof PermissionActualizado) {
            CacheService::invalidatePermissionsCache();
        }
    }
}
