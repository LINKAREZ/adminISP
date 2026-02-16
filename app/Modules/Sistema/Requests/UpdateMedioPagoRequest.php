<?php

namespace App\Modules\Sistema\Requests;

use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMedioPagoRequest extends FormRequest
{
    use AuthorizesWithPermission;
    public function authorize(): bool
    {
        return $this->authorizePermission('sistema.update');
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:yape,transferencia,efectivo,plin,otro',
            'numero_cuenta' => 'nullable|string|max:50',
            'banco' => 'nullable|string|max:100',
            'activo' => 'nullable|boolean',
            'notas' => 'nullable|string|max:1000',
        ];
    }
}
