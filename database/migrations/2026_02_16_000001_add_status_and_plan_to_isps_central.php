<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    public function up(): void
    {
        if (!Schema::connection($this->connection)->hasTable('plans')) {
            Schema::connection($this->connection)->create('plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug', 50)->unique();
                $table->unsignedInteger('max_clientes')->nullable();
                $table->unsignedInteger('max_usuarios')->nullable();
                $table->unsignedInteger('max_storage_mb')->nullable();
                $table->decimal('price_monthly', 10, 2)->nullable();
                $table->decimal('price_yearly', 10, 2)->nullable();
                $table->string('currency', 10)->default('USD');
                $table->string('interval', 20)->default('month');
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        Schema::connection($this->connection)->table('isps', function (Blueprint $table) {
            if (!Schema::connection($this->connection)->hasColumn('isps', 'status')) {
                $table->string('status', 20)->default('active')->after('activo')
                    ->comment('pending, active, suspended, cancelled');
            }
        });

        Schema::connection($this->connection)->table('isps', function (Blueprint $table) {
            if (!Schema::connection($this->connection)->hasColumn('isps', 'plan_id')) {
                $table->unsignedBigInteger('plan_id')->nullable()->after('status');
                $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('isps', function (Blueprint $table) {
            if (Schema::connection($this->connection)->hasColumn('isps', 'plan_id')) {
                $table->dropForeign(['plan_id']);
                $table->dropColumn('plan_id');
            }
            if (Schema::connection($this->connection)->hasColumn('isps', 'status')) {
                $table->dropColumn('status');
            }
        });
        Schema::connection($this->connection)->dropIfExists('plans');
    }
};
