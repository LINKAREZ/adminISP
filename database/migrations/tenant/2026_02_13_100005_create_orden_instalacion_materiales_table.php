<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orden_instalacion_materiales')) {
            return;
        }
        Schema::create('orden_instalacion_materiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_instalacion_id')->constrained('ordenes_instalacion')->cascadeOnDelete();
            $table->foreignId('articulo_id')->constrained('articulos')->cascadeOnDelete();
            $table->foreignId('almacen_id')->constrained('almacenes')->cascadeOnDelete();
            $table->decimal('cantidad', 12, 3);
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_instalacion_materiales');
    }
};
