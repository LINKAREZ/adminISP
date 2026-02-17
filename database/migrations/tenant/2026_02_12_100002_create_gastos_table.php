<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->decimal('monto', 12, 2);
            $table->string('descripcion', 500)->nullable();
            $table->foreignId('categoria_gasto_id')->constrained('categoria_gastos')->cascadeOnDelete();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
