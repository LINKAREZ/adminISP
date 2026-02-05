<?php

namespace App\Modules\Red\Requests;

use App\Core\Services\TenantConnectionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreRouterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('red.create');
    }

    public function rules(): array
    {
        $tenantConn = TenantConnectionService::currentTenantConnectionName();

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'ip_url' => ['required', 'string', 'max:255'],
            'puerto_api' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'puerto_snmp' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'comunidad' => ['nullable', 'string', 'max:255'],
            'usuario' => ['required', 'string', 'max:255'],
            'contraseña' => ['required', 'string', 'max:255'],
            'nodo_id' => [
                'nullable',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) use ($tenantConn): void {
                    if ($value === '' || $value === null || ! $tenantConn) {
                        return;
                    }
                    $exists = DB::connection($tenantConn)->table('nodos')->where('id', (int) $value)->exists();
                    if (! $exists) {
                        $fail(__('validation.exists', ['attribute' => $attribute]));
                    }
                },
            ],
            'notas' => ['nullable', 'string', 'max:1000'],
            'estado' => ['nullable', 'boolean'],
        ];
    }
}
