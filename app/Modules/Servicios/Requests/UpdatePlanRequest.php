<?php

namespace App\Modules\Servicios\Requests;

use App\Core\Services\TenantConnectionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('servicios.update');
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('estado')) {
            $estado = $this->estado;
            if ($estado === '1' || $estado === 1 || $estado === true || $estado === 'true') {
                $this->merge(['estado' => true]);
            } elseif ($estado === '0' || $estado === 0 || $estado === false || $estado === 'false') {
                $this->merge(['estado' => false]);
            }
        }

        if ($this->has('precio_mensual')) {
            $precio = $this->precio_mensual;
            if (is_string($precio)) {
                $precio = str_replace(',', '.', $precio);
            }
            $this->merge(['precio_mensual' => (float)$precio]);
        }

        // ip_fija en BD puede ser boolean/tinyint; vacío se guarda como 0
        if ($this->has('ip_fija') && ($this->ip_fija === null || $this->ip_fija === '')) {
            $this->merge(['ip_fija' => 0]);
        }
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
            'ip_fija' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '' || $value === 0) {
                        return;
                    }
                    if (! filter_var($value, FILTER_VALIDATE_IP)) {
                        $fail(__('validation.ip', ['attribute' => $attribute]));
                    }
                },
            ],
        ];
    }
}
