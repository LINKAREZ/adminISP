<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Datos del cable del recorrido: tipo, marca, año, buffers y hilos.
     */
    public function up(): void
    {
        Schema::table('recorridos', function (Blueprint $table) {
            if (! Schema::hasColumn('recorridos', 'tipo_cable')) {
                $table->string('tipo_cable', 100)->nullable()->after('nombre');
            }
            if (! Schema::hasColumn('recorridos', 'marca_cable')) {
                $table->string('marca_cable', 100)->nullable()->after('tipo_cable');
            }
            if (! Schema::hasColumn('recorridos', 'anio_fabricacion')) {
                $table->unsignedSmallInteger('anio_fabricacion')->nullable()->after('marca_cable');
            }
            if (! Schema::hasColumn('recorridos', 'cantidad_buffer')) {
                $table->unsignedInteger('cantidad_buffer')->nullable()->after('anio_fabricacion');
            }
            if (! Schema::hasColumn('recorridos', 'hilos_por_buffer')) {
                $table->unsignedInteger('hilos_por_buffer')->nullable()->after('cantidad_buffer');
            }
            if (! Schema::hasColumn('recorridos', 'cantidad_total_hilos')) {
                $table->unsignedInteger('cantidad_total_hilos')->nullable()->after('hilos_por_buffer');
            }
        });
    }

    public function down(): void
    {
        Schema::table('recorridos', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_cable',
                'marca_cable',
                'anio_fabricacion',
                'cantidad_buffer',
                'hilos_por_buffer',
                'cantidad_total_hilos',
            ]);
        });
    }
};
