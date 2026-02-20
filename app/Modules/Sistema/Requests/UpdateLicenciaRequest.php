<?php

namespace App\Modules\Sistema\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLicenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $licencia = $this->route('licencia');
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:50', Rule::unique('mysql.licencias', 'slug')->ignore($licencia->id ?? null)],
            'max_routers' => ['nullable', 'integer', 'min:0'],
            'max_clientes' => ['nullable', 'integer', 'min:0'],
            'max_usuarios' => ['nullable', 'integer', 'min:0'],
            'max_storage_mb' => ['nullable', 'integer', 'min:0'],
            'price_monthly' => ['nullable', 'numeric', 'min:0'],
            'price_yearly' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'interval' => ['nullable', 'string', Rule::in(['month', 'year'])],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
