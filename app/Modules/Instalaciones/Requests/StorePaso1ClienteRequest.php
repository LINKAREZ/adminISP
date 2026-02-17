<?php

namespace App\Modules\Instalaciones\Requests;

use App\Core\Rules\DocumentoUnico;
use App\Core\Rules\DocumentoValido;
use App\Core\Rules\TelefonoValido;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación para crear un cliente en el Paso 1 del wizard de instalación.
 * Mismas reglas que StoreClienteRequest, autorizado por permiso instalaciones.create.
 */
class StorePaso1ClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('instalaciones.create');
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'tipo_documento' => ['required', 'in:dni,ce,ruc'],
            'documento' => [
                'required',
                'string',
                'regex:/^[0-9]+$/',
                new DocumentoUnico(),
                new DocumentoValido($this->input('tipo_documento', 'dni')),
            ],
            'telefonos' => [
                'required',
                'string',
                'max:500',
                new TelefonoValido(),
            ],
            'dni_nombres' => ['nullable', 'string', 'max:255'],
            'dni_apellido_paterno' => ['nullable', 'string', 'max:255'],
            'dni_apellido_materno' => ['nullable', 'string', 'max:255'],
            'ruc_nombre_comercial' => ['nullable', 'string', 'max:255'],
            'ruc_estado' => ['nullable', 'string', 'max:50'],
            'ruc_condicion' => ['nullable', 'string', 'max:50'],
            'ruc_ubigeo' => ['nullable', 'string', 'max:10'],
            'ruc_capital' => ['nullable', 'numeric', 'min:0'],
            'direccion_api' => ['nullable', 'string', 'max:500'],
            'departamento_api' => ['nullable', 'string', 'max:100'],
            'provincia_api' => ['nullable', 'string', 'max:100'],
            'distrito_api' => ['nullable', 'string', 'max:100'],
            'fuente_info' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('isp_id') && auth()->check() && auth()->user()->isp_id) {
            $this->merge(['isp_id' => auth()->user()->isp_id]);
        }
        if ($this->has('telefonos') && !empty($this->telefonos)) {
            $telefonos = array_map('trim', explode(',', $this->telefonos));
            $telefonos = array_filter($telefonos);
            $telefonosNormalizados = [];
            foreach ($telefonos as $telefono) {
                if (!str_starts_with($telefono, '+51')) {
                    if (preg_match('/^[0-9]{9}$/', $telefono) && str_starts_with($telefono, '9')) {
                        $telefono = '+51' . $telefono;
                        $telefonosNormalizados[] = $telefono;
                    }
                } else {
                    $telefonosNormalizados[] = $telefono;
                }
            }
            $this->merge(['telefonos' => !empty($telefonosNormalizados) ? implode(', ', $telefonosNormalizados) : null]);
        } else {
            $this->merge(['telefonos' => null]);
        }
    }
}
