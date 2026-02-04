<?php

namespace App\Modules\Sistema\Services;

use App\Modules\Sistema\Models\Isp;
use Illuminate\Support\Facades\DB;

class IspExportService
{
    /**
     * Tablas y condiciones para exportación SQL (orden de dependencias).
     * Retorna [ 'tabla' => ['col' => valor], ... ] con isp_id ya asignado.
     */
    protected function sqlTables(int $ispId): array
    {
        return [
            'isps' => ['id' => $ispId],
            'users' => ['isp_id' => $ispId],
            'roles' => ['isp_id' => $ispId],
            'permissions' => ['isp_id' => $ispId],
            'nodos' => ['isp_id' => $ispId],
            'routers' => ['isp_id' => $ispId],
            'planes' => ['isp_id' => $ispId],
            'clientes' => ['isp_id' => $ispId],
            'ubicaciones' => ['isp_id' => $ispId],
            'servicios' => ['isp_id' => $ispId],
            'recibos' => ['isp_id' => $ispId],
            'pagos' => ['isp_id' => $ispId],
            'promesas_pago' => ['isp_id' => $ispId],
            'comprobantes' => ['isp_id' => $ispId],
            'comprobante_items' => ['isp_id' => $ispId],
            'series_comprobantes' => ['isp_id' => $ispId],
            'onus' => ['isp_id' => $ispId],
            'onu_marcas' => ['isp_id' => $ispId],
            'onu_modelos' => ['isp_id' => $ispId],
            'medios_pago' => ['isp_id' => $ispId],
            'reglas' => ['isp_id' => $ispId],
            'audit_logs' => ['isp_id' => $ispId],
        ];
    }

    /**
     * Tablas para exportación JSON.
     */
    protected function jsonTables(): array
    {
        return [
            'users', 'roles', 'permissions', 'nodos', 'routers',
            'planes', 'clientes', 'ubicaciones', 'servicios',
            'recibos', 'pagos', 'promesas_pago', 'comprobantes',
            'comprobante_items', 'series_comprobantes', 'onus',
            'onu_marcas', 'onu_modelos', 'medios_pago', 'reglas',
            'audit_logs',
        ];
    }

    /**
     * Exportar ISP a SQL (string).
     */
    public function exportToSql(Isp $isp): string
    {
        $ispId = $isp->id;
        $tables = $this->sqlTables($ispId);

        $sql = "-- Exportación de datos del ISP: {$isp->nombre}\n";
        $sql .= "-- Fecha: " . now()->format('Y-m-d H:i:s') . "\n";
        $sql .= "-- ID ISP: {$ispId}\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table => $conditions) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }

            $query = DB::table($table);
            foreach ($conditions as $column => $value) {
                $query->where($column, $value);
            }

            $rows = $query->get();

            if ($rows->isEmpty()) {
                continue;
            }

            $columns = array_keys((array) $rows->first());

            $sql .= "\n-- Tabla: {$table}\n";
            $sql .= "DELETE FROM `{$table}` WHERE ";

            $whereConditions = [];
            foreach ($conditions as $column => $value) {
                $whereConditions[] = "`{$column}` = " . DB::getPdo()->quote($value);
            }
            $sql .= implode(' AND ', $whereConditions) . ";\n\n";

            foreach ($rows as $row) {
                $values = array_map(function ($value) {
                    if ($value === null) {
                        return 'NULL';
                    }
                    if (is_bool($value)) {
                        return $value ? '1' : '0';
                    }
                    return DB::getPdo()->quote($value);
                }, array_values((array) $row));

                $sql .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
            }

            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }

    /**
     * Exportar ISP a JSON (string).
     */
    public function exportToJson(Isp $isp): string
    {
        $ispId = $isp->id;

        $data = [
            'isp' => $isp->toArray(),
            'exported_at' => now()->toIso8601String(),
            'tables' => [],
        ];

        foreach ($this->jsonTables() as $table) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }
            if (!DB::getSchemaBuilder()->hasColumn($table, 'isp_id')) {
                continue;
            }

            $rows = DB::table($table)->where('isp_id', $ispId)->get();

            $data['tables'][$table] = $rows->map(fn ($row) => (array) $row)->toArray();
        }

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
