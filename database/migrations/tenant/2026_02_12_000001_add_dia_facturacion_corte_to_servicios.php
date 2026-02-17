<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Día de facturación y día de corte por servicio (Fase 0 - Fundamentos).
     * Si son null, se usan los valores globales de config isp.comprobantes.
     */
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->unsignedTinyInteger('dia_facturacion')->nullable()->after('fecha_corte')->comment('Día del mes 1-28 para emitir recibo; null = usar config');
            $table->unsignedTinyInteger('dia_corte')->nullable()->after('dia_facturacion')->comment('Día del mes 1-28 para aplicar corte; null = usar lógica por vencimiento');
            $table->unsignedTinyInteger('dias_gracia')->nullable()->after('dia_corte')->comment('Días de gracia tras vencimiento; null = usar config');
        });
    }

    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->dropColumn(['dia_facturacion', 'dia_corte', 'dias_gracia']);
        });
    }
};
