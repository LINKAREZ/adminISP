<?php

namespace App\Modules\Servicios\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOnuWithoutServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'serial_number_completo' => 'nullable|string|size:16|regex:/^[0-9A-Fa-f]{16}$/',
            'mac_address' => 'required|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'marca_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value && ! \App\Modules\Sistema\Models\OnuMarca::where('id', $value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => 'marca']));
                    }
                },
            ],
            'modelo_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value && ! \App\Modules\Servicios\Models\OnuModelo::where('id', $value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => 'modelo']));
                    }
                },
            ],
            'usuario' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'notas' => 'nullable|string|max:1000',
        ];
    }
}
