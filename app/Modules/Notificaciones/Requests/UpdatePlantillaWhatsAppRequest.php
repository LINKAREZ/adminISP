<?php

namespace App\Modules\Notificaciones\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlantillaWhatsAppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'mensaje' => 'required|string',
            'activo' => 'boolean',
        ];
    }
}
