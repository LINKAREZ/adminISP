<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PagoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cliente_id' => $this->cliente_id,
            'cliente' => $this->whenLoaded('cliente', fn() => [
                'id' => $this->cliente->id,
                'nombre' => $this->cliente->nombre,
            ]),
            'servicio_id' => $this->servicio_id,
            'recibo_id' => $this->recibo_id,
            'monto' => (float) $this->monto,
            'fecha_pago' => $this->fecha_pago?->format('Y-m-d H:i:s'),
            'medio_pago' => $this->medio_pago,
            'medio_pago_id' => $this->medio_pago_id,
            'medio_pago_nombre' => $this->medio_pago_nombre,
            'codigo_seguridad' => $this->codigo_seguridad,
            'numero_operacion' => $this->numero_operacion,
            'referencia' => $this->referencia,
            'notas' => $this->notas,
            'registrado_por' => $this->registrado_por,
            'registrado_por_usuario' => $this->whenLoaded('registradoPor', fn() => [
                'id' => $this->registradoPor->id,
                'name' => $this->registradoPor->name,
            ]),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
