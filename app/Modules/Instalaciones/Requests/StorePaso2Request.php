<?php

namespace App\Modules\Instalaciones\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaso2Request extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('instalaciones.create');
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
