<?php

namespace App\Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class DocumentoValido implements Rule
{
    protected string $tipoDocumento;

    /**
     * Create a new rule instance.
     */
    public function __construct(string $tipoDocumento)
    {
        $this->tipoDocumento = $tipoDocumento;
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value): bool
    {
        $longitud = strlen($value);

        return match($this->tipoDocumento) {
            'dni' => $longitud === 8,
            'ce' => $longitud === 9,
            'ruc' => $longitud === 11,
            default => false,
        };
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        $longitudEsperada = match($this->tipoDocumento) {
            'dni' => '8',
            'ce' => '9',
            'ruc' => '11',
            default => '?',
        };

        return "El {$this->tipoDocumento} debe tener exactamente {$longitudEsperada} dígitos.";
    }
}

