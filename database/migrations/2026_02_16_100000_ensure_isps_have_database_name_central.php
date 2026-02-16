<?php

use App\Core\Services\TenantConnectionService;
use App\Core\Services\TenantDatabaseService;
use App\Modules\Sistema\Models\Isp;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Garantiza que todo ISP tenga database_name:
 * 1) Crea la BD tenant para cada ISP que no la tenga.
 * 2) Hace la columna database_name NOT NULL.
 */
return new class extends Migration
{
    protected $connection = 'mysql';

    public function up(): void
    {
        $conn = $this->connection;
        $ispsSinDb = Isp::on($conn)->withoutGlobalScope(\App\Core\Scopes\IspScope::class)
            ->where(function ($q) {
                $q->whereNull('database_name')->orWhere('database_name', '');
            })
            ->orderBy('id')
            ->get();

        foreach ($ispsSinDb as $isp) {
            try {
                TenantDatabaseService::createDatabaseForIsp($isp, true);
            } catch (\Throwable $e) {
                // Si falla (ej. permisos), al menos asignar nombre para que exista el valor
                $name = TenantDatabaseService::generateDatabaseName($isp);
                $isp->update(['database_name' => $name]);
                try {
                    $central = TenantConnectionService::centralConnection();
                    DB::connection($central)->statement(
                        'CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', $name) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
                    );
                } catch (\Throwable $e2) {
                    // Continuar; el admin puede ejecutar isp:migrate-tenant después
                }
            }
        }

        if (Schema::connection($conn)->hasColumn('isps', 'database_name')) {
            DB::connection($conn)->statement(
                'ALTER TABLE isps MODIFY COLUMN database_name VARCHAR(64) NOT NULL COMMENT \'Nombre de la BD tenant para este ISP\''
            );
        }
    }

    public function down(): void
    {
        if (Schema::connection($this->connection)->hasColumn('isps', 'database_name')) {
            DB::connection($this->connection)->statement(
                'ALTER TABLE isps MODIFY COLUMN database_name VARCHAR(64) NULL COMMENT \'Nombre de la BD tenant para este ISP\''
            );
        }
    }
};
