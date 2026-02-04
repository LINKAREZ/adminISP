<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    public function up(): void
    {
        Schema::connection($this->connection)->create('isps', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->string('moneda', 10)->default('PEN');
            $table->string('simbolo_moneda', 10)->default('S/.');
            $table->decimal('igv', 5, 2)->default(18);
            $table->string('database_name', 64)->nullable()->comment('Nombre de la BD tenant para este ISP');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('isps');
    }
};
