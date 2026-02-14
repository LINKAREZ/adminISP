<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    public function up(): void
    {
        if (!Schema::connection($this->connection)->hasTable('tenant_requests')) {
            Schema::connection($this->connection)->create('tenant_requests', function (Blueprint $table) {
                $table->id();
                $table->string('nombre_isp');
                $table->string('email');
                $table->string('telefono', 50)->nullable();
                $table->text('mensaje')->nullable();
                $table->string('status', 20)->default('pending')->comment('pending, approved, rejected');
                $table->unsignedBigInteger('isp_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::connection($this->connection)->hasTable('tenant_activation_tokens')) {
            Schema::connection($this->connection)->create('tenant_activation_tokens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('isp_id')->constrained('isps')->cascadeOnDelete();
                $table->string('token', 64)->unique();
                $table->timestamp('expires_at');
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::connection($this->connection)->hasTable('platform_settings')) {
            Schema::connection($this->connection)->create('platform_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('type', 20)->default('string');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('tenant_activation_tokens');
        Schema::connection($this->connection)->dropIfExists('tenant_requests');
        Schema::connection($this->connection)->dropIfExists('platform_settings');
    }
};
