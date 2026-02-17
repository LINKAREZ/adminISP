<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tipo de servicio (PPPoE, DHCP, IP estática) elegido antes del plan en el wizard.
     */
    public function up(): void
    {
        Schema::table('ordenes_instalacion', function (Blueprint $table) {
            $table->string('tipo_conexion', 30)->nullable()->after('router_id');
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_instalacion', function (Blueprint $table) {
            $table->dropColumn('tipo_conexion');
        });
    }
};
