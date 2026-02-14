<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clientes')) {
            return;
        }
        Schema::table('clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('clientes', 'dia_facturacion')) {
                $table->unsignedTinyInteger('dia_facturacion')->nullable()->comment('Día del mes 1-28 para facturación; null = usar servicio/zona');
            }
            if (!Schema::hasColumn('clientes', 'dia_corte')) {
                $table->unsignedTinyInteger('dia_corte')->nullable()->comment('Día del mes 1-28 para corte; null = usar servicio/zona');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('clientes')) {
            return;
        }
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['dia_facturacion', 'dia_corte']);
        });
    }
};
