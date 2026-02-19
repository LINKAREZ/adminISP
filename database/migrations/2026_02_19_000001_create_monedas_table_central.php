<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    public function up(): void
    {
        Schema::connection($this->connection)->create('monedas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique()->comment('Ej: PEN, USD');
            $table->string('nombre', 64)->comment('Ej: Soles Peruanos');
            $table->string('simbolo', 10)->comment('Ej: S/., $');
            $table->boolean('activo')->default(true);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('monedas');
    }
};
