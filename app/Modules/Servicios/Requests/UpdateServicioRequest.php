<?php

namespace App\Modules\Servicios\Requests;

use App\Modules\Clientes\Models\Ubicacion;
use App\Modules\Red\Models\Router;
use App\Modules\Servicios\Models\Plan;
use App\Modules\Servicios\Models\Servicio;
use App\Modules\Servicios\Models\Onu;
use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServicioRequest extends FormRequest
{
    use AuthorizesWithPermission;
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
        return $this->authorizePermission('servicios.update');
    }

    protected function prepareForValidation(): void
    {
        foreach (['dia_facturacion', 'dia_corte', 'dias_gracia'] as $key) {
            if ($this->has($key) && $this->input($key) === '') {
                $this->merge([$key => null]);
            }
        }
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
        $rules['ubicacion_latitud'] = ['nullable', 'numeric', 'between:-90,90'];
        $rules['ubicacion_longitud'] = ['nullable', 'numeric', 'between:-180,180'];
        $rules['ubicacion_foto_1'] = ['nullable', 'image', 'max:2048'];
        $rules['ubicacion_foto_2'] = ['nullable', 'image', 'max:2048'];
        $rules['ubicacion_foto_3'] = ['nullable', 'image', 'max:2048'];
        $rules['ubicacion_foto_1_titulo'] = ['nullable', 'string', 'max:80'];
        $rules['ubicacion_foto_2_titulo'] = ['nullable', 'string', 'max:80'];
        $rules['ubicacion_foto_3_titulo'] = ['nullable', 'string', 'max:80'];

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

                // La ubicación debe tener al menos una foto (existente o nueva)
                $ubicacion = $servicio->ubicacion;
                if ($ubicacion) {
                    $yaTieneFoto = $ubicacion->foto_1 || $ubicacion->foto_2 || $ubicacion->foto_3;
                    $subeNueva = $this->hasFile('ubicacion_foto_1') || $this->hasFile('ubicacion_foto_2') || $this->hasFile('ubicacion_foto_3');
                    if (!$yaTieneFoto && !$subeNueva) {
                        $validator->errors()->add('ubicacion_foto_1', 'Debe subir al menos una foto de la ubicación.');
                    }
                }
            }
        });
    }
}
