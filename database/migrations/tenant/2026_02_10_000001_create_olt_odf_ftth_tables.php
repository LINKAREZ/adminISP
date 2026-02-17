<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Trazabilidad FTTH: OLT → ODF → cable (recorrido/hilo) → splitter → NAP → abonado.
     */
    public function up(): void
    {
        Schema::create('olts', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('ubicacion', 255)->nullable();
            $table->text('notas')->nullable();
            $table->boolean('estado')->default(true);
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        Schema::create('olt_puertos_pon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained('olts')->cascadeOnDelete();
            $table->unsignedSmallInteger('numero');
            $table->string('nombre', 50)->nullable(); // ej. PON1
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
            $table->unique(['olt_id', 'numero']);
        });

        Schema::create('odfs', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('ubicacion', 255)->nullable();
            $table->text('notas')->nullable();
            $table->boolean('estado')->default(true);
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        Schema::create('odf_puertos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odf_id')->constrained('odfs')->cascadeOnDelete();
            $table->unsignedSmallInteger('numero_puerto');
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
            $table->unique(['odf_id', 'numero_puerto']);
        });

        Schema::create('enlace_olt_odf', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_puerto_pon_id')->constrained('olt_puertos_pon')->cascadeOnDelete();
            $table->foreignId('odf_puerto_id')->constrained('odf_puertos')->cascadeOnDelete();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
            $table->unique('odf_puerto_id'); // un puerto ODF solo puede recibir un OLT PON
        });

        Schema::create('recorrido_hilo_origen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recorrido_id')->constrained('recorridos')->cascadeOnDelete();
            $table->unsignedSmallInteger('numero_hilo'); // 1..N (hilo 4 del cable de 12)
            $table->foreignId('odf_puerto_id')->constrained('odf_puertos')->cascadeOnDelete();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
            $table->unique(['recorrido_id', 'numero_hilo']);
        });

        Schema::create('splitters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mufa_id')->constrained('mufas')->cascadeOnDelete();
            $table->foreignId('recorrido_id')->constrained('recorridos')->cascadeOnDelete();
            $table->unsignedSmallInteger('numero_hilo'); // hilo del cable que entra al splitter
            $table->unsignedSmallInteger('ratio_entrada')->default(1);
            $table->unsignedSmallInteger('ratio_salida'); // ej. 8 para 1:8
            $table->string('codigo', 50)->nullable();
            $table->text('notas')->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
            $table->index(['recorrido_id', 'numero_hilo']);
        });

        Schema::create('splitter_salidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('splitter_id')->constrained('splitters')->cascadeOnDelete();
            $table->unsignedSmallInteger('numero_salida'); // 1..ratio_salida
            $table->foreignId('caja_nap_id')->nullable()->constrained('cajas_nap')->nullOnDelete();
            $table->foreignId('splitter_destino_id')->nullable()->constrained('splitters')->nullOnDelete(); // cascada
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
            $table->unique(['splitter_id', 'numero_salida']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('splitter_salidas');
        Schema::dropIfExists('splitters');
        Schema::dropIfExists('recorrido_hilo_origen');
        Schema::dropIfExists('enlace_olt_odf');
        Schema::dropIfExists('odf_puertos');
        Schema::dropIfExists('odfs');
        Schema::dropIfExists('olt_puertos_pon');
        Schema::dropIfExists('olts');
    }
};
