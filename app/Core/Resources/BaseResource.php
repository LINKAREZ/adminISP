<?php

namespace App\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Clase base para API Resources
 */
abstract class BaseResource extends JsonResource
{
    /**
     * Campos a ocultar
     */
    protected array $hidden = [];

    /**
     * Campos adicionales a incluir
     */
    protected array $additional = [];

    /**
     * Transformar el recurso en un array
     */
    public function toArray(Request $request): array
    {
        $data = $this->transformData($request);

        // Remover campos ocultos
        foreach ($this->hidden as $field) {
            unset($data[$field]);
        }

        // Agregar campos adicionales
        return array_merge($data, $this->additional);
    }

    /**
     * Transformar datos (a implementar en clases hijas)
     */
    abstract protected function transformData(Request $request): array;

    /**
     * Agregar timestamps formateados
     */
    protected function timestamps(): array
    {
        return [
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i'),
        ];
    }

    /**
     * Agregar timestamps ISO
     */
    protected function timestampsIso(): array
    {
        return [
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Formatear monto
     */
    protected function formatMoney(?float $amount, string $symbol = 'S/.'): ?string
    {
        if ($amount === null) {
            return null;
        }

        return $symbol . ' ' . number_format($amount, 2, '.', ',');
    }

    /**
     * Formatear fecha
     */
    protected function formatDate($date, string $format = 'd/m/Y'): ?string
    {
        if (!$date) {
            return null;
        }

        return \Carbon\Carbon::parse($date)->format($format);
    }

    /**
     * Incluir relación si está cargada
     */
    protected function whenLoadedResource(string $relationship, string $resourceClass)
    {
        return $this->whenLoaded($relationship, function () use ($relationship, $resourceClass) {
            return new $resourceClass($this->$relationship);
        });
    }

    /**
     * Incluir colección si está cargada
     */
    protected function whenLoadedCollection(string $relationship, string $resourceClass)
    {
        return $this->whenLoaded($relationship, function () use ($relationship, $resourceClass) {
            return $resourceClass::collection($this->$relationship);
        });
    }

    /**
     * Ocultar campos
     */
    public function hide(array $fields): self
    {
        $this->hidden = array_merge($this->hidden, $fields);
        return $this;
    }

    /**
     * Agregar campos adicionales
     */
    public function append(array $data): self
    {
        $this->additional = array_merge($this->additional, $data);
        return $this;
    }
}
