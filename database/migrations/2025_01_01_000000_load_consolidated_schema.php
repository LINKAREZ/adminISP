<?php

use Illuminate\Database\Migrations\Migration;

/**
 * @deprecated Schema consolidado ya no se usa. La BD central solo tiene isps, users, roles, permissions.
 * Las tablas tenant se crean por ISP con migrate --path=database/migrations/tenant --database=isp_{id}
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op: esquema central se crea con migraciones 2025_01_01_000001_* a 000005_*
    }

    public function down(): void
    {
        // No-op
    }
};
