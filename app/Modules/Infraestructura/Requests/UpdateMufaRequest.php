<?php

namespace App\Modules\Infraestructura\Requests;

use App\Core\Rules\ExistsInTenant;
use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMufaRequest extends FormRequest
{
    use AuthorizesWithPermission;
    public function authorize(): bool
    {
        return $this->authorizePermission('infraestructura.update');
    }

    public function rules(): array
    {
        return [
            'codigo' => ['nullable', 'string', 'max:100'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'poste_id' => ['nullable', 'integer', new ExistsInTenant('postes')],
            'notas' => ['nullable', 'string', 'max:1000'],
            'estado' => ['nullable', 'boolean'],
        ];
    }
}
