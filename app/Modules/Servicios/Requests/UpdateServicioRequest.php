<?php

namespace App\Modules\Servicios\Requests;

use App\Modules\Servicios\Models\Servicio;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('servicios.update');
    }

    public function rules(): array
    {
        $data = $this->all();
        $servicio = $this->route('servicio');
        $data['id'] = $servicio ? $servicio->id : null;

        return Servicio::rules($data);
    }

    public function messages(): array
    {
        return Servicio::messages();
    }

    /**
     * Validar que la ubicación pertenezca al cliente del servicio
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $servicio = $this->route('servicio');

            if ($servicio) {
                $servicio->load('ubicacion.cliente');
                $cliente = $servicio->ubicacion->cliente;

                // Si se proporciona ubicacion_id, validar que pertenezca al cliente del servicio
                if ($this->filled('ubicacion_id') && $this->ubicacion_id !== $servicio->ubicacion_id) {
                    $ubicacion = \App\Modules\Clientes\Models\Ubicacion::find($this->ubicacion_id);

                    if (!$ubicacion) {
                        $validator->errors()->add('ubicacion_id', 'La ubicación seleccionada no existe.');
                    } elseif ($ubicacion->cliente_id !== $cliente->id) {
                        $validator->errors()->add(
                            'ubicacion_id',
                            'La ubicación seleccionada no pertenece al cliente del servicio.'
                        );
                    }
                }
            }
        });
    }
}
