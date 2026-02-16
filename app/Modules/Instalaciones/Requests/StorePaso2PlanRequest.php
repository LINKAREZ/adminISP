<?php

namespace App\Modules\Instalaciones\Requests;

use App\Modules\Red\Models\Nodo;
use App\Modules\Red\Models\Router;
use App\Core\Traits\AuthorizesWithPermission;
use App\Modules\Servicios\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;

class StorePaso2PlanRequest extends FormRequest
{
    use AuthorizesWithPermission;
    public function authorize(): bool
    {
        return $this->authorizePermission('instalaciones.update');
    }

    public function rules(): array
    {
        return [
            'nodo_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    if ($value && ! Nodo::where('id', (int) $value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => 'nodo']));
                    }
                },
            ],
            'router_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if ($value && ! Router::where('id', (int) $value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => 'router']));
                    }
                },
            ],
            'tipo_conexion' => 'required|in:pppoe,dhcp,estatica',
            'plan_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if ($value && ! Plan::where('id', (int) $value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => 'plan']));
                    }
                },
            ],
        ];
    }
}
