<?php

namespace App\Modules\Red\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNodoRequest extends FormRequest
{
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

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-asignar isp_id si no viene en el request
        if (!$this->has('isp_id') && auth()->check() && auth()->user()->isp_id) {
            $this->merge(['isp_id' => auth()->user()->isp_id]);
        }
    }
}
