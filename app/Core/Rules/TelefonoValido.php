<?php

namespace App\Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class TelefonoValido implements Rule
{
    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value): bool
    {
        if (empty($value)) {
            return true; // Es nullable
        }

        // Separar teléfonos por comas
        $telefonos = array_map('trim', explode(',', $value));
        $telefonos = array_filter($telefonos);

        foreach ($telefonos as $telefono) {
            $telefono = trim($telefono);

            // Si no tiene +51, agregarlo
            if (!str_starts_with($telefono, '+51')) {
                // Si solo tiene números, asumir que son los 9 dígitos
                if (preg_match('/^[0-9]{9}$/', $telefono)) {
                    // Verificar que inicie con 9
                    if (!str_starts_with($telefono, '9')) {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                // Si ya tiene +51, verificar que el número después de +51 inicie con 9
                $numero = substr($telefono, 3);
                if (!preg_match('/^9[0-9]{8}$/', $numero)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'El teléfono debe tener 9 dígitos e iniciar con 9. Formato esperado: +519XXXXXXXX o 9XXXXXXXX';
    }
}

