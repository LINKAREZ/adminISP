<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('promesas_pago', 'hora_compromiso')) {
            Schema::table('promesas_pago', function (Blueprint $table) {
                $table->time('hora_compromiso')->default('13:00:00')->after('fecha_compromiso');
            });
        }
    }

    public function down(): void
    {
        Schema::table('promesas_pago', function (Blueprint $table) {
            $table->dropColumn('hora_compromiso');
        });
    }
};
