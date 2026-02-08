<?php

namespace App\Modules\Servicios\Requests;

use App\Modules\Clientes\Models\Ubicacion;
use App\Modules\Red\Models\Router;
use App\Modules\Servicios\Models\Onu;
use App\Modules\Servicios\Models\Plan;
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

        $rules = Servicio::rules($data);
        // Validar contra la BD del tenant (modelos con UsesTenantConnection)
        $rules['router_id'] = ['required', function ($attribute, $value, $fail) {
            if ($value && ! Router::where('id', $value)->exists()) {
                $fail(__('validation.exists', ['attribute' => 'router']));
            }
        }];
        $rules['plan_id'] = ['required', function ($attribute, $value, $fail) {
            if ($value && ! Plan::where('id', $value)->exists()) {
                $fail(__('validation.exists', ['attribute' => 'plan']));
            }
        }];
        $rules['ubicacion_id'] = [
            str_contains($rules['ubicacion_id'], 'required') ? 'required' : 'nullable',
            function ($attribute, $value, $fail) {
                if ($value && ! Ubicacion::where('id', $value)->exists()) {
                    $fail(__('validation.exists', ['attribute' => 'ubicación']));
                }
            },
        ];
        $rules['onu_id'] = ['nullable', function ($attribute, $value, $fail) {
            if ($value && ! Onu::where('id', $value)->exists()) {
                $fail(__('validation.exists', ['attribute' => 'onu']));
            }
        }];

        return $rules;
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
