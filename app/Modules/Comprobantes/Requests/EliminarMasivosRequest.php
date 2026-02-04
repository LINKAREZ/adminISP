<?php

namespace App\Modules\Comprobantes\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EliminarMasivosRequest extends FormRequest
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
            'confirmar' => ['required', 'accepted'], // Asegurar que el checkbox esté marcado
        ];
    }

    public function messages(): array
    {
        return [
            'mes.required' => 'El mes es obligatorio.',
            'mes.in' => 'El mes seleccionado no es válido.',
            'ano.required' => 'El año es obligatorio.',
            'ano.min' => 'El año debe ser mayor o igual a 2020.',
            'ano.max' => 'El año debe ser menor o igual a 2099.',
            'confirmar.required' => 'Debe confirmar la eliminación.',
            'confirmar.accepted' => 'Debe confirmar la eliminación marcando la casilla.',
        ];
    }
}
