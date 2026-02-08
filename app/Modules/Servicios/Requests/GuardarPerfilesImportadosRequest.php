<?php

namespace App\Modules\Servicios\Requests;

use App\Modules\Red\Models\Router;
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
            'router_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value && ! Router::where('id', $value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => 'router']));
                    }
                },
            ],
            'perfiles' => 'required|array',
            'perfiles.*.name' => 'required|string',
        ];
    }
}
