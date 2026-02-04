<?php

namespace App\Core\Rules;

use App\Modules\Servicios\Models\Servicio;
use Illuminate\Contracts\Validation\Rule;

class MacAddressUnica implements Rule
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
        if (empty($value)) {
            return true; // Es nullable
        }

        // Normalizar MAC address
        $mac = strtoupper(trim($value));
        $mac = preg_replace('/[:\-\s]+/', '', $mac);

        if (strlen($mac) !== 12 || !ctype_xdigit($mac)) {
            return false;
        }

        $mac = implode(':', str_split($mac, 2));

        $query = \App\Modules\Servicios\Models\Servicio::where('mac_address', $mac);

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
        return 'Esta dirección MAC ya está registrada en otro servicio.';
    }
}

