<?php

namespace App\Modules\Instalaciones\Requests;

use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class StorePaso2Request extends FormRequest
{
    use AuthorizesWithPermission;
    public function authorize(): bool
    {
        return $this->authorizePermission('instalaciones.create');
    }

    public function rules(): array
    {
        return [
            'direccion' => 'required|string|max:255',
            'referencia' => 'nullable|string|max:255',
            'distrito' => 'nullable|string|max:100',
            'provincia' => 'nullable|string|max:100',
            'departamento' => 'nullable|string|max:100',
        ];
    }
}
