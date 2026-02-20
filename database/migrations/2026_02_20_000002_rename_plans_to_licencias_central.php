<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    /** Quitar rastro "plan": plans→licencias, isp_plan→isp_licencia, plan_id→licencia_id (central). */
    public function up(): void
    {
        $c = $this->connection;
        if (!Schema::connection($c)->hasTable('plans') && !Schema::connection($c)->hasTable('licencias')) {
            return;
        }
        if (Schema::connection($c)->hasTable('plans')) {
            if (Schema::connection($c)->hasTable('isp_plan')) {
                Schema::connection($c)->table('isp_plan', function (Blueprint $table) {
                    $table->dropForeign(['plan_id']);
                });
            }
            Schema::connection($c)->rename('plans', 'licencias');
            if (Schema::connection($c)->hasTable('isp_plan')) {
                Schema::connection($c)->rename('isp_plan', 'isp_licencia');
                Schema::connection($c)->table('isp_licencia', function (Blueprint $table) {
                    $table->renameColumn('plan_id', 'licencia_id');
                });
                Schema::connection($c)->table('isp_licencia', function (Blueprint $table) {
                    $table->foreign('licencia_id')->references('id')->on('licencias')->cascadeOnDelete();
                });
            }
        }
        if (Schema::connection($c)->hasColumn('isps', 'plan_id')) {
            Schema::connection($c)->table('isps', function (Blueprint $table) {
                $table->dropForeign(['plan_id']);
            });
            Schema::connection($c)->table('isps', function (Blueprint $table) {
                $table->renameColumn('plan_id', 'licencia_id');
            });
            Schema::connection($c)->table('isps', function (Blueprint $table) {
                $table->foreign('licencia_id')->references('id')->on('licencias')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        $c = $this->connection;
        if (Schema::connection($c)->hasTable('isp_licencia')) {
            Schema::connection($c)->table('isp_licencia', function (Blueprint $table) {
                $table->dropForeign(['licencia_id']);
            });
            Schema::connection($c)->table('isp_licencia', function (Blueprint $table) {
                $table->renameColumn('licencia_id', 'plan_id');
            });
            Schema::connection($c)->rename('isp_licencia', 'isp_plan');
        }
        if (Schema::connection($c)->hasTable('licencias')) {
            Schema::connection($c)->rename('licencias', 'plans');
            if (Schema::connection($c)->hasTable('isp_plan')) {
                Schema::connection($c)->table('isp_plan', function (Blueprint $table) {
                    $table->dropForeign(['plan_id']);
                    $table->foreign('plan_id')->references('id')->on('plans')->cascadeOnDelete();
                });
            }
        }
        if (Schema::connection($c)->hasColumn('isps', 'licencia_id')) {
            Schema::connection($c)->table('isps', function (Blueprint $table) {
                $table->dropForeign(['licencia_id']);
                $table->renameColumn('licencia_id', 'plan_id');
                $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
            });
        }
    }
};
