<?php

namespace App\Modules\Infraestructura\Requests;

use App\Core\Services\TenantConnectionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UpdateCajaNapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('infraestructura.update');
    }

    public function rules(): array
    {
        $tenantConn = TenantConnectionService::currentTenantConnectionName();

        return [
            'poste_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) use ($tenantConn): void {
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
            'codigo' => ['nullable', 'string', 'max:100'],
            'capacidad_puertos' => ['required', 'integer', 'min:1', 'max:128'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'notas' => ['nullable', 'string', 'max:1000'],
            'estado' => ['nullable', 'boolean'],
        ];
    }
}
