<?php

namespace App\Modules\Sistema\Requests;

use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOnuModeloRequest extends FormRequest
{
    use AuthorizesWithPermission;
    public function authorize(): bool
    {
        return $this->authorizePermission('sistema.update');
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
