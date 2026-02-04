<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    public function up(): void
    {
        Schema::connection($this->connection)->create('isps', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ruc', 20)->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('activo')->default(true);
            $table->string('moneda', 10)->nullable();
            $table->string('simbolo_moneda', 10)->nullable();
            $table->decimal('igv', 5, 2)->default(18);
            $table->unsignedTinyInteger('dia_emision')->nullable();
            $table->unsignedSmallInteger('dias_gracia')->nullable();
            $table->unsignedSmallInteger('dias_vencimiento')->nullable();
            $table->boolean('generar_recibos_automaticos')->default(true);
            $table->string('serie_boleta', 20)->nullable();
            $table->string('serie_factura', 20)->nullable();
            $table->boolean('corte_automatico')->default(false);
            $table->unsignedSmallInteger('dias_antes_corte')->nullable();
            $table->boolean('reactivacion_automatica')->default(false);
            $table->boolean('notificar_vencimiento')->default(false);
            $table->unsignedSmallInteger('dias_notificacion_vencimiento')->nullable();
            $table->boolean('whatsapp_habilitado')->default(false);
            $table->boolean('email_habilitado')->default(false);
            $table->boolean('sms_habilitado')->default(false);
            $table->string('database_name', 64)->nullable()->comment('Nombre de la BD tenant para este ISP');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('isps');
    }
};
