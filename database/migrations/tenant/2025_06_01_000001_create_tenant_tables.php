<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración tenant: crea todas las tablas operativas de un ISP.
 * El comando que la ejecuta debe establecer la conexión por defecto al tenant antes de migrar.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clientes')) {
            return;
        }
        // clientes
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo_documento', 20)->nullable();
            $table->string('documento', 20)->nullable();
            $table->string('telefonos')->nullable();
            $table->text('notas')->nullable();
            $table->string('nombre_comercial')->nullable();
            $table->string('estado_ruc', 50)->nullable();
            $table->string('condicion_ruc', 50)->nullable();
            $table->string('ubigeo', 10)->nullable();
            $table->decimal('capital', 15, 2)->nullable();
            $table->string('direccion_api')->nullable();
            $table->string('departamento_api')->nullable();
            $table->string('provincia_api')->nullable();
            $table->string('distrito_api')->nullable();
            $table->string('fuente_info', 50)->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        // nodos
        Schema::create('nodos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ubicacion')->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('estado')->default(true);
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        // routers
        Schema::create('routers', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ip_url')->nullable();
            $table->unsignedInteger('puerto_api')->nullable();
            $table->unsignedInteger('puerto_snmp')->nullable();
            $table->string('comunidad')->nullable();
            $table->string('usuario')->nullable();
            $table->string('contraseña')->nullable();
            $table->foreignId('nodo_id')->nullable()->constrained('nodos')->nullOnDelete();
            $table->text('notas')->nullable();
            $table->boolean('estado')->default(true);
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        // ubicaciones
        Schema::create('ubicaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('router_id')->nullable()->constrained('routers')->nullOnDelete();
            $table->string('direccion')->nullable();
            $table->string('referencia')->nullable();
            $table->string('distrito')->nullable();
            $table->string('provincia')->nullable();
            $table->string('departamento')->nullable();
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->text('notas')->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        // medios_pago
        Schema::create('medios_pago', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo', 50)->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        // onu_marcas
        Schema::create('onu_marcas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('estado')->default(true);
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        // onu_modelos
        Schema::create('onu_modelos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marca_id')->constrained('onu_marcas')->cascadeOnDelete();
            $table->string('nombre');
            $table->boolean('estado')->default(true);
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        // planes
        Schema::create('planes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('router_id')->nullable()->constrained('routers')->cascadeOnDelete();
            $table->boolean('estado')->default(true);
            $table->unsignedInteger('velocidad_bajada_mbps')->nullable();
            $table->unsignedInteger('velocidad_subida_mbps')->nullable();
            $table->decimal('precio_mensual', 10, 2)->nullable();
            $table->string('tipo_conexion', 50)->nullable();
            $table->string('perfil_mikrotik')->nullable();
            $table->string('local_address')->nullable();
            $table->string('remote_address')->nullable();
            $table->string('dns')->nullable();
            $table->string('rate_limit')->nullable();
            $table->boolean('ip_fija')->default(false);
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        // series_comprobantes
        Schema::create('series_comprobantes', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 20);
            $table->string('serie', 20);
            $table->unsignedInteger('ultimo_numero')->default(0);
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        // servicios
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ubicacion_id')->constrained('ubicaciones')->cascadeOnDelete();
            $table->foreignId('router_id')->nullable()->constrained('routers')->nullOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('planes')->nullOnDelete();
            $table->string('tipo_pppoe', 50)->nullable();
            $table->string('usuario_pppoe')->nullable();
            $table->string('password_pppoe')->nullable();
            $table->string('mac_address', 50)->nullable();
            $table->string('estado', 20)->default('activo');
            $table->date('fecha_instalacion')->nullable();
            $table->text('notas')->nullable();
            $table->boolean('es_provisional')->default(false);
            $table->timestamp('fecha_activacion_definitiva')->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        // onus
        Schema::create('onus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->nullOnDelete();
            $table->string('serial_number')->nullable();
            $table->string('serial_number_completo', 50)->nullable();
            $table->string('serial_number_olt', 50)->nullable();
            $table->string('mac_address', 50)->nullable();
            $table->string('usuario')->nullable();
            $table->string('password')->nullable();
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->text('notas')->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        // recibos
        Schema::create('recibos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->nullable();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('servicio_id')->constrained('servicios')->cascadeOnDelete();
            $table->string('periodo', 7);
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('monto', 12, 2)->default(0);
            $table->decimal('saldo', 12, 2)->default(0);
            $table->string('estado', 20)->default('pendiente');
            $table->text('notas')->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        // pagos
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->nullOnDelete();
            $table->foreignId('recibo_id')->nullable()->constrained('recibos')->nullOnDelete();
            $table->decimal('monto', 12, 2);
            $table->date('fecha_pago');
            $table->dateTime('fecha_hora')->nullable();
            $table->string('medio_pago')->nullable();
            $table->unsignedBigInteger('medio_pago_id')->nullable();
            $table->string('codigo_seguridad', 20)->nullable();
            $table->string('numero_operacion', 50)->nullable();
            $table->string('referencia')->nullable();
            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->text('notas')->nullable();
            $table->string('captura')->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        // comprobantes
        Schema::create('comprobantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->nullable()->constrained('pagos')->nullOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('tipo', 20);
            $table->string('serie', 20);
            $table->unsignedInteger('numero');
            $table->string('numero_completo', 30)->nullable();
            $table->date('fecha_emision');
            $table->decimal('monto', 12, 2)->default(0);
            $table->string('moneda', 10)->nullable();
            $table->decimal('tipo_cambio', 10, 4)->nullable();
            $table->decimal('subtotal', 12, 2)->nullable();
            $table->decimal('igv', 12, 2)->nullable();
            $table->decimal('descuento', 12, 2)->nullable();
            $table->decimal('exonerado_igv', 12, 2)->nullable();
            $table->string('cliente_nombre')->nullable();
            $table->string('cliente_documento', 20)->nullable();
            $table->string('cliente_tipo_documento', 10)->nullable();
            $table->string('cliente_direccion')->nullable();
            $table->string('hash')->nullable();
            $table->string('codigo_respuesta', 10)->nullable();
            $table->text('mensaje_respuesta')->nullable();
            $table->string('ticket_sunat')->nullable();
            $table->timestamp('enviado_sunat_at')->nullable();
            $table->boolean('enviado_sunat')->default(false);
            $table->string('forma_pago', 20)->nullable();
            $table->date('fecha_vencimiento_pago')->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->string('condiciones_pago')->nullable();
            $table->string('guia_remision')->nullable();
            $table->string('orden_compra')->nullable();
            $table->unsignedBigInteger('comprobante_referencia_id')->nullable();
            $table->unsignedBigInteger('generado_por')->nullable();
            $table->string('estado', 20)->default('emitido');
            $table->timestamps();
        });

        // comprobante_items
        Schema::create('comprobante_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_id')->constrained('comprobantes')->cascadeOnDelete();
            $table->string('descripcion')->nullable();
            $table->decimal('cantidad', 12, 4)->default(1);
            $table->string('unidad', 20)->nullable();
            $table->decimal('precio_unitario', 12, 4)->nullable();
            $table->decimal('subtotal', 12, 2)->nullable();
            $table->decimal('igv', 12, 2)->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        // promesas_pago
        Schema::create('promesas_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recibo_id')->constrained('recibos')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->nullOnDelete();
            $table->date('fecha_compromiso');
            $table->time('hora_compromiso')->default('13:00:00');
            $table->decimal('monto_comprometido', 12, 2);
            $table->string('estado', 20)->default('pendiente');
            $table->timestamp('cumplida_at')->nullable();
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        // reglas
        Schema::create('reglas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')->constrained('routers')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('tipo', 50)->nullable();
            $table->json('configuracion')->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('exportado')->default(false);
            $table->text('notas')->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->timestamps();
        });

        // audit_logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 100);
            $table->text('description')->nullable();
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('model_label')->nullable();
            $table->string('module', 100)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->unsignedBigInteger('isp_id')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        // api_configs
        Schema::create('api_configs', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->text('token')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // plantillas_whatsapp
        Schema::create('plantillas_whatsapp', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo', 50)->nullable();
            $table->text('mensaje')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tables = [
            'plantillas_whatsapp', 'api_configs', 'audit_logs', 'reglas', 'promesas_pago',
            'comprobante_items', 'comprobantes', 'pagos', 'recibos', 'onus', 'servicios',
            'series_comprobantes', 'planes', 'onu_modelos', 'onu_marcas', 'medios_pago',
            'ubicaciones', 'routers', 'nodos', 'clientes',
        ];
        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
