<?php

namespace App\Modules\Sistema\Services;

use App\Core\Services\TenantConnectionService;
use App\Modules\Sistema\Models\Isp;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

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
     * Escapa nombre para SQL (backticks).
     */
    protected function escapeName(string $name): string
    {
        return '`' . str_replace('`', '``', $name) . '`';
    }

    /**
     * Convierte un valor de fila a literal SQL para INSERT.
     */
    protected function valueToSql($value, \PDO $pdo): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if ($value instanceof \DateTimeInterface) {
            return $pdo->quote($value->format('Y-m-d H:i:s'));
        }
        return $pdo->quote((string) $value);
    }

    /**
     * Exportar datos del ISP desde su BD tenant (multi-tenant).
     * Genera SQL con tablas cualificadas (`bd`.`tabla`) para importación correcta.
     */
    public function exportToSql(Isp $isp): string
    {
        $centralConnection = TenantConnectionService::CENTRAL_CONNECTION;
        $sql = "-- Exportación de datos del ISP: {$isp->nombre}\n";
        $sql .= "-- Fecha: " . now()->format('Y-m-d H:i:s') . "\n";
        $sql .= "-- ID ISP: {$isp->id}\n";
        $sql .= "-- BD tenant: " . ($isp->database_name ?? 'no asignada') . "\n\n";

        if (!$isp->database_name) {
            $sql .= "-- Este ISP no tiene base de datos tenant. No hay datos operativos que exportar.\n";
            return $sql;
        }

        TenantConnectionService::setCurrentIspId($isp->id);
        $connName = TenantConnectionService::connectionNameForIsp($isp);
        $conn = DB::connection($connName);
        $tenantDb = $isp->database_name;
        $pdo = $conn->getPdo();

        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($this->tenantTables() as $table) {
            if (!$conn->getSchemaBuilder()->hasTable($table)) {
                continue;
            }

            $rows = $conn->table($table)->get();
            if ($rows->isEmpty()) {
                continue;
            }

            $qualifiedTable = $this->escapeName($tenantDb) . '.' . $this->escapeName($table);
            $columns = array_keys((array) $rows->first());
            $columnsEscaped = array_map([$this, 'escapeName'], $columns);

            $sql .= "\n-- Tabla: {$table} (" . count($rows) . " filas)\n";
            $sql .= "TRUNCATE TABLE {$qualifiedTable};\n\n";

            foreach ($rows as $row) {
                $values = array_map(function ($v) use ($pdo) {
                    return $this->valueToSql($v, $pdo);
                }, array_values((array) $row));
                $sql .= "INSERT INTO {$qualifiedTable} (" . implode(', ', $columnsEscaped) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        Config::set('database.default', $centralConnection);

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

        $conn = DB::connection($connection);
        foreach ($this->tenantTables() as $table) {
            if (!$conn->getSchemaBuilder()->hasTable($table)) {
                continue;
            }
            $rows = $conn->table($table)->get();
            $data['tables'][$table] = $rows->map(fn ($row) => (array) $row)->toArray();
        }

        Config::set('database.default', TenantConnectionService::CENTRAL_CONNECTION);

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
