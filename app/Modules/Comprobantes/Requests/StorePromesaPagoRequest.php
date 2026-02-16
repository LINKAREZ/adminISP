<?php

namespace App\Modules\Comprobantes\Requests;

use App\Modules\Comprobantes\Models\PromesaPago;
use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class StorePromesaPagoRequest extends FormRequest
{
    use AuthorizesWithPermission;
    public function authorize(): bool
    {
        return $this->authorizePermission('comprobantes.create');
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('recibo')) {
            $this->merge([
                'recibo_id' => $this->route('recibo')->id,
            ]);
        }

        if ($this->has('monto_comprometido')) {
            $monto = $this->input('monto_comprometido');
            if (is_string($monto)) {
                $monto = str_replace(',', '', $monto);
                $this->merge([
                    'monto_comprometido' => (float) $monto,
                ]);
            }
        }

        // Establecer hora por defecto si no se proporciona
        // Solo si la columna existe en la base de datos
        if (\Illuminate\Support\Facades\Schema::hasColumn('promesas_pago', 'hora_compromiso')) {
            if (!$this->has('hora_compromiso') || empty($this->input('hora_compromiso'))) {
                $this->merge(['hora_compromiso' => '13:00']);
            }
        } else {
            // Si la columna no existe, remover el campo del request
            $this->offsetUnset('hora_compromiso');
        }
    }

    public function rules(): array
    {
        return [
            'recibo_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value && ! \App\Modules\Comprobantes\Models\Recibo::where('id', $value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => 'recibo']));
                        return;
                    }
                },
                function ($attribute, $value, $fail) {
                    // Verificar que no exista una promesa de pago activa para este recibo
                    $promesaActiva = PromesaPago::where('recibo_id', $value)
                        ->whereIn('estado', [
                            PromesaPago::ESTADO_PENDIENTE,
                            PromesaPago::ESTADO_VENCIDA
                        ])
                        ->first();

                    if ($promesaActiva) {
                        $estadoTexto = $promesaActiva->estado === PromesaPago::ESTADO_PENDIENTE
                            ? 'pendiente'
                            : 'vencida';
                        $fechaCompromiso = $promesaActiva->fecha_compromiso->format('d/m/Y');
                        $fail("Ya existe una promesa de pago {$estadoTexto} para este recibo (fecha de compromiso: {$fechaCompromiso}). Solo se puede crear una promesa de pago por recibo.");
                    }
                },
            ],
            'fecha_compromiso' => 'required|date|after_or_equal:' . now()->format('Y-m-d'),
            'monto_comprometido' => 'required|numeric|min:0.01',
            'observacion' => 'nullable|string|max:1000',
            // Validar hora_compromiso solo si la columna existe
            ...(\Illuminate\Support\Facades\Schema::hasColumn('promesas_pago', 'hora_compromiso') 
                ? ['hora_compromiso' => 'nullable|date_format:H:i'] 
                : []),
        ];
    }

    public function messages(): array
    {
        return [
            'recibo_id.required' => 'El recibo es requerido.',
            'recibo_id.exists' => 'El recibo seleccionado no existe.',
            'fecha_compromiso.required' => 'La fecha de compromiso es requerida.',
            'fecha_compromiso.date' => 'La fecha de compromiso debe ser una fecha válida.',
            'fecha_compromiso.after_or_equal' => 'La fecha de compromiso debe ser hoy o una fecha futura.',
            'monto_comprometido.required' => 'El monto comprometido es requerido.',
            'monto_comprometido.numeric' => 'El monto comprometido debe ser un número.',
            'monto_comprometido.min' => 'El monto comprometido debe ser mayor a 0.',
        ];
    }
}
