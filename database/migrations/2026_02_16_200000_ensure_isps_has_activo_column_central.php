<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Asegura que la tabla isps (BD central) tenga la columna activo.
 * Algunos entornos pueden tener la tabla creada sin esta columna.
 */
return new class extends Migration
{
    protected $connection = 'mysql';

    public function up(): void
    {
        if (!Schema::connection($this->connection)->hasTable('isps')) {
            return;
        }
        if (Schema::connection($this->connection)->hasColumn('isps', 'activo')) {
            return;
        }
        Schema::connection($this->connection)->table('isps', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('nombre');
        });
    }

    public function down(): void
    {
        if (!Schema::connection($this->connection)->hasTable('isps')) {
            return;
        }
        if (!Schema::connection($this->connection)->hasColumn('isps', 'activo')) {
            return;
        }
        Schema::connection($this->connection)->table('isps', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }
};
