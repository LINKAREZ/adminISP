<?php

namespace App\Modules\Sistema\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIspRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'activo' => 'nullable|boolean',
            'moneda' => 'required|string|size:3',
            'simbolo_moneda' => 'required|string|max:10',
            'igv' => 'required|numeric|min:0|max:100',
            'licencia_id' => 'nullable|exists:licencias,id',
            'status' => 'nullable|in:active,pending,suspended,cancelled',
            'database_name' => 'prohibited', // No se puede modificar; todo ISP debe conservar su BD
        ];
    }
}
