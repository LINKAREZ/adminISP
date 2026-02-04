<?php

namespace App\Core\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Clase base para eventos
 */
abstract class BaseEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Usuario que disparó el evento
     */
    public ?int $userId;

    /**
     * Timestamp del evento
     */
    public string $timestamp;

    /**
     * Metadata adicional
     */
    public array $metadata = [];

    public function __construct()
    {
        $this->userId = auth()->id();
        $this->timestamp = now()->toIso8601String();
    }

    /**
     * Agregar metadata al evento
     */
    public function withMetadata(array $metadata): self
    {
        $this->metadata = array_merge($this->metadata, $metadata);
        return $this;
    }

    /**
     * Obtener el nombre del evento para logs
     */
    public function getEventName(): string
    {
        return class_basename(static::class);
    }

    /**
     * Obtener datos para logging
     */
    public function toLogArray(): array
    {
        return [
            'event' => $this->getEventName(),
            'user_id' => $this->userId,
            'timestamp' => $this->timestamp,
            'metadata' => $this->metadata,
        ];
    }
}
