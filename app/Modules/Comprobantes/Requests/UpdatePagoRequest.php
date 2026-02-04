<?php

namespace App\Modules\Comprobantes\Requests;

use App\Core\Rules\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('comprobantes.update');
    }

    protected function prepareForValidation(): void
    {
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

            if ($fechaPago && $hora && $periodo) {
                try {
                    // Convertir hora AM/PM a formato 24 horas
                    $hora24 = $hora;
                    if ($periodo === 'PM' && $hora !== 12) {
                        $hora24 = $hora + 12;
                    } elseif ($periodo === 'AM' && $hora === 12) {
                        $hora24 = 0;
                    }

                    $fechaHora = \Carbon\Carbon::createFromFormat('Y-m-d H:i', sprintf('%s %02d:%02d', $fechaPago, $hora24, $minuto), 'America/Lima');
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
    }

    public function rules(): array
    {
        $medioPagoId = $this->input('medio_pago_id');
        $tipoMedioPago = null;

        if ($medioPagoId) {
            $medioPago = \App\Modules\Sistema\Models\MedioPago::find($medioPagoId);
            $tipoMedioPago = $medioPago ? $medioPago->tipo : null;
        } else {
            $tipoMedioPago = $this->input('medio_pago');
        }

        $rules = [
            'servicio_id' => ['required', 'exists:servicios,id'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'fecha_pago' => ['required', 'date'],
            'fecha_hora' => ['nullable', 'date'],
            'hora' => ['nullable', 'integer', 'min:1', 'max:12'],
            'minuto' => ['nullable', 'integer', 'min:0', 'max:59'],
            'periodo' => ['nullable', 'in:AM,PM'],
            'medio_pago' => ['nullable', 'in:efectivo,yape,plin,transferencia,otro'],
            'medio_pago_id' => ['required', 'exists:medios_pago,id'],
            'codigo_seguridad' => ['nullable', 'string', 'max:10'],
            'numero_operacion' => [
                'nullable',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $pagoId = $this->route('pago'); // ID del pago que se está editando
                        $existe = \App\Modules\Comprobantes\Models\Pago::where('numero_operacion', $value)
                            ->where('id', '!=', $pagoId) // Excluir el pago actual
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
}
