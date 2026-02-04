<?php

namespace App\Modules\Servicios\Requests;

use App\Modules\Servicios\Models\Servicio;
use Illuminate\Foundation\Http\FormRequest;

class StoreServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('servicios.create');
    }

    public function rules(): array
    {
        $data = $this->all();
        $data['id'] = null;

        return Servicio::rules($data);
    }

    public function messages(): array
    {
        return Servicio::messages();
    }

    /**
     * Validar que la ubicación pertenezca al cliente
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $cliente = $this->route('cliente');

            // Si se proporciona ubicacion_id, validar que pertenezca al cliente
            if ($cliente && $this->filled('ubicacion_id')) {
                $ubicacion = \App\Modules\Clientes\Models\Ubicacion::find($this->ubicacion_id);

                if (!$ubicacion) {
                    $validator->errors()->add('ubicacion_id', 'La ubicación seleccionada no existe.');
                } elseif ($ubicacion->cliente_id !== $cliente->id) {
                    $validator->errors()->add(
                        'ubicacion_id',
                        'La ubicación seleccionada no pertenece al cliente.'
                    );
                }
            }
        });
    }
}
