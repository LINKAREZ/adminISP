<?php

namespace App\Modules\Servicios\Requests;

use App\Modules\Clientes\Models\Ubicacion;
use App\Modules\Red\Models\Router;
use App\Modules\Servicios\Models\Plan;
use App\Modules\Servicios\Models\Servicio;
use App\Modules\Servicios\Models\Onu;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServicioRequest extends FormRequest
{
    /**
     * Obtiene la instancia del servicio desde la ruta (puede ser modelo o ID en rutas anidadas).
     */
    protected function getServicioFromRoute(): ?Servicio
    {
        $param = $this->route('servicio');
        if ($param instanceof Servicio) {
            return $param;
        }
        if (is_numeric($param)) {
            return Servicio::find((int) $param);
        }
        return null;
    }

    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('servicios.update');
    }

    public function rules(): array
    {
        $data = $this->all();
        $servicio = $this->getServicioFromRoute();
        $data['id'] = $servicio?->id;

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
        $rules['ubicacion_notas'] = ['nullable', 'string', 'max:1000'];
        $rules['ubicacion_foto_1'] = ['nullable', 'image', 'max:2048'];
        $rules['ubicacion_foto_2'] = ['nullable', 'image', 'max:2048'];
        $rules['ubicacion_foto_3'] = ['nullable', 'image', 'max:2048'];

        return $rules;
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
            $servicio = $this->getServicioFromRoute();

            if ($servicio) {
                $servicio->load('ubicacion.cliente');
                $cliente = $servicio->ubicacion->cliente ?? null;

                // Si se proporciona ubicacion_id, validar que pertenezca al cliente del servicio
                if ($this->filled('ubicacion_id') && $this->ubicacion_id !== $servicio->ubicacion_id) {
                    $ubicacion = \App\Modules\Clientes\Models\Ubicacion::find($this->ubicacion_id);

                    if (!$ubicacion) {
                        $validator->errors()->add('ubicacion_id', 'La ubicación seleccionada no existe.');
                    } elseif ($cliente && $ubicacion->cliente_id !== $cliente->id) {
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
