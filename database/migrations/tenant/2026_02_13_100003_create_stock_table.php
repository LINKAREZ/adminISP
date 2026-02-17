<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock')) {
            return;
        }
        Schema::create('stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('almacen_id')->constrained('almacenes')->cascadeOnDelete();
            $table->foreignId('articulo_id')->constrained('articulos')->cascadeOnDelete();
            $table->decimal('cantidad', 12, 3)->default(0);
            $table->decimal('costo_promedio', 12, 2)->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });
        Schema::table('stock', function (Blueprint $table) {
            $table->unique(['almacen_id', 'articulo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock');
    }
};
