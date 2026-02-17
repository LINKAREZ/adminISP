<?php

namespace App\Modules\Infraestructura\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePosteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('infraestructura.update');
    }

    public function rules(): array
    {
        return [
            'codigo' => ['nullable', 'string', 'max:100'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'zona' => ['nullable', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:50', 'in:minus,grip-lines-vertical,bolt,broadcast-tower,tower-cell,plug,satellite-dish,signal,circle-nodes'],
            'notas' => ['nullable', 'string', 'max:1000'],
            'estado' => ['nullable', 'boolean'],
        ];
    }
}
