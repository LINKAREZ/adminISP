<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    public function up(): void
    {
        if (Schema::connection($this->connection)->hasTable('plans')
            && !Schema::connection($this->connection)->hasColumn('plans', 'max_routers')) {
            Schema::connection($this->connection)->table('plans', function (Blueprint $table) {
                $table->unsignedInteger('max_routers')->nullable()->after('sort_order')
                    ->comment('Null = ilimitado (planes de pago por router)');
            });
        }
    }

    public function down(): void
    {
        if (Schema::connection($this->connection)->hasColumn('plans', 'max_routers')) {
            Schema::connection($this->connection)->table('plans', function (Blueprint $table) {
                $table->dropColumn('max_routers');
            });
        }
    }
};
