<?php

namespace App\Core\Rules;

use App\Core\Services\TenantConnectionService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

/**
 * Valida que el valor exista en la tabla del tenant actual.
 * Uso: ['poste_id' => ['nullable', 'integer', new ExistsInTenant('postes')]]
 */
class ExistsInTenant implements ValidationRule
{
    public function __construct(
        protected string $table,
        protected string $column = 'id'
    ) {}

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $conn = TenantConnectionService::currentTenantConnectionName();
        if (!$conn) {
            $fail(__('validation.exists', ['attribute' => $attribute]));
            return;
        }
        if (!DB::connection($conn)->table($this->table)->where($this->column, (int) $value)->exists()) {
            $fail(__('validation.exists', ['attribute' => $attribute]));
        }
    }
}
