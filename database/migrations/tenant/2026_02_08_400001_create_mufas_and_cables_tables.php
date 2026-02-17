<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mufas (empalmes) y cables (conexiones entre postes, cajas NAP y mufas).
     */
    public function up(): void
    {
        if (Schema::hasTable('mufas')) {
            return;
        }
        Schema::create('mufas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable();
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->foreignId('poste_id')->nullable()->constrained('postes')->nullOnDelete();
            $table->text('notas')->nullable();
            $table->boolean('estado')->default(true);
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        Schema::create('cables', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_origen', 20); // poste, caja_nap, mufa
            $table->unsignedBigInteger('id_origen');
            $table->string('tipo_destino', 20);
            $table->unsignedBigInteger('id_destino');
            $table->string('nombre')->nullable();
            $table->unsignedInteger('metros')->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();

            $table->index(['tipo_origen', 'id_origen']);
            $table->index(['tipo_destino', 'id_destino']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cables');
        Schema::dropIfExists('mufas');
    }
};
