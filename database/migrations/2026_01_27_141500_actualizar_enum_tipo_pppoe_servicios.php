<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE servicios MODIFY tipo_pppoe ENUM('unico','diferente','usuario_compartido','usuario_unico') NOT NULL");

        DB::table('servicios')
            ->where('tipo_pppoe', 'unico')
            ->update(['tipo_pppoe' => 'usuario_compartido']);

        DB::table('servicios')
            ->where('tipo_pppoe', 'diferente')
            ->update(['tipo_pppoe' => 'usuario_unico']);

        DB::statement("ALTER TABLE servicios MODIFY tipo_pppoe ENUM('usuario_compartido','usuario_unico') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE servicios MODIFY tipo_pppoe ENUM('unico','diferente','usuario_compartido','usuario_unico') NOT NULL");

        DB::table('servicios')
            ->where('tipo_pppoe', 'usuario_compartido')
            ->update(['tipo_pppoe' => 'unico']);

        DB::table('servicios')
            ->where('tipo_pppoe', 'usuario_unico')
            ->update(['tipo_pppoe' => 'diferente']);

        DB::statement("ALTER TABLE servicios MODIFY tipo_pppoe ENUM('unico','diferente') NOT NULL");
    }
};
