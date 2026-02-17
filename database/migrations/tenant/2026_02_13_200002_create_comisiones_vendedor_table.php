<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('comisiones_vendedor')) {
            return;
        }
        Schema::create('comisiones_vendedor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendedor_id');
            $table->foreignId('orden_instalacion_id')->constrained('ordenes_instalacion')->cascadeOnDelete();
            $table->decimal('monto', 12, 2);
            $table->date('fecha_cumplimiento_3mes');
            $table->string('estado', 20)->default('pendiente');
            $table->date('fecha_pago')->nullable();
            $table->foreignId('comprobante_id')->nullable()->constrained('comprobantes')->nullOnDelete();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });
        Schema::table('comisiones_vendedor', function (Blueprint $table) {
            $table->unique('orden_instalacion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comisiones_vendedor');
    }
};
