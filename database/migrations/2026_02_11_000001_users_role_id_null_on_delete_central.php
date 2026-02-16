<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    /**
     * RBAC extensible: evitar que borrar un rol borre a los usuarios.
     * Cambia la FK users.role_id de cascadeOnDelete a nullOnDelete (role_id debe ser nullable).
     */
    public function up(): void
    {
        $conn = Schema::connection($this->connection)->getConnection();
        $driver = $conn->getDriverName();
        if ($driver !== 'mysql') {
            Schema::connection($this->connection)->table('users', function (Blueprint $table) {
                $table->dropForeign(['role_id']);
                $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
            });
            return;
        }
        Schema::connection($this->connection)->table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });
        DB::connection($this->connection)->statement('ALTER TABLE users MODIFY role_id BIGINT UNSIGNED NULL');
        Schema::connection($this->connection)->table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });
    }
};
