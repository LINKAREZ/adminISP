<?php

namespace App\Modules\Infraestructura\Requests;

use App\Core\Traits\MergesIspId;
use Illuminate\Foundation\Http\FormRequest;

class StorePosteRequest extends FormRequest
{
    use MergesIspId;
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('infraestructura.create');
    }

    public function rules(): array
    {
        return [
            'codigo' => ['nullable', 'string', 'max:100'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'zona' => ['nullable', 'string', 'max:100'],
            'notas' => ['nullable', 'string', 'max:1000'],
            'estado' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->mergeIspId();
    }
}
