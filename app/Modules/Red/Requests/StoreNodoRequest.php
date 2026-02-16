<?php

namespace App\Modules\Red\Requests;

use App\Core\Traits\MergesIspId;
use Illuminate\Foundation\Http\FormRequest;

class StoreNodoRequest extends FormRequest
{
    use MergesIspId;
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('red.create');
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'estado' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->mergeIspId();
    }
}
