<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    public function up(): void
    {
        Schema::connection($this->connection)->create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('isp_id')->nullable()->constrained('isps')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('roles');
    }
};
