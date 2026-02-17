<?php

namespace App\Modules\Almacen\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EntregaTecnicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('almacen.create') || $this->user()->hasPermission('almacen.update');
    }

    public function rules(): array
    {
        return [
            'tecnico_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.articulo_id' => 'required|exists:articulos,id',
            'items.*.cantidad' => 'required|numeric|min:0',
            'observacion' => 'nullable|string|max:500',
        ];
    }
}
