<?php

namespace App\Modules\Red\Requests;

use App\Core\Rules\ExistsInTenant;
use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class StoreRouterRequest extends FormRequest
{
    use AuthorizesWithPermission;
    public function authorize(): bool
    {
        return $this->authorizePermission('red.create');
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'ip_url' => ['required', 'string', 'max:255'],
            'puerto_api' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'puerto_snmp' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'comunidad' => ['nullable', 'string', 'max:255'],
            'usuario' => ['required', 'string', 'max:255'],
            'contraseña' => ['required', 'string', 'max:255'],
            'nodo_id' => ['nullable', 'integer', new ExistsInTenant('nodos')],
            'notas' => ['nullable', 'string', 'max:1000'],
            'estado' => ['nullable', 'boolean'],
        ];
    }
}
