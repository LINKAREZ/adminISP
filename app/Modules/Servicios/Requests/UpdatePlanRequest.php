<?php

namespace App\Modules\Servicios\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('servicios.update');
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('estado')) {
            $estado = $this->estado;
            if ($estado === '1' || $estado === 1 || $estado === true || $estado === 'true') {
                $this->merge(['estado' => true]);
            } elseif ($estado === '0' || $estado === 0 || $estado === false || $estado === 'false') {
                $this->merge(['estado' => false]);
            }
        }

        if ($this->has('precio_mensual')) {
            $precio = $this->precio_mensual;
            if (is_string($precio)) {
                $precio = str_replace(',', '.', $precio);
            }
            $this->merge(['precio_mensual' => (float)$precio]);
        }
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'router_id' => ['required', 'exists:routers,id'],
            'estado' => ['nullable', 'boolean'],
            'velocidad_bajada_mbps' => ['required', 'integer', 'min:1'],
            'velocidad_subida_mbps' => ['required', 'integer', 'min:1'],
            'precio_mensual' => ['required', 'numeric', 'min:0'],
            'tipo_conexion' => ['required', 'in:pppoe,dhcp,estatica'],
            'perfil_mikrotik' => ['nullable', 'string', 'max:255'],
            'ip_fija' => ['nullable', 'ip'],
        ];
    }
}
