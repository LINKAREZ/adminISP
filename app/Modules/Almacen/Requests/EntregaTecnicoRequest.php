<?php

namespace App\Modules\Almacen\Requests;

use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class EntregaTecnicoRequest extends FormRequest
{
    use AuthorizesWithPermission;

    public function authorize(): bool
    {
        return $this->authorizeAnyPermission(['almacen.create', 'almacen.update']);
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
