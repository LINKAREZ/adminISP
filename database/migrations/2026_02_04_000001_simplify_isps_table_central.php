<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    /**
     * Elimina columnas innecesarias de isps. Solo las que existen (compatible con instalaciones nuevas y antiguas).
     */
    public function up(): void
    {
        $columnsToDrop = [
            'ruc',
            'direccion',
            'telefono',
            'email',
            'logo',
            'dia_emision',
            'dias_gracia',
            'dias_vencimiento',
            'generar_recibos_automaticos',
            'serie_boleta',
            'serie_factura',
            'corte_automatico',
            'dias_antes_corte',
            'reactivacion_automatica',
            'notificar_vencimiento',
            'dias_notificacion_vencimiento',
            'whatsapp_habilitado',
            'email_habilitado',
            'sms_habilitado',
        ];

        foreach ($columnsToDrop as $column) {
            if (Schema::connection($this->connection)->hasColumn('isps', $column)) {
                Schema::connection($this->connection)->table('isps', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('isps', function (Blueprint $table) {
            $table->string('ruc', 20)->nullable()->after('nombre');
            $table->string('direccion')->nullable()->after('ruc');
            $table->string('telefono', 50)->nullable()->after('direccion');
            $table->string('email')->nullable()->after('telefono');
            $table->string('logo')->nullable()->after('email');
            $table->unsignedTinyInteger('dia_emision')->nullable()->after('igv');
            $table->unsignedSmallInteger('dias_gracia')->nullable()->after('dia_emision');
            $table->unsignedSmallInteger('dias_vencimiento')->nullable()->after('dias_gracia');
            $table->boolean('generar_recibos_automaticos')->default(true)->after('dias_vencimiento');
            $table->string('serie_boleta', 20)->nullable()->after('generar_recibos_automaticos');
            $table->string('serie_factura', 20)->nullable()->after('serie_boleta');
            $table->boolean('corte_automatico')->default(false)->after('serie_factura');
            $table->unsignedSmallInteger('dias_antes_corte')->nullable()->after('corte_automatico');
            $table->boolean('reactivacion_automatica')->default(false)->after('dias_antes_corte');
            $table->boolean('notificar_vencimiento')->default(false)->after('reactivacion_automatica');
            $table->unsignedSmallInteger('dias_notificacion_vencimiento')->nullable()->after('notificar_vencimiento');
            $table->boolean('whatsapp_habilitado')->default(false)->after('dias_notificacion_vencimiento');
            $table->boolean('email_habilitado')->default(false)->after('whatsapp_habilitado');
            $table->boolean('sms_habilitado')->default(false)->after('email_habilitado');
        });
    }
};
