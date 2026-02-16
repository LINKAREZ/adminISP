<?php

namespace App\Modules\Infraestructura\Services;

use App\Core\Services\TenantConnectionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Asegura que existan las tablas de infraestructura en el tenant (mufas, cables, recorridos, recorrido_puntos).
 * Usa SQL directo por compatibilidad con el esquema legacy; no reemplaza migraciones tenant.
 */
class InfraestructuraTableEnsurer
{
    public static function ensure(string $connName): void
    {
        $conn = DB::connection($connName);

        try {
            if (Schema::connection($connName)->hasTable('postes')) {
                $conn->unprepared("
                    CREATE TABLE IF NOT EXISTS mufas (
                        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        codigo VARCHAR(255) NULL,
                        latitud DECIMAL(10,8) NULL,
                        longitud DECIMAL(11,8) NULL,
                        poste_id BIGINT UNSIGNED NULL,
                        notas TEXT NULL,
                        estado TINYINT(1) DEFAULT 1,
                        isp_id BIGINT UNSIGNED NULL,
                        created_at TIMESTAMP NULL,
                        updated_at TIMESTAMP NULL,
                        INDEX (poste_id),
                        FOREIGN KEY (poste_id) REFERENCES postes(id) ON DELETE SET NULL
                    )
                ");
            }
        } catch (\Throwable $e) {
            // Ignorar si mufas ya existe o FK falla
        }

        try {
            $conn->unprepared("
                CREATE TABLE IF NOT EXISTS cables (
                    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    tipo_origen VARCHAR(20) NOT NULL,
                    id_origen BIGINT UNSIGNED NOT NULL,
                    tipo_destino VARCHAR(20) NOT NULL,
                    id_destino BIGINT UNSIGNED NOT NULL,
                    nombre VARCHAR(255) NULL,
                    metros INT UNSIGNED NULL,
                    isp_id BIGINT UNSIGNED NULL,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    INDEX (tipo_origen, id_origen),
                    INDEX (tipo_destino, id_destino)
                )
            ");
        } catch (\Throwable $e) {
        }

        try {
            $conn->unprepared("
                CREATE TABLE IF NOT EXISTS recorridos (
                    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    nombre VARCHAR(255) NULL,
                    tipo_cable VARCHAR(100) NULL,
                    marca_cable VARCHAR(100) NULL,
                    anio_fabricacion SMALLINT UNSIGNED NULL,
                    cantidad_buffer INT UNSIGNED NULL,
                    hilos_por_buffer INT UNSIGNED NULL,
                    cantidad_total_hilos INT UNSIGNED NULL,
                    isp_id BIGINT UNSIGNED NULL,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL
                )
            ");
        } catch (\Throwable $e) {
        }

        $recorridoExtras = [
            'tipo_cable' => 'VARCHAR(100) NULL',
            'marca_cable' => 'VARCHAR(100) NULL',
            'anio_fabricacion' => 'SMALLINT UNSIGNED NULL',
            'cantidad_buffer' => 'INT UNSIGNED NULL',
            'hilos_por_buffer' => 'INT UNSIGNED NULL',
            'cantidad_total_hilos' => 'INT UNSIGNED NULL',
        ];
        foreach ($recorridoExtras as $col => $def) {
            try {
                if (Schema::connection($connName)->hasTable('recorridos') && ! Schema::connection($connName)->hasColumn('recorridos', $col)) {
                    $conn->unprepared("ALTER TABLE recorridos ADD COLUMN {$col} {$def}");
                }
            } catch (\Throwable $e) {
            }
        }

        try {
            if (Schema::connection($connName)->hasTable('recorridos')) {
                $conn->unprepared("
                    CREATE TABLE IF NOT EXISTS recorrido_puntos (
                        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        recorrido_id BIGINT UNSIGNED NOT NULL,
                        orden SMALLINT UNSIGNED NOT NULL,
                        tipo VARCHAR(20) NOT NULL,
                        nodo_id BIGINT UNSIGNED NOT NULL,
                        created_at TIMESTAMP NULL,
                        updated_at TIMESTAMP NULL,
                        INDEX (recorrido_id, orden),
                        FOREIGN KEY (recorrido_id) REFERENCES recorridos(id) ON DELETE CASCADE
                    )
                ");
            }
        } catch (\Throwable $e) {
        }
    }
}
