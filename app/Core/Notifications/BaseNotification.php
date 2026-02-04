<?php

namespace App\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Clase base para notificaciones
 */
abstract class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Cola a usar
     */
    public string $queue = 'notifications';

    /**
     * Obtener canales de entrega
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Obtener datos para base de datos
     */
    abstract public function toDatabase($notifiable): array;

    /**
     * Obtener el tipo de notificación
     */
    public function getType(): string
    {
        return class_basename(static::class);
    }

    /**
     * Obtener icono para la notificación
     */
    public function getIcon(): string
    {
        return 'fa-bell';
    }

    /**
     * Obtener color para la notificación
     */
    public function getColor(): string
    {
        return 'info';
    }

    /**
     * Datos base para todas las notificaciones
     */
    protected function baseData(): array
    {
        return [
            'type' => $this->getType(),
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
