<?php

namespace App\Modules\Servicios\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOnuApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'serial_number_completo' => 'sometimes|required|string|size:16|regex:/^[0-9A-Fa-f]{16}$/',
            'mac_address' => 'sometimes|required|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'usuario' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'notas' => 'nullable|string|max:1000',
        ];
    }
}
