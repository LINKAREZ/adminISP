<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade a comprobantes las columnas usadas por ComprobanteService y el modelo
 * (periodo_servicio, fechas de servicio, anulación) que no estaban en el create original.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('comprobantes')) {
            return;
        }

        Schema::table('comprobantes', function (Blueprint $table) {
            if (!Schema::hasColumn('comprobantes', 'tipo_nota')) {
                $table->string('tipo_nota', 20)->nullable()->after('estado');
            }
            if (!Schema::hasColumn('comprobantes', 'motivo_nota')) {
                $table->text('motivo_nota')->nullable()->after('tipo_nota');
            }
            if (!Schema::hasColumn('comprobantes', 'periodo_servicio')) {
                $table->string('periodo_servicio', 7)->nullable()->after('orden_compra')->comment('Ej: 2026-01');
            }
            if (!Schema::hasColumn('comprobantes', 'fecha_inicio_servicio')) {
                $table->date('fecha_inicio_servicio')->nullable()->after('periodo_servicio');
            }
            if (!Schema::hasColumn('comprobantes', 'fecha_fin_servicio')) {
                $table->date('fecha_fin_servicio')->nullable()->after('fecha_inicio_servicio');
            }
            if (!Schema::hasColumn('comprobantes', 'anulado')) {
                $table->boolean('anulado')->default(false)->after('fecha_fin_servicio');
            }
            if (!Schema::hasColumn('comprobantes', 'anulado_at')) {
                $table->timestamp('anulado_at')->nullable()->after('anulado');
            }
            if (!Schema::hasColumn('comprobantes', 'anulado_por')) {
                $table->unsignedBigInteger('anulado_por')->nullable()->after('anulado_at')->comment('user_id central');
            }
            if (!Schema::hasColumn('comprobantes', 'notas')) {
                $table->text('notas')->nullable()->after('generado_por');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('comprobantes')) {
            return;
        }

        Schema::table('comprobantes', function (Blueprint $table) {
            $columns = [
                'tipo_nota', 'motivo_nota', 'periodo_servicio',
                'fecha_inicio_servicio', 'fecha_fin_servicio',
                'anulado', 'anulado_at', 'anulado_por', 'notas',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('comprobantes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
