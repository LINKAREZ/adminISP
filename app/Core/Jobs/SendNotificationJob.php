<?php

namespace App\Core\Jobs;

use Illuminate\Support\Facades\Notification;

/**
 * Job base para envío de notificaciones
 */
class SendNotificationJob extends BaseJob
{
    public string $queue = 'notifications';

    protected $notifiable;
    protected $notification;

    public function __construct($notifiable, $notification)
    {
        $this->notifiable = $notifiable;
        $this->notification = $notification;
    }

    public function handle(): void
    {
        $this->logInfo('Enviando notificación', [
            'notifiable_type' => get_class($this->notifiable),
            'notifiable_id' => $this->notifiable->id ?? null,
            'notification' => get_class($this->notification),
        ]);

        Notification::send($this->notifiable, $this->notification);

        $this->logInfo('Notificación enviada correctamente');
    }

    public function tags(): array
    {
        return array_merge(parent::tags(), [
            'notification:' . class_basename($this->notification),
        ]);
    }
}
