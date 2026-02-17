<?php

namespace App\Modules\Infraestructura\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOdfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('infraestructura.create');
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string', 'max:2000'],
            'estado' => ['nullable', 'boolean'],
        ];
    }
}
