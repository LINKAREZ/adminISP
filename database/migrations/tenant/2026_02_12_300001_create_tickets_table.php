<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('asunto');
            $table->string('estado', 30)->default('abierto'); // abierto, en_progreso, cerrado
            $table->unsignedBigInteger('asignado_a')->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        Schema::create('ticket_mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->comment('null = mensaje del cliente');
            $table->text('mensaje');
            $table->string('adjunto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_mensajes');
        Schema::dropIfExists('tickets');
    }
};
