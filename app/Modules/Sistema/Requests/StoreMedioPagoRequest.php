<?php

namespace App\Modules\Sistema\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedioPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('sistema.create');
    }

    protected function prepareForValidation(): void
    {
        // Convertir activo de string a booleano
        if ($this->has('activo')) {
            $this->merge([
                'activo' => filter_var($this->activo, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
            ]);
        } else {
            $this->merge(['activo' => true]);
        }
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:yape,transferencia,efectivo,plin,otro',
            'numero_cuenta' => 'nullable|string|max:50',
            'banco' => 'nullable|string|max:100',
            'activo' => 'nullable|boolean',
            'notas' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es requerido.',
            'tipo.required' => 'El tipo de medio de pago es requerido.',
            'tipo.in' => 'El tipo de medio de pago no es válido.',
        ];
    }
}
