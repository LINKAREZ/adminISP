<?php

namespace App\Modules\Clientes\Requests;

use App\Core\Rules\DocumentoUnico;
use App\Core\Rules\DocumentoValido;
use App\Core\Rules\TelefonoValido;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClienteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('clientes.update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $clienteId = $this->route('cliente')->id;

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'tipo_documento' => ['required', 'in:dni,ce,ruc'],
            'documento' => [
                'required',
                'string',
                'regex:/^[0-9]+$/',
                new DocumentoUnico($clienteId),
                new DocumentoValido($this->input('tipo_documento', 'dni')),
            ],
            'telefonos' => [
                'nullable',
                'string',
                'max:500',
                new TelefonoValido(),
            ],
            'notas' => ['nullable', 'string', 'max:1000'],
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
        // Normalizar teléfonos antes de validar
        // Asegurar que siempre se procese el campo telefonos, incluso si está vacío
        $telefonosInput = $this->input('telefonos');

        // Si el campo existe (incluso si está vacío), procesarlo
        if ($this->has('telefonos')) {
            if (!empty($telefonosInput) && trim($telefonosInput) !== '') {
                $telefonos = array_map('trim', explode(',', $telefonosInput));
                $telefonos = array_filter($telefonos, fn($t) => !empty($t));
                $telefonosNormalizados = [];

                foreach ($telefonos as $telefono) {
                    // Limpiar el teléfono de caracteres no numéricos excepto +
                    $telefonoLimpio = preg_replace('/[^0-9+]/', '', $telefono);

                    // Si no tiene +51, agregarlo
                    if (!str_starts_with($telefonoLimpio, '+51')) {
                        // Si solo tiene números, asumir que son los 9 dígitos
                        if (preg_match('/^[0-9]{9}$/', $telefonoLimpio)) {
                            $telefonoLimpio = '+51' . $telefonoLimpio;
                        }
                    }
                    $telefonosNormalizados[] = $telefonoLimpio;
                }

                $telefonosFinal = implode(', ', $telefonosNormalizados);
                $this->merge(['telefonos' => $telefonosFinal]);
            } else {
                // Si está vacío, asegurar que se envíe como string vacío
                $this->merge(['telefonos' => '']);
            }
        } else {
            // Si el campo no existe en el request, no hacer nada (mantener el valor actual)
        }
    }
}
