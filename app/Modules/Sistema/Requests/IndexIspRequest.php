<?php

namespace App\Modules\Sistema\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexIspRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas para filtros y orden del listado de ISPs (superadmin).
     */
    public function rules(): array
    {
        return [
            'buscar' => ['sometimes', 'string', 'max:100'],
            'estado' => ['sometimes', 'in:activo,inactivo'],
            'orden' => ['sometimes', 'in:nombre_asc,nombre_desc,recientes,antiguos'],
        ];
    }
}
