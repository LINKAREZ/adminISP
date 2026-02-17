<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('articulos')) {
            return;
        }
        Schema::create('articulos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo')->nullable();
            $table->string('tipo', 30);
            $table->string('unidad', 20)->default('pza');
            $table->decimal('costo_referencia', 12, 2)->nullable();
            $table->foreignId('onu_modelo_id')->nullable()->constrained('onu_modelos')->nullOnDelete();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });
        Schema::table('articulos', function (Blueprint $table) {
            $table->index(['isp_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articulos');
    }
};
