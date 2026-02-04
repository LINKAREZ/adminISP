<?php

namespace App\Modules\ControlAcceso\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionResourceRequest extends FormRequest
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
            'new_resource' => 'required|string|regex:/^[a-z0-9_-]+$/',
        ];
    }
}
