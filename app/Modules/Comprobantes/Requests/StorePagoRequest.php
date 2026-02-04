<?php

namespace App\Modules\Comprobantes\Requests;

use App\Core\Rules\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StorePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Modules\ControlAcceso\Models\User|null $user */
        $user = Auth::user();

        return $user !== null && $user->hasPermission('comprobantes.create');
    }

    protected function prepareForValidation(): void
    {
        // Auto-asignar isp_id si no viene en el request
        /** @var \App\Modules\ControlAcceso\Models\User|null $user */
        $user = Auth::user();
        if (!$this->has('isp_id') && $user && $user->isp_id) {
            $this->merge(['isp_id' => $user->isp_id]);
        }

        if ($this->has('numero_operacion')) {
            $numeroOperacion = trim($this->input('numero_operacion'));
            $numeroOperacion = preg_replace('/[^0-9]/', '', $numeroOperacion);
            $this->merge([
                'numero_operacion' => $numeroOperacion ?: null,
            ]);
        }

        if ($this->has('codigo_seguridad')) {
            $codigoSeguridad = trim($this->input('codigo_seguridad'));
            $codigoSeguridad = preg_replace('/[^0-9]/', '', $codigoSeguridad);
            $this->merge([
                'codigo_seguridad' => $codigoSeguridad ?: null,
            ]);
        }

        // Construir fecha_pago a partir de dia, mes, ano si están presentes
        if ($this->has(['dia', 'mes', 'ano']) && !$this->has('fecha_pago')) {
            $dia = str_pad($this->input('dia'), 2, '0', STR_PAD_LEFT);
            $mes = str_pad($this->input('mes'), 2, '0', STR_PAD_LEFT);
            $ano = $this->input('ano');

            // Validar que la fecha sea válida
            if (checkdate((int)$mes, (int)$dia, (int)$ano)) {
                $this->merge([
                    'fecha_pago' => sprintf('%s-%s-%s', $ano, $mes, $dia),
                ]);
            }
        }

        // Procesar hora, minuto, periodo y convertir a fecha_hora
        if ($this->has(['hora', 'minuto', 'periodo']) && $this->has('fecha_pago')) {
            $fechaPago = $this->input('fecha_pago');
            $hora = (int)$this->input('hora');
            $minuto = (int)$this->input('minuto');
            $periodo = $this->input('periodo');
            $timeZone = config('app.timezone', 'America/Lima');

            if ($fechaPago && $hora && $periodo) {
                try {
                    // Convertir hora AM/PM a formato 24 horas
                    $hora24 = $hora;
                    if ($periodo === 'PM' && $hora !== 12) {
                        $hora24 = $hora + 12;
                    } elseif ($periodo === 'AM' && $hora === 12) {
                        $hora24 = 0;
                    }

                    $fechaHora = \Carbon\Carbon::createFromFormat('Y-m-d H:i', sprintf('%s %02d:%02d', $fechaPago, $hora24, $minuto), $timeZone);
                    $this->merge([
                        'fecha_hora' => $fechaHora->format('Y-m-d H:i:s'),
                    ]);
                } catch (\Exception $e) {
                    // Si hay error, usar la fecha_hora_hidden si existe
                    if ($this->has('fecha_hora_hidden')) {
                        $this->merge([
                            'fecha_hora' => $this->input('fecha_hora_hidden'),
                        ]);
                    }
                }
            }
        } elseif ($this->has('fecha_hora_hidden')) {
            // Fallback: usar fecha_hora_hidden si existe
            $this->merge([
                'fecha_hora' => $this->input('fecha_hora_hidden'),
            ]);
        }

        $medioPagoId = $this->input('medio_pago_id');
        $tipoMedioPago = null;

        if ($medioPagoId) {
            $medioPago = \App\Modules\Sistema\Models\MedioPago::find($medioPagoId);
            $tipoMedioPago = $medioPago ? $medioPago->tipo : null;
        } else {
            $tipoMedioPago = $this->input('medio_pago');
        }

        if ($tipoMedioPago) {
            $this->merge(['_tipo_medio_pago' => $tipoMedioPago]);
        }
    }

    public function rules(): array
    {
        $tipoMedioPago = $this->input('_tipo_medio_pago');
        /** @var \App\Modules\ControlAcceso\Models\User|null $user */
        $user = Auth::user();

        $rules = [
            'isp_id' => [
                'nullable',
                'integer',
                'exists:isps,id',
                function ($attribute, $value, $fail) use ($user) {
                    if ($user && $user->isp_id && (int)$value !== (int)$user->isp_id) {
                        $fail('El ISP asignado no es válido para el usuario actual.');
                    }
                },
            ],
            'servicio_id' => [
                'nullable',
                'exists:servicios,id',
                function ($attribute, $value, $fail) {
                    // Validar que el recibo no esté pagado si se proporciona
                    if ($this->has('recibo_id') && $this->recibo_id) {
                        $recibo = \App\Modules\Comprobantes\Models\Recibo::find($this->recibo_id);
                        if ($recibo && $recibo->estaPagada()) {
                            $fail('No se puede registrar un pago para un recibo que ya está pagado.');
                        }
                    }
                },
            ],
            'recibo_id' => ['nullable', 'exists:recibos,id'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'fecha_pago' => ['required', 'date'],
            'fecha_hora' => ['nullable', 'date'],
            'medio_pago' => ['nullable', 'in:efectivo,yape,plin,transferencia,otro'],
            'medio_pago_id' => ['required', 'exists:medios_pago,id'],
            'codigo_seguridad' => ['nullable', 'string', 'max:10'],
            'numero_operacion' => [
                'nullable',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $existe = \App\Modules\Comprobantes\Models\Pago::where('numero_operacion', $value)
                            ->where('id', '!=', $this->route('pago')) // Excluir el pago actual si es edición
                            ->exists();

                        if ($existe) {
                            $fail('Este número de operación ya ha sido registrado anteriormente.');
                        }
                    }
                },
            ],
            'referencia' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string', 'max:1000'],
            'captura' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];

        if ($tipoMedioPago === 'yape') {
            $rules['codigo_seguridad'] = ValidationRules::codigoSeguridadYape();
            $rules['numero_operacion'] = ValidationRules::numeroOperacionYape();
        } elseif ($tipoMedioPago === 'plin') {
            $rules['numero_operacion'] = ValidationRules::numeroOperacionPlin();
        } elseif ($tipoMedioPago === 'transferencia') {
            $rules['numero_operacion'] = ValidationRules::numeroOperacionTransferencia();
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric' => 'El monto debe ser un número válido.',
            'monto.min' => 'El monto debe ser mayor a 0.',
            'fecha_pago.required' => 'La fecha de pago es obligatoria.',
            'servicio_id.exists' => 'El servicio seleccionado no es válido.',
            'medio_pago_id.required' => 'El medio de pago es obligatorio.',
            'medio_pago_id.exists' => 'El medio de pago seleccionado no es válido.',
            'medio_pago.required' => 'El medio de pago es obligatorio.',
            'codigo_seguridad.required' => 'El código de seguridad es obligatorio para pagos con Yape.',
            'numero_operacion.required' => 'El número de operación es obligatorio.',
        ];
    }
}
