<?php

namespace App\Core\Listeners;

use App\Core\Events\BaseEvent;
use App\Core\Events\ModelCreated;
use App\Core\Events\ModelDeleted;
use App\Core\Events\ModelUpdated;

/**
 * Listener para registrar actividad de modelos
 */
class LogModelActivity extends BaseListener
{
    public string $queue = 'logs';

    /**
     * Manejar el evento
     */
    public function handle(BaseEvent $event): void
    {
        $logData = $event->toLogArray();

        $action = match (true) {
            $event instanceof ModelCreated => 'CREATED',
            $event instanceof ModelUpdated => 'UPDATED',
            $event instanceof ModelDeleted => 'DELETED',
            default => 'ACTION',
        };

        $this->logInfo("{$action}: " . ($logData['model_type'] ?? 'Unknown'), $logData);
    }
}
