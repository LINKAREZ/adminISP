<?php

namespace App\Modules\Instalaciones\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaso3Request extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('instalaciones.create');
    }

    public function rules(): array
    {
        return [
            'foto_1' => 'nullable|image|max:5120',
            'foto_1_titulo' => 'nullable|string|max:100',
            'foto_2' => 'nullable|image|max:5120',
            'foto_2_titulo' => 'nullable|string|max:100',
            'foto_3' => 'nullable|image|max:5120',
            'foto_3_titulo' => 'nullable|string|max:100',
        ];
    }
}
