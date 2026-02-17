<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade hilo_id a servicios para vincular cliente/servicio con caja NAP y poste.
     */
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->foreignId('hilo_id')->nullable()->after('ubicacion_id')->constrained('hilos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->dropForeign(['hilo_id']);
        });
    }
};
