<?php

namespace App\Modules\ControlAcceso\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroyPermissionResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resource' => 'required|string',
            'module' => 'required|string',
        ];
    }
}
