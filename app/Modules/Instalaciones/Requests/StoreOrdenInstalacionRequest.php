<?php

namespace App\Modules\Instalaciones\Requests;

use App\Modules\Clientes\Models\Cliente;
use App\Modules\Red\Models\Router;
use App\Modules\Servicios\Models\Plan;
use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrdenInstalacionRequest extends FormRequest
{
    use AuthorizesWithPermission;
    public function authorize(): bool
    {
        return $this->authorizePermission('instalaciones.create');
    }

    public function rules(): array
    {
        return [
            'cliente_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if ($value && ! Cliente::where('id', (int) $value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => 'cliente']));
                    }
                },
            ],
            'plan_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if ($value && ! Plan::where('id', (int) $value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => 'plan']));
                    }
                },
            ],
            'router_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    if ($value && ! Router::where('id', (int) $value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => 'router']));
                    }
                },
            ],
            'direccion' => 'required|string|max:255',
            'referencia' => 'nullable|string|max:255',
            'distrito' => 'nullable|string|max:100',
            'provincia' => 'nullable|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'fecha_programada' => 'nullable|date',
            'estado' => 'nullable|in:pendiente,programada,en_curso',
            'tecnico_id' => 'nullable|integer',
            'vendedor_id' => 'nullable|integer',
            'notas' => 'nullable|string|max:2000',
        ];
    }
}
