<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    public function up(): void
    {
        Schema::connection($this->connection)->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('isp_id')->nullable()->constrained('isps')->nullOnDelete();
            $table->boolean('is_default_admin')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('users');
    }
};
