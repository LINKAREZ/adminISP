<?php

namespace App\Modules\Sistema\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOnuMarcaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $marcaId = $this->route('marca')?->id ?? $this->route('marca');

        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('onu_marcas', 'nombre')->ignore($marcaId),
            ],
            'estado' => 'boolean',
            'orden' => 'nullable|integer|min:0',
        ];
    }
}
