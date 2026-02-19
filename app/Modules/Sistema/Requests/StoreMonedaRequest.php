<?php

namespace App\Modules\Sistema\Requests;

use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMonedaRequest extends FormRequest
{
    use AuthorizesWithPermission;

    public function authorize(): bool
    {
        return $this->authorizePermission('sistema.create');
    }

    protected function prepareForValidation(): void
    {
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
            'codigo' => ['required', 'string', 'size:3', Rule::unique('monedas', 'codigo')->connection('mysql')],
            'nombre' => 'required|string|max:64',
            'simbolo' => 'required|string|max:10',
            'activo' => 'nullable|boolean',
            'orden' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El código de moneda es requerido.',
            'codigo.unique' => 'Ya existe una moneda con ese código.',
            'nombre.required' => 'El nombre es requerido.',
            'simbolo.required' => 'El símbolo es requerido.',
        ];
    }
}
