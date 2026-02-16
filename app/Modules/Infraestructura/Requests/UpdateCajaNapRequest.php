<?php

namespace App\Modules\Infraestructura\Requests;

use App\Core\Rules\ExistsInTenant;
use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCajaNapRequest extends FormRequest
{
    use AuthorizesWithPermission;
    public function authorize(): bool
    {
        return $this->authorizePermission('infraestructura.update');
    }

    public function rules(): array
    {
        return [
            'poste_id' => ['required', 'integer', new ExistsInTenant('postes')],
            'codigo' => ['nullable', 'string', 'max:100'],
            'capacidad_puertos' => ['required', 'integer', 'min:1', 'max:128'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'notas' => ['nullable', 'string', 'max:1000'],
            'estado' => ['nullable', 'boolean'],
        ];
    }
}
