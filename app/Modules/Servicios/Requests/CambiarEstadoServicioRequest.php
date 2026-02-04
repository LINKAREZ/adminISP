<?php

namespace App\Modules\Servicios\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CambiarEstadoServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => 'required|in:activo,cortado',
        ];
    }
}
