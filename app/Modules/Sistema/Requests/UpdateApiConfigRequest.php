<?php

namespace App\Modules\Sistema\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApiConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['nullable', 'string', 'max:500'],
            'activo' => ['boolean'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
