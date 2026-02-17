<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade fecha_corte a servicios para prorrateo cuando el cliente suspende o corta.
     * Se registra cuando estado pasa a 'cortado'; se limpia al reactivar.
     */
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->date('fecha_corte')->nullable()->after('fecha_activacion_definitiva');
        });
    }

    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->dropColumn('fecha_corte');
        });
    }
};
