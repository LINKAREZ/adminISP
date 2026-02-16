<?php

namespace App\Modules\Infraestructura\Requests;

use App\Core\Rules\ExistsInTenant;
use App\Core\Traits\AuthorizesWithPermission;
use App\Core\Traits\MergesIspId;
use Illuminate\Foundation\Http\FormRequest;

class StoreMufaRequest extends FormRequest
{
    use AuthorizesWithPermission;
    use MergesIspId;

    public function authorize(): bool
    {
        return $this->authorizePermission('infraestructura.create');
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

    protected function prepareForValidation(): void
    {
        $this->mergeIspId();
    }
}
