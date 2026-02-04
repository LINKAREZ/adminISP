<?php

namespace App\Modules\Sistema\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOnuMarcaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255|unique:onu_marcas,nombre',
            'estado' => 'boolean',
            'orden' => 'nullable|integer|min:0',
        ];
    }
}
