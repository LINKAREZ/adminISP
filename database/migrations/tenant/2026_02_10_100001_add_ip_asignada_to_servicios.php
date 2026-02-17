<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * IP asignada al cliente (DHCP: guardada al obtener lease o make static).
     */
    public function up(): void
    {
        if (!Schema::hasTable('servicios')) {
            return;
        }
        Schema::table('servicios', function (Blueprint $table) {
            if (!Schema::hasColumn('servicios', 'ip_asignada')) {
                $table->string('ip_asignada', 45)->nullable()->after('mac_address');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('servicios')) {
            return;
        }
        Schema::table('servicios', function (Blueprint $table) {
            if (Schema::hasColumn('servicios', 'ip_asignada')) {
                $table->dropColumn('ip_asignada');
            }
        });
    }
};
