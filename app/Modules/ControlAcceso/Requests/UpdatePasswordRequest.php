<?php

namespace App\Modules\ControlAcceso\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', 'string', 'size:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'La contraseña actual es incorrecta.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.size' => 'La nueva contraseña debe tener exactamente 8 caracteres.',
        ];
    }
}
