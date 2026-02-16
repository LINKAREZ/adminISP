<?php

namespace App\Modules\Installer\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveDatabaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'APP_URL' => ['required', 'string', 'max:255', 'regex:#^https?://.+#'],
            'DB_HOST' => ['required', 'string', 'max:255'],
            'DB_PORT' => ['required', 'string', 'max:10'],
            'DB_DATABASE' => ['required', 'string', 'max:255'],
            'DB_USERNAME' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (str_contains($value, '@')) {
                        $fail('El usuario de MySQL no puede ser un correo. Use el usuario de la base de datos (ej: root o adminisp), no el email del administrador del panel.');
                    }
                },
            ],
            'DB_PASSWORD' => ['nullable', 'string'],
        ];
    }
}
