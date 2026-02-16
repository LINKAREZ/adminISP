<?php

namespace App\Modules\Comprobantes\Requests;

use App\Core\Traits\AuthorizesWithPermission;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReciboRequest extends FormRequest
{
    use AuthorizesWithPermission;
    public function authorize(): bool
    {
        return $this->authorizePermission('comprobantes.update');
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
            'periodo' => ['required', 'date_format:Y-m'],
            'fecha_emision' => ['required', 'date'],
            'fecha_vencimiento' => ['required', 'date', 'after_or_equal:fecha_emision'],
            'monto' => ['required', 'numeric', 'min:0'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
