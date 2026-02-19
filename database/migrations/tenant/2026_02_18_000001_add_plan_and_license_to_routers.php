<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            if (!Schema::hasColumn('routers', 'plan_id')) {
                $table->unsignedBigInteger('plan_id')->nullable()->after('estado')
                    ->comment('ID plan SaaS central (referencia); límite de clientes por router');
            }
            if (!Schema::hasColumn('routers', 'license_starts_at')) {
                $table->date('license_starts_at')->nullable()->after('plan_id');
            }
            if (!Schema::hasColumn('routers', 'license_expires_at')) {
                $table->date('license_expires_at')->nullable()->after('license_starts_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            if (Schema::hasColumn('routers', 'license_expires_at')) {
                $table->dropColumn('license_expires_at');
            }
            if (Schema::hasColumn('routers', 'license_starts_at')) {
                $table->dropColumn('license_starts_at');
            }
            if (Schema::hasColumn('routers', 'plan_id')) {
                $table->dropColumn('plan_id');
            }
        });
    }
};
