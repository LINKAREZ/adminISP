<?php

namespace App\Modules\Comprobantes\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateComprobanteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notas' => 'nullable|string|max:1000',
            'condiciones_pago' => 'nullable|string|max:500',
            'orden_compra' => 'nullable|string|max:50',
            'guia_remision' => 'nullable|string|max:50',
        ];
    }
}
