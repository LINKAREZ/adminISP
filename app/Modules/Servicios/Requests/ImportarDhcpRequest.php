<?php

namespace App\Modules\Servicios\Requests;

use App\Core\Rules\ExistsInTenant;
use Illuminate\Foundation\Http\FormRequest;

class ImportarDhcpRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Misma lógica que ImportarPerfilesRequest: cualquier usuario autenticado puede importar
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'router_id' => ['required', 'integer', new ExistsInTenant('routers')],
            'servidores' => 'required|array',
            'servidores.*.nombre_servidor' => 'required|string|max:255',
            'servidores.*.nombre_plan' => 'nullable|string|max:255',
            'servidores.*.precio_mensual' => 'nullable|numeric|min:0',
        ];
    }
}
