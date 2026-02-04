<?php

namespace App\Modules\Sistema\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOnuModeloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('sistema.update');
    }

    public function rules(): array
    {
        return [
            'usuario_pppoe_default' => ['nullable', 'string', 'max:255'],
            'password_pppoe_default' => ['nullable', 'string', 'max:255'],
            'vlan_default' => ['nullable', 'string', 'max:50'],
            'tipo_conexion_default' => ['nullable', 'in:pppoe,dhcp,estatica'],
        ];
    }
}
