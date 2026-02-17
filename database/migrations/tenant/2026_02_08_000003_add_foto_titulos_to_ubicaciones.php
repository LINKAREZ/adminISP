<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade títulos editables para cada foto de ubicación (ej: fachada, puerta, piso).
     */
    public function up(): void
    {
        Schema::table('ubicaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('ubicaciones', 'foto_1_titulo')) {
                $table->string('foto_1_titulo', 80)->nullable()->after('foto_1');
            }
            if (!Schema::hasColumn('ubicaciones', 'foto_2_titulo')) {
                $table->string('foto_2_titulo', 80)->nullable()->after('foto_2');
            }
            if (!Schema::hasColumn('ubicaciones', 'foto_3_titulo')) {
                $table->string('foto_3_titulo', 80)->nullable()->after('foto_3');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ubicaciones', function (Blueprint $table) {
            if (Schema::hasColumn('ubicaciones', 'foto_1_titulo')) {
                $table->dropColumn('foto_1_titulo');
            }
            if (Schema::hasColumn('ubicaciones', 'foto_2_titulo')) {
                $table->dropColumn('foto_2_titulo');
            }
            if (Schema::hasColumn('ubicaciones', 'foto_3_titulo')) {
                $table->dropColumn('foto_3_titulo');
            }
        });
    }
};
