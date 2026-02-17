<?php

namespace App\Modules\Almacen\Requests;

use App\Core\Services\TenantConnectionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UpdateArticuloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('almacen.update');
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:50',
            'tipo' => 'required|in:equipo,material,herramienta,consumible',
            'unidad' => 'required|string|max:20',
            'costo_referencia' => 'nullable|numeric|min:0',
            'onu_modelo_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        return;
                    }
                    $conn = TenantConnectionService::currentTenantConnectionName();
                    if (!$conn) {
                        $fail('No hay contexto de ISP. Seleccione un ISP o inicie sesión con un usuario asignado a un ISP.');
                        return;
                    }
                    if (!DB::connection($conn)->table('onu_modelos')->where('id', $value)->exists()) {
                        $fail('El modelo ONU seleccionado no es válido.');
                    }
                },
            ],
        ];
    }
}
