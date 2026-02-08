<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade columnas que puede que falten en onu_modelos (migraciones antiguas no las tenían).
     */
    public function up(): void
    {
        Schema::table('onu_modelos', function (Blueprint $table) {
            if (!Schema::hasColumn('onu_modelos', 'orden')) {
                $table->unsignedSmallInteger('orden')->default(0)->after('estado');
            }
            if (!Schema::hasColumn('onu_modelos', 'requiere_transformacion')) {
                $table->boolean('requiere_transformacion')->default(false)->after('orden');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onu_modelos', function (Blueprint $table) {
            if (Schema::hasColumn('onu_modelos', 'orden')) {
                $table->dropColumn('orden');
            }
            if (Schema::hasColumn('onu_modelos', 'requiere_transformacion')) {
                $table->dropColumn('requiere_transformacion');
            }
        });
    }
};
