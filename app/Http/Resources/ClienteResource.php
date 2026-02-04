<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
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
            'nombre' => $this->nombre,
            'nombres' => $this->nombres,
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno,
            'tipo_documento' => $this->tipo_documento,
            'documento' => $this->documento,
            'documento_completo' => $this->documento_completo,
            'telefonos' => $this->telefonos,
            'notas' => $this->notas,
            'nombre_comercial' => $this->nombre_comercial,
            'estado_ruc' => $this->estado_ruc,
            'condicion_ruc' => $this->condicion_ruc,
            'saldo_total' => $this->saldo_total,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
