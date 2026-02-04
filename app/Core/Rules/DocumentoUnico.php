<?php

namespace App\Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class DocumentoUnico implements Rule
{
    protected ?int $exceptId = null;

    /**
     * Create a new rule instance.
     */
    public function __construct(?int $exceptId = null)
    {
        $this->exceptId = $exceptId;
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value): bool
    {
        $query = \App\Modules\Clientes\Models\Cliente::where('documento', $value);

        if ($this->exceptId) {
            $query->where('id', '!=', $this->exceptId);
        }

        return !$query->exists();
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'Este documento ya está registrado.';
    }
}
