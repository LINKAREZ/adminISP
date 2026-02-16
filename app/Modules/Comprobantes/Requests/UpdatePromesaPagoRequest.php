<?php

namespace App\Modules\Comprobantes\Requests;

use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePromesaPagoRequest extends FormRequest
{
    use AuthorizesWithPermission;
    public function authorize(): bool
    {
        return $this->authorizePermission('comprobantes.update');
    }

    protected function prepareForValidation(): void
    {
        // Establecer hora por defecto si no se proporciona
        // Solo si la columna existe en la base de datos
        if (\Illuminate\Support\Facades\Schema::hasColumn('promesas_pago', 'hora_compromiso')) {
            if (!$this->has('hora_compromiso') || empty($this->input('hora_compromiso'))) {
                $this->merge(['hora_compromiso' => '13:00']);
            }
        } else {
            // Si la columna no existe, remover el campo del request
            $this->offsetUnset('hora_compromiso');
        }
    }

    public function rules(): array
    {
        return [
            'fecha_compromiso' => 'required|date',
            'monto_comprometido' => 'required|numeric|min:0.01',
            'observacion' => 'nullable|string|max:1000',
            'estado' => 'sometimes|in:pendiente,cumplida,vencida,cancelada',
            // Validar hora_compromiso solo si la columna existe
            ...(\Illuminate\Support\Facades\Schema::hasColumn('promesas_pago', 'hora_compromiso') 
                ? ['hora_compromiso' => 'nullable|date_format:H:i'] 
                : []),
        ];
    }
}
