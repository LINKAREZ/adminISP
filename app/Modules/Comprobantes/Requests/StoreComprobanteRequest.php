<?php

namespace App\Modules\Comprobantes\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreComprobanteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo' => 'required|in:boleta,factura,recibo',
            'cliente_id' => 'required|exists:clientes,id',
            'fecha_emision' => 'required|date',
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'required|string|max:500',
            'exonerado_igv' => 'required|boolean',
            'moneda' => 'required|in:PEN,USD',
            'forma_pago' => 'required|in:contado,credito',
            'mes' => 'nullable|string|size:2',
            'ano' => 'nullable|digits:4',
        ];
    }
}
