<?php

namespace App\Modules\Servicios\Requests;

use App\Core\Rules\ExistsInTenant;
use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    use AuthorizesWithPermission;
    public function authorize(): bool
    {
        return $this->authorizePermission('servicios.update');
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('estado')) {
            $e = $this->estado;
            if (in_array($e, ['1', 1, true, 'true'], true)) {
                $this->merge(['estado' => true]);
            } elseif (in_array($e, ['0', 0, false, 'false'], true)) {
                $this->merge(['estado' => false]);
            }
        }
        if ($this->has('precio_mensual')) {
            $p = $this->precio_mensual;
            $this->merge(['precio_mensual' => (float) (is_string($p) ? str_replace(',', '.', $p) : $p)]);
        }
        if ($this->has('ip_fija') && ($this->ip_fija === null || $this->ip_fija === '')) {
            $this->merge(['ip_fija' => 0]);
        }
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'router_id' => ['required', 'integer', new ExistsInTenant('routers')],
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
