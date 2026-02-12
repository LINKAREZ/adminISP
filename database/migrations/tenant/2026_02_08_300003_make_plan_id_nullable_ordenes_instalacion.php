<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hacer plan_id nullable para permitir crear la orden en paso 1 solo con el cliente;
     * el plan se asigna en el paso 2 (Nodo / Router / Plan).
     */
    public function up(): void
    {
        Schema::table('ordenes_instalacion', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_instalacion', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable(false)->change();
        });
    }
};
