<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Órdenes de instalación: programar → asignar técnico → completar (crear ubicación + servicio).
     */
    public function up(): void
    {
        if (Schema::hasTable('ordenes_instalacion')) {
            return;
        }
        Schema::create('ordenes_instalacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('planes')->nullOnDelete();
            $table->foreignId('router_id')->nullable()->constrained('routers')->nullOnDelete();
            $table->string('direccion');
            $table->string('referencia')->nullable();
            $table->string('distrito')->nullable();
            $table->string('provincia')->nullable();
            $table->string('departamento')->nullable();
            $table->string('estado', 30)->default('pendiente'); // pendiente, programada, en_curso, completada, cancelada
            $table->date('fecha_programada')->nullable();
            $table->dateTime('fecha_completada')->nullable();
            $table->unsignedBigInteger('tecnico_id')->nullable(); // ID usuario (tabla central)
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->nullOnDelete();
            $table->text('notas')->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_instalacion');
    }
};
