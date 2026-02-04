<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicioResource extends JsonResource
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
                'documento' => $this->cliente->documento,
            ]),
            'ubicacion_id' => $this->ubicacion_id,
            'router_id' => $this->router_id,
            'router' => $this->whenLoaded('router', fn() => [
                'id' => $this->router->id,
                'nombre' => $this->router->nombre,
            ]),
            'plan_id' => $this->plan_id,
            'plan' => $this->whenLoaded('plan', fn() => [
                'id' => $this->plan->id,
                'nombre' => $this->plan->nombre,
                'precio_mensual' => $this->plan->precio_mensual,
            ]),
            'tipo_pppoe' => $this->tipo_pppoe,
            'tipo_pppoe_nombre' => $this->tipo_pppoe_nombre,
            'usuario_pppoe' => $this->usuario_pppoe,
            'mac_address' => $this->mac_address,
            'estado' => $this->estado,
            'fecha_instalacion' => $this->fecha_instalacion?->format('Y-m-d'),
            'es_provisional' => $this->es_provisional,
            'notas' => $this->notas,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
