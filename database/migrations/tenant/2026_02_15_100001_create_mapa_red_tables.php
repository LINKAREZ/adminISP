<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mapa_red_proyectos')) {
            return;
        }

        Schema::create('mapa_red_proyectos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        Schema::create('mapa_red_versiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('mapa_red_proyectos')->cascadeOnDelete();
            $table->unsignedInteger('numero');
            $table->longText('snapshot')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->index(['proyecto_id', 'numero']);
        });

        Schema::create('mapa_red_capas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('mapa_red_proyectos')->cascadeOnDelete();
            $table->string('nombre');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('visible')->default(true);
            $table->boolean('bloqueado')->default(false);
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        Schema::create('mapa_red_nodos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('mapa_red_proyectos')->cascadeOnDelete();
            $table->foreignId('capa_id')->nullable()->constrained('mapa_red_capas')->nullOnDelete();
            $table->string('tipo', 50);
            $table->decimal('x', 12, 4)->default(0);
            $table->decimal('y', 12, 4)->default(0);
            $table->json('atributos')->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
            $table->index(['proyecto_id']);
            $table->index(['proyecto_id', 'x', 'y']);
        });

        Schema::create('mapa_red_enlaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('mapa_red_proyectos')->cascadeOnDelete();
            $table->foreignId('origen_id')->constrained('mapa_red_nodos')->cascadeOnDelete();
            $table->foreignId('destino_id')->constrained('mapa_red_nodos')->cascadeOnDelete();
            $table->foreignId('capa_id')->nullable()->constrained('mapa_red_capas')->nullOnDelete();
            $table->string('tipo', 50);
            $table->json('atributos')->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
            $table->index(['proyecto_id']);
            $table->index(['origen_id']);
            $table->index(['destino_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapa_red_enlaces');
        Schema::dropIfExists('mapa_red_nodos');
        Schema::dropIfExists('mapa_red_capas');
        Schema::dropIfExists('mapa_red_versiones');
        Schema::dropIfExists('mapa_red_proyectos');
    }
};
