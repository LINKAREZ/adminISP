<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Renombrar routers.plan_id a licencia_id (referencia a licencias central). */
    public function up(): void
    {
        if (Schema::hasColumn('routers', 'plan_id') && !Schema::hasColumn('routers', 'licencia_id')) {
            Schema::table('routers', function (Blueprint $table) {
                $table->renameColumn('plan_id', 'licencia_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('routers', 'licencia_id')) {
            Schema::table('routers', function (Blueprint $table) {
                $table->renameColumn('licencia_id', 'plan_id');
            });
        }
    }
};
