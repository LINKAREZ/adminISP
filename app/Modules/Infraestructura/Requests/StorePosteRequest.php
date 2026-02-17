<?php

namespace App\Modules\Infraestructura\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePosteRequest extends FormRequest
{
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
        if (!$this->has('isp_id') && auth()->check() && auth()->user()->isp_id) {
            $this->merge(['isp_id' => auth()->user()->isp_id]);
        }
    }
}
