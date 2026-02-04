<?php

namespace App\Modules\ControlAcceso\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resource' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_-]+$/'],
            'module' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_hidden' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'resource.required' => 'El recurso es obligatorio.',
            'resource.regex' => 'El recurso solo puede contener letras minúsculas, números, guiones y guiones bajos.',
            'module.required' => 'El módulo es obligatorio.',
        ];
    }
}
