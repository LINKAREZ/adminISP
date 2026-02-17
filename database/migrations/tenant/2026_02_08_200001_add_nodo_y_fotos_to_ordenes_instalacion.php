<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade nodo_id y fotos de referencia a la orden de instalación (wizard paso 2 y 3).
     */
    public function up(): void
    {
        Schema::table('ordenes_instalacion', function (Blueprint $table) {
            $table->foreignId('nodo_id')->nullable()->after('router_id')->constrained('nodos')->nullOnDelete();
            $table->string('foto_1')->nullable()->after('departamento');
            $table->string('foto_1_titulo')->nullable()->after('foto_1');
            $table->string('foto_2')->nullable()->after('foto_1_titulo');
            $table->string('foto_2_titulo')->nullable()->after('foto_2');
            $table->string('foto_3')->nullable()->after('foto_2_titulo');
            $table->string('foto_3_titulo')->nullable()->after('foto_3');
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_instalacion', function (Blueprint $table) {
            $table->dropForeign(['nodo_id']);
            $table->dropColumn([
                'nodo_id', 'foto_1', 'foto_1_titulo', 'foto_2', 'foto_2_titulo',
                'foto_3', 'foto_3_titulo',
            ]);
        });
    }
};
