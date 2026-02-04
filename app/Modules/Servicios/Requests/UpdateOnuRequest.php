<?php

namespace App\Modules\Servicios\Requests;

use App\Core\Rules\ValidationRules;
use App\Modules\Sistema\Models\OnuMarca;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOnuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('servicios.update');
    }

    public function rules(): array
    {
        $onu = $this->route('onu');
        $onuId = $onu ? $onu->id : null;

        return [
            'serial_number_completo' => ValidationRules::serialNumberOnu(),
            'serial_number_olt' => ValidationRules::nombre(false),
            'serial_number' => ['nullable', 'string', 'max:255', 'unique:onus,serial_number,' . $onuId],
            'mac_address' => ValidationRules::macAddress(),
            'usuario' => ValidationRules::nombre(false),
            'password' => ValidationRules::nombre(false),
            'marca_id' => ValidationRules::foreignId('onu_marcas', false),
            'modelo_id' => ValidationRules::foreignId('onu_modelos', false),
            'marca' => ValidationRules::nombre(false),
            'modelo' => ValidationRules::nombre(false),
            'notas' => ValidationRules::descripcion(false, 1000),
        ];
    }

    public function messages(): array
    {
        return [
            'serial_number_completo.required' => 'El número de serie completo es obligatorio.',
            'serial_number_completo.size' => 'El número de serie completo debe tener exactamente 16 caracteres hexadecimales.',
            'serial_number_completo.regex' => 'El número de serie completo debe contener solo caracteres hexadecimales (0-9, A-F).',
            'serial_number.unique' => 'Este número de serie ya está registrado.',
            'mac_address.required' => 'La dirección MAC es obligatoria.',
            'mac_address.regex' => 'La dirección MAC debe tener el formato válido (ej: 00:11:22:33:44:55).',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $marcaNombre = null;
            if ($this->has('marca_id') && $this->marca_id) {
                $marca = OnuMarca::find($this->marca_id);
                if ($marca) {
                    $marcaNombre = $marca->nombre;
                }
            } else {
                $marcaNombre = $this->marca;
            }

            if (strtoupper(trim($marcaNombre ?? '')) === 'ATW') {
                if (empty($this->usuario) || empty($this->password)) {
                    $validator->errors()->add('usuario', 'El campo usuario es obligatorio para equipos ATW');
                    $validator->errors()->add('password', 'El campo password es obligatorio para equipos ATW');
                }
            }
        });
    }
}
