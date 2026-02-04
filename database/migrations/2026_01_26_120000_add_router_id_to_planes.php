<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('planes')) {
            return;
        }

        Schema::table('planes', function (Blueprint $table) {
            if (!Schema::hasColumn('planes', 'router_id')) {
                $table->foreignId('router_id')->nullable()->after('nombre')->constrained('routers')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('planes')) {
            return;
        }

        Schema::table('planes', function (Blueprint $table) {
            if (Schema::hasColumn('planes', 'router_id')) {
                $table->dropForeign(['router_id']);
                $table->dropColumn('router_id');
            }
        });
    }
};
