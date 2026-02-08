<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade hasta 3 fotos de la casa por ubicación.
     */
    public function up(): void
    {
        Schema::table('ubicaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('ubicaciones', 'foto_1')) {
                $table->string('foto_1', 500)->nullable()->after('notas');
            }
            if (!Schema::hasColumn('ubicaciones', 'foto_2')) {
                $table->string('foto_2', 500)->nullable()->after('foto_1');
            }
            if (!Schema::hasColumn('ubicaciones', 'foto_3')) {
                $table->string('foto_3', 500)->nullable()->after('foto_2');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ubicaciones', function (Blueprint $table) {
            if (Schema::hasColumn('ubicaciones', 'foto_1')) {
                $table->dropColumn('foto_1');
            }
            if (Schema::hasColumn('ubicaciones', 'foto_2')) {
                $table->dropColumn('foto_2');
            }
            if (Schema::hasColumn('ubicaciones', 'foto_3')) {
                $table->dropColumn('foto_3');
            }
        });
    }
};
