<?php

namespace App\Modules\Servicios\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarPerfilesImportadosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'router_id' => 'required|exists:routers,id',
            'perfiles' => 'required|array',
            'perfiles.*.name' => 'required|string',
        ];
    }
}
