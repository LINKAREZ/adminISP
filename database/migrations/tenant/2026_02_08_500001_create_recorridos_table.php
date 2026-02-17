<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Recorrido = una sola entidad de inicio a fin (varios puntos en orden).
     */
    public function up(): void
    {
        if (Schema::hasTable('recorridos')) {
            return;
        }
        Schema::create('recorridos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        Schema::create('recorrido_puntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recorrido_id')->constrained('recorridos')->cascadeOnDelete();
            $table->unsignedSmallInteger('orden');
            $table->string('tipo', 20); // poste, caja_nap, mufa
            $table->unsignedBigInteger('nodo_id');
            $table->timestamps();

            $table->index(['recorrido_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recorrido_puntos');
        Schema::dropIfExists('recorridos');
    }
};
