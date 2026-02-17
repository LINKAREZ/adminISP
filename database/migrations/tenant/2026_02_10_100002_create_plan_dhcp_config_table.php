<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Configuración DHCP por plan (servidor, interfaz, pool, red, gateway, DNS, lease).
     */
    public function up(): void
    {
        if (Schema::hasTable('plan_dhcp_config')) {
            return;
        }
        Schema::create('plan_dhcp_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('planes')->cascadeOnDelete();
            $table->string('nombre_servidor_routeros')->nullable()->comment('Nombre del servidor DHCP en RouterOS');
            $table->string('interfaz')->nullable()->comment('Interfaz: ether1, vlan100, bridge-lan, etc.');
            $table->string('pool_nombre')->nullable()->comment('Nombre del pool en RouterOS');
            $table->string('red_cidr', 50)->nullable()->comment('Red en CIDR ej. 192.168.1.0/24');
            $table->string('rango_ip')->nullable()->comment('Rango ej. 192.168.1.10-192.168.1.254');
            $table->string('gateway', 45)->nullable();
            $table->string('dns')->nullable();
            $table->string('domain')->nullable();
            $table->string('lease_time', 20)->nullable()->comment('Ej. 1d, 3d');
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_dhcp_config');
    }
};
