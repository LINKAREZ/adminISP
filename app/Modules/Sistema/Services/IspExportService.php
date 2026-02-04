<?php

namespace App\Modules\Sistema\Services;

use App\Core\Services\TenantConnectionService;
use App\Modules\Sistema\Models\Isp;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IspExportService
{
    /**
     * Tablas de la BD tenant en orden (respetar FKs para SQL).
     */
    protected function tenantTables(): array
    {
        return [
            'clientes', 'nodos', 'routers', 'ubicaciones', 'medios_pago',
            'onu_marcas', 'onu_modelos', 'planes', 'series_comprobantes',
            'servicios', 'onus', 'recibos', 'pagos', 'comprobantes',
            'comprobante_items', 'promesas_pago', 'reglas', 'audit_logs',
            'api_configs', 'plantillas_whatsapp',
        ];
    }

    /**
     * Exportar datos del ISP desde su BD tenant (multi-tenant).
     * Si el ISP no tiene database_name, devuelve export vacío o solo datos del ISP desde central.
     */
    public function exportToSql(Isp $isp): string
    {
        $ispId = $isp->id;
        $sql = "-- Exportación de datos del ISP: {$isp->nombre}\n";
        $sql .= "-- Fecha: " . now()->format('Y-m-d H:i:s') . "\n";
        $sql .= "-- ID ISP: {$ispId}\n";
        $sql .= "-- BD tenant: " . ($isp->database_name ?? 'no asignada') . "\n\n";

        if (!$isp->database_name) {
            $sql .= "-- Este ISP no tiene base de datos tenant. No hay datos operativos que exportar.\n";
            return $sql;
        }

        TenantConnectionService::setCurrentIspId($ispId);
        $connection = TenantConnectionService::connectionNameForIsp($isp);
        Config::set('database.default', $connection);
        $tenantDb = $isp->database_name;

        $sql .= "USE `" . str_replace('`', '``', $tenantDb) . "`;\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($this->tenantTables() as $table) {
            if (!Schema::connection($connection)->hasTable($table)) {
                continue;
            }

            $rows = DB::connection($connection)->table($table)->get();
            if ($rows->isEmpty()) {
                continue;
            }

            $columns = array_keys((array) $rows->first());
            $sql .= "\n-- Tabla: {$table}\n";
            $sql .= "TRUNCATE TABLE `" . str_replace('`', '``', $table) . "`;\n\n";

            foreach ($rows as $row) {
                $values = array_map(function ($value) use ($connection) {
                    if ($value === null) {
                        return 'NULL';
                    }
                    if (is_bool($value)) {
                        return $value ? '1' : '0';
                    }
                    return DB::connection($connection)->getPdo()->quote($value);
                }, array_values((array) $row));

                $sql .= "INSERT INTO `" . str_replace('`', '``', $table) . "` (`" . implode('`, `', array_map(function ($c) {
                    return str_replace('`', '``', $c);
                }, $columns)) . "`) VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        Config::set('database.default', TenantConnectionService::CENTRAL_CONNECTION);

        return $sql;
    }

    /**
     * Exportar datos del ISP a JSON desde su BD tenant.
     */
    public function exportToJson(Isp $isp): string
    {
        $data = [
            'isp' => $isp->only(['id', 'nombre', 'activo', 'moneda', 'simbolo_moneda', 'igv', 'database_name']),
            'exported_at' => now()->toIso8601String(),
            'tables' => [],
        ];

        if (!$isp->database_name) {
            $data['notice'] = 'Este ISP no tiene base de datos tenant. No hay datos operativos.';
            return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        TenantConnectionService::setCurrentIspId($isp->id);
        $connection = TenantConnectionService::connectionNameForIsp($isp);

        foreach ($this->tenantTables() as $table) {
            if (!Schema::connection($connection)->hasTable($table)) {
                continue;
            }
            $rows = DB::connection($connection)->table($table)->get();
            $data['tables'][$table] = $rows->map(fn ($row) => (array) $row)->toArray();
        }

        Config::set('database.default', TenantConnectionService::CENTRAL_CONNECTION);

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
