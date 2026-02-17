<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes_instalacion', function (Blueprint $table) {
            if (!Schema::hasColumn('ordenes_instalacion', 'hora_agendada')) {
                $table->time('hora_agendada')->nullable()->after('fecha_programada');
            }
        });

        if (Schema::hasTable('orden_instalacion_archivos')) {
            return;
        }
        Schema::create('orden_instalacion_archivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_instalacion_id')->constrained('ordenes_instalacion')->cascadeOnDelete();
            $table->string('nombre_archivo');
            $table->string('ruta');
            $table->string('tipo', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_instalacion_archivos');
        Schema::table('ordenes_instalacion', function (Blueprint $table) {
            if (Schema::hasColumn('ordenes_instalacion', 'hora_agendada')) {
                $table->dropColumn('hora_agendada');
            }
        });
    }
};
