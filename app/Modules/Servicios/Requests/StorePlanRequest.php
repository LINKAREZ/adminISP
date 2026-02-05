<?php

namespace App\Modules\Servicios\Requests;

use App\Core\Services\TenantConnectionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('servicios.create');
    }

    public function rules(): array
    {
        $tenantConn = TenantConnectionService::currentTenantConnectionName();

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'router_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) use ($tenantConn): void {
                    if (! $tenantConn) {
                        $fail(__('validation.exists', ['attribute' => $attribute]));
                        return;
                    }
                    $exists = DB::connection($tenantConn)->table('routers')->where('id', (int) $value)->exists();
                    if (! $exists) {
                        $fail(__('validation.exists', ['attribute' => $attribute]));
                    }
                },
            ],
            'estado' => ['nullable', 'boolean'],
            'velocidad_bajada_mbps' => ['required', 'integer', 'min:1'],
            'velocidad_subida_mbps' => ['required', 'integer', 'min:1'],
            'precio_mensual' => ['required', 'numeric', 'min:0'],
            'tipo_conexion' => ['required', 'in:pppoe,dhcp,estatica'],
            'perfil_mikrotik' => ['nullable', 'string', 'max:255'],
            'ip_fija' => ['nullable', 'ip'],
        ];
    }
}
