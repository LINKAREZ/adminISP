<?php

namespace App\Modules\Comprobantes\Requests;

use App\Core\Traits\AuthorizesWithPermission;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreReciboRequest extends FormRequest
{
    use AuthorizesWithPermission;
    public function authorize(): bool
    {
        return $this->authorizePermission('comprobantes.create');
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('fecha_emision') && $this->fecha_emision) {
            $fechaEmision = Carbon::parse($this->fecha_emision)->startOfDay();
            $this->merge(['fecha_emision' => $fechaEmision->format('Y-m-d')]);
        }

        if ($this->has('fecha_vencimiento') && $this->fecha_vencimiento) {
            $fechaVencimiento = Carbon::parse($this->fecha_vencimiento)->startOfDay();
            $this->merge(['fecha_vencimiento' => $fechaVencimiento->format('Y-m-d')]);
        }
    }

    public function rules(): array
    {
        return [
            'servicio_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value && ! \App\Modules\Servicios\Models\Servicio::where('id', $value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => 'servicio']));
                    }
                },
            ],
            'periodo' => ['required', 'date_format:Y-m'],
            'fecha_emision' => ['required', 'date'],
            'fecha_vencimiento' => ['required', 'date', 'after_or_equal:fecha_emision'],
            'monto' => ['required', 'numeric', 'min:0'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'periodo.required' => 'El período es obligatorio.',
            'periodo.date_format' => 'El período debe tener el formato YYYY-MM (ej: 2025-12).',
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric' => 'El monto debe ser un número válido.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a la fecha de emisión.',
        ];
    }
}
