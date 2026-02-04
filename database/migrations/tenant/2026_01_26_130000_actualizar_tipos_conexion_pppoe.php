<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('planes')) {
            return;
        }
        DB::statement("ALTER TABLE planes MODIFY tipo_conexion ENUM('pppoe_usuario_unico','pppoe_usuario_compartido','pppoe_vlan','ip_estatica','ip_fija_vlan','dhcp_dinamico','pppoe','estatica','dhcp') NOT NULL");
        DB::statement("ALTER TABLE servicios MODIFY tipo_pppoe ENUM('unico','diferente','usuario_compartido','usuario_unico') NOT NULL");
        if (Schema::hasColumn('onu_modelos', 'tipo_conexion_default')) {
            DB::statement("ALTER TABLE onu_modelos MODIFY tipo_conexion_default ENUM('pppoe_usuario_unico','pppoe_usuario_compartido','pppoe_vlan','ip_estatica','ip_fija_vlan','dhcp_dinamico','pppoe','estatica','dhcp') NULL");
        }

        DB::table('planes')
            ->whereIn('tipo_conexion', ['pppoe_usuario_unico', 'pppoe_usuario_compartido', 'pppoe_vlan'])
            ->update(['tipo_conexion' => 'pppoe']);

        DB::table('planes')
            ->whereIn('tipo_conexion', ['ip_estatica', 'ip_fija_vlan'])
            ->update(['tipo_conexion' => 'estatica']);

        DB::table('planes')
            ->where('tipo_conexion', 'dhcp_dinamico')
            ->update(['tipo_conexion' => 'dhcp']);

        DB::table('servicios')
            ->where('tipo_pppoe', 'unico')
            ->update(['tipo_pppoe' => 'usuario_compartido']);

        DB::table('servicios')
            ->where('tipo_pppoe', 'diferente')
            ->update(['tipo_pppoe' => 'usuario_unico']);

        if (Schema::hasColumn('onu_modelos', 'tipo_conexion_default')) {
            DB::table('onu_modelos')
                ->whereIn('tipo_conexion_default', ['pppoe_usuario_unico', 'pppoe_usuario_compartido', 'pppoe_vlan'])
                ->update(['tipo_conexion_default' => 'pppoe']);
            DB::table('onu_modelos')
                ->whereIn('tipo_conexion_default', ['ip_estatica', 'ip_fija_vlan'])
                ->update(['tipo_conexion_default' => 'estatica']);
            DB::table('onu_modelos')
                ->where('tipo_conexion_default', 'dhcp_dinamico')
                ->update(['tipo_conexion_default' => 'dhcp']);
        }

        DB::statement("ALTER TABLE planes MODIFY tipo_conexion ENUM('pppoe','estatica','dhcp') NOT NULL");
        DB::statement("ALTER TABLE servicios MODIFY tipo_pppoe ENUM('usuario_compartido','usuario_unico') NOT NULL");
        if (Schema::hasColumn('onu_modelos', 'tipo_conexion_default')) {
            DB::statement("ALTER TABLE onu_modelos MODIFY tipo_conexion_default ENUM('pppoe','estatica','dhcp') NULL");
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE planes MODIFY tipo_conexion ENUM('pppoe','estatica','dhcp','pppoe_usuario_unico','pppoe_usuario_compartido','pppoe_vlan','ip_estatica','ip_fija_vlan','dhcp_dinamico') NOT NULL");
        DB::statement("ALTER TABLE servicios MODIFY tipo_pppoe ENUM('unico','diferente','usuario_compartido','usuario_unico') NOT NULL");
        if (Schema::hasColumn('onu_modelos', 'tipo_conexion_default')) {
            DB::statement("ALTER TABLE onu_modelos MODIFY tipo_conexion_default ENUM('pppoe','estatica','dhcp','pppoe_usuario_unico','pppoe_usuario_compartido','pppoe_vlan','ip_estatica','ip_fija_vlan','dhcp_dinamico') NULL");
        }

        DB::table('planes')
            ->where('tipo_conexion', 'pppoe')
            ->update(['tipo_conexion' => 'pppoe_usuario_unico']);

        DB::table('planes')
            ->where('tipo_conexion', 'estatica')
            ->update(['tipo_conexion' => 'ip_estatica']);

        DB::table('planes')
            ->where('tipo_conexion', 'dhcp')
            ->update(['tipo_conexion' => 'dhcp_dinamico']);

        DB::table('servicios')
            ->where('tipo_pppoe', 'usuario_compartido')
            ->update(['tipo_pppoe' => 'unico']);

        DB::table('servicios')
            ->where('tipo_pppoe', 'usuario_unico')
            ->update(['tipo_pppoe' => 'diferente']);

        if (Schema::hasColumn('onu_modelos', 'tipo_conexion_default')) {
            DB::table('onu_modelos')
                ->where('tipo_conexion_default', 'pppoe')
                ->update(['tipo_conexion_default' => 'pppoe_usuario_unico']);
            DB::table('onu_modelos')
                ->where('tipo_conexion_default', 'estatica')
                ->update(['tipo_conexion_default' => 'ip_estatica']);
            DB::table('onu_modelos')
                ->where('tipo_conexion_default', 'dhcp')
                ->update(['tipo_conexion_default' => 'dhcp_dinamico']);
        }

        DB::statement("ALTER TABLE planes MODIFY tipo_conexion ENUM('pppoe_usuario_unico','pppoe_usuario_compartido','pppoe_vlan','ip_estatica','ip_fija_vlan','dhcp_dinamico') NOT NULL");
        DB::statement("ALTER TABLE servicios MODIFY tipo_pppoe ENUM('unico','diferente') NOT NULL");
        if (Schema::hasColumn('onu_modelos', 'tipo_conexion_default')) {
            DB::statement("ALTER TABLE onu_modelos MODIFY tipo_conexion_default ENUM('pppoe_usuario_unico','pppoe_usuario_compartido','pppoe_vlan','ip_estatica','ip_fija_vlan','dhcp_dinamico') NULL");
        }
    }
};
