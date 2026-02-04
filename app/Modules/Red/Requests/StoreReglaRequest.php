<?php

namespace App\Modules\Red\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReglaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|in:firewall,address-list,queue,ip,etc',
            'configuracion' => 'required|array',
            'activo' => 'nullable|boolean',
            'notas' => 'nullable|string',
        ];
    }
}
