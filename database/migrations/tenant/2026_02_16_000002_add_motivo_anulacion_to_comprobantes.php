<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('comprobantes')) {
            return;
        }
        Schema::table('comprobantes', function (Blueprint $table) {
            if (!Schema::hasColumn('comprobantes', 'motivo_anulacion')) {
                $table->text('motivo_anulacion')->nullable()->after('estado');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('comprobantes')) {
            return;
        }
        Schema::table('comprobantes', function (Blueprint $table) {
            $table->dropColumn('motivo_anulacion');
        });
    }
};
