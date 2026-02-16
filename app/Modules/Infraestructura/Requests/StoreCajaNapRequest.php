<?php

namespace App\Modules\Infraestructura\Requests;

use App\Core\Rules\ExistsInTenant;
use App\Core\Traits\MergesIspId;
use Illuminate\Foundation\Http\FormRequest;

class StoreCajaNapRequest extends FormRequest
{
    use MergesIspId;
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('infraestructura.create');
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

    protected function prepareForValidation(): void
    {
        $this->mergeIspId();
    }
}
