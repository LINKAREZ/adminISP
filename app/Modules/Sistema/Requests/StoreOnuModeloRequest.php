<?php

namespace App\Modules\Sistema\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOnuModeloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('sistema.create');
    }

    public function rules(): array
    {
        return [
            'marca_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value && ! \App\Modules\Sistema\Models\OnuMarca::where('id', $value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => 'marca']));
                    }
                },
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'estado' => ['boolean'],
            'orden' => ['nullable', 'integer', 'min:0'],
            'requiere_transformacion' => ['boolean'],
            'usuario_pppoe_default' => ['nullable', 'string', 'max:255'],
            'password_pppoe_default' => ['nullable', 'string', 'max:255'],
            'vlan_default' => ['nullable', 'string', 'max:50'],
            'tipo_conexion_default' => ['nullable', 'in:pppoe,dhcp,estatica'],
        ];
    }
}
