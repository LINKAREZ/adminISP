<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('movimientos_inventario')) {
            return;
        }
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('almacen_origen_id')->nullable()->constrained('almacenes')->nullOnDelete();
            $table->foreignId('almacen_destino_id')->nullable()->constrained('almacenes')->nullOnDelete();
            $table->foreignId('articulo_id')->constrained('articulos')->cascadeOnDelete();
            $table->decimal('cantidad', 12, 3);
            $table->string('tipo', 30); // ingreso, salida, traslado, ajuste, consumo_instalacion
            $table->string('referencia_tipo', 50)->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
