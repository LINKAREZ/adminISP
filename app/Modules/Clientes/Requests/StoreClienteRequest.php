<?php

namespace App\Modules\Clientes\Requests;

use App\Core\Rules\DocumentoUnico;
use App\Core\Rules\DocumentoValido;
use App\Core\Rules\TelefonoValido;
use App\Core\Traits\AuthorizesWithPermission;
use App\Core\Traits\MergesIspId;
use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
{
    use AuthorizesWithPermission;
    use MergesIspId;

    public function authorize(): bool
    {
        return $this->authorizePermission('clientes.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
                'nullable',
                'string',
                'max:500',
                new TelefonoValido(),
            ],
            // Campos de información de DNI (se guardan en BD)
            'dni_nombres' => ['nullable', 'string', 'max:255'],
            'dni_apellido_paterno' => ['nullable', 'string', 'max:255'],
            'dni_apellido_materno' => ['nullable', 'string', 'max:255'],
            // Campos de información de RUC
            'ruc_nombre_comercial' => ['nullable', 'string', 'max:255'],
            'ruc_estado' => ['nullable', 'string', 'max:50'],
            'ruc_condicion' => ['nullable', 'string', 'max:50'],
            'ruc_ubigeo' => ['nullable', 'string', 'max:10'],
            'ruc_capital' => ['nullable', 'numeric', 'min:0'],
            // Campos de dirección de la API
            'direccion_api' => ['nullable', 'string', 'max:500'],
            'departamento_api' => ['nullable', 'string', 'max:100'],
            'provincia_api' => ['nullable', 'string', 'max:100'],
            'distrito_api' => ['nullable', 'string', 'max:100'],
            'fuente_info' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'tipo_documento.required' => 'El tipo de documento es obligatorio.',
            'tipo_documento.in' => 'El tipo de documento debe ser DNI, CE o RUC.',
            'documento.required' => 'El número de documento es obligatorio.',
            'documento.regex' => 'El documento solo debe contener números.',
            'documento.unique' => 'Este documento ya está registrado.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeIspId();

        // Normalizar teléfonos antes de validar
        if ($this->has('telefonos') && !empty($this->telefonos)) {
            $telefonos = array_map('trim', explode(',', $this->telefonos));
            $telefonos = array_filter($telefonos);
            $telefonosNormalizados = [];

            foreach ($telefonos as $telefono) {
                // Si no tiene +51, agregarlo
                if (!str_starts_with($telefono, '+51')) {
                    // Si solo tiene números, asumir que son los 9 dígitos
                    if (preg_match('/^[0-9]{9}$/', $telefono)) {
                        // Verificar que inicie con 9
                        if (str_starts_with($telefono, '9')) {
                            $telefono = '+51' . $telefono;
                            $telefonosNormalizados[] = $telefono;
                        }
                    }
                } else {
                    // Si ya tiene +51, agregarlo directamente
                    $telefonosNormalizados[] = $telefono;
                }
            }

            // Solo actualizar si hay teléfonos normalizados
            if (!empty($telefonosNormalizados)) {
                $this->merge(['telefonos' => implode(', ', $telefonosNormalizados)]);
            } else {
                // Si no hay teléfonos válidos, establecer como null
                $this->merge(['telefonos' => null]);
            }
        } else {
            // Si no viene el campo telefonos, establecer como null explícitamente
            $this->merge(['telefonos' => null]);
        }
    }
}
