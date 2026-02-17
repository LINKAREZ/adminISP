<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes_instalacion', function (Blueprint $table) {
            if (!Schema::hasColumn('ordenes_instalacion', 'vendedor_id')) {
                $table->unsignedBigInteger('vendedor_id')->nullable()->after('tecnico_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_instalacion', function (Blueprint $table) {
            if (Schema::hasColumn('ordenes_instalacion', 'vendedor_id')) {
                $table->dropColumn('vendedor_id');
            }
        });
    }
};
