<?php

namespace App\Modules\Almacen\Requests;

use App\Core\Rules\ExistsInTenant;
use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class StoreArticuloRequest extends FormRequest
{
    use AuthorizesWithPermission;

    public function authorize(): bool
    {
        return $this->authorizePermission('almacen.create');
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:50',
            'tipo' => 'required|in:equipo,material,herramienta,consumible',
            'unidad' => 'required|string|max:20',
            'costo_referencia' => 'nullable|numeric|min:0',
            'onu_modelo_id' => ['nullable', 'integer', new ExistsInTenant('onu_modelos')],
        ];
    }
}
