<?php

namespace App\Modules\Comprobantes\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerarMasivosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mes' => \App\Core\Rules\ValidationRules::mes(),
            'ano' => \App\Core\Rules\ValidationRules::ano(),
            'fecha_vencimiento' => ['required', 'date'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'mes.required' => 'El mes es obligatorio.',
            'mes.size' => 'El mes debe tener 2 dígitos.',
            'mes.in' => 'El mes seleccionado no es válido.',
            'ano.required' => 'El año es obligatorio.',
            'ano.integer' => 'El año debe ser un número entero.',
            'ano.min' => 'El año debe ser mayor o igual a 2020.',
            'ano.max' => 'El año debe ser menor o igual a 2099.',
            'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'fecha_vencimiento.date' => 'La fecha de vencimiento debe ser una fecha válida.',
        ];
    }
}
