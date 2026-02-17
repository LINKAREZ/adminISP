<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea tablas del módulo Infraestructura: postes, cajas NAP, hilos.
     */
    public function up(): void
    {
        if (Schema::hasTable('postes')) {
            return;
        }
        Schema::create('postes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable();
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->string('direccion')->nullable();
            $table->string('zona')->nullable();
            $table->text('notas')->nullable();
            $table->boolean('estado')->default(true);
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        Schema::create('cajas_nap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poste_id')->constrained('postes')->cascadeOnDelete();
            $table->string('codigo')->nullable();
            $table->unsignedSmallInteger('capacidad_puertos')->default(8);
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->text('notas')->nullable();
            $table->boolean('estado')->default(true);
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        Schema::create('hilos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_nap_id')->constrained('cajas_nap')->cascadeOnDelete();
            $table->unsignedSmallInteger('numero_puerto');
            $table->string('estado', 20)->default('libre'); // libre, ocupado, reservado
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();

            $table->unique(['caja_nap_id', 'numero_puerto']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hilos');
        Schema::dropIfExists('cajas_nap');
        Schema::dropIfExists('postes');
    }
};
