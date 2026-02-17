<?php

namespace App\Modules\Infraestructura\Requests;

use App\Core\Services\TenantConnectionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreMufaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('infraestructura.create');
    }

    public function rules(): array
    {
        $tenantConn = TenantConnectionService::currentTenantConnectionName();

        return [
            'codigo' => ['nullable', 'string', 'max:100'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'poste_id' => [
                'nullable',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) use ($tenantConn): void {
                    if ($value === null || $value === '') {
                        return;
                    }
                    if (! $tenantConn) {
                        $fail(__('validation.exists', ['attribute' => $attribute]));
                        return;
                    }
                    $exists = DB::connection($tenantConn)->table('postes')->where('id', (int) $value)->exists();
                    if (! $exists) {
                        $fail(__('validation.exists', ['attribute' => $attribute]));
                    }
                },
            ],
            'notas' => ['nullable', 'string', 'max:1000'],
            'estado' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('isp_id') && auth()->check() && auth()->user()->isp_id) {
            $this->merge(['isp_id' => auth()->user()->isp_id]);
        }
    }
}
