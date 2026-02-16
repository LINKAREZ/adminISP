<?php

namespace App\Console\Commands;

use App\Core\Services\TenantConnectionService;
use App\Modules\Sistema\Models\Isp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina todas las bases de datos tenant y deja la central en blanco (migrate:fresh).
 * Uso: empezar de cero (desarrollo o reinstalación).
 */
class IspResetAllDatabases extends Command
{
    protected $signature = 'isp:reset-all-databases
                            {--force : No pedir confirmación}
                            {--skip-tenant-drops : Solo hacer migrate:fresh central, no borrar BDs tenant}';

    protected $description = 'Elimina todas las BDs tenant y resetea la BD central (empezar de 0)';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('¿Eliminar TODAS las bases de datos (tenant + reset central)? No hay vuelta atrás.')) {
            return self::SUCCESS;
        }

        $centralConn = TenantConnectionService::centralConnection();
        $centralDbName = config("database.connections.{$centralConn}.database");
        $prefix = config('tenant.database_prefix', 'adminisp_isp_');

        $tenantDbs = $this->gatherTenantDatabaseNames($centralConn, $centralDbName, $prefix);

        if (! $this->option('skip-tenant-drops') && count($tenantDbs) > 0) {
            $this->info('Eliminando ' . count($tenantDbs) . ' base(s) de datos tenant: ' . implode(', ', $tenantDbs));
            foreach ($tenantDbs as $db) {
                try {
                    $this->dropDatabase($centralConn, $db);
                    $this->line("  — Eliminada: <info>{$db}</info>");
                } catch (\Throwable $e) {
                    $this->warn("  — No se pudo eliminar {$db}: " . $e->getMessage());
                }
            }
        } elseif ($this->option('skip-tenant-drops')) {
            $this->info('Omitiendo eliminación de BDs tenant (--skip-tenant-drops).');
        } else {
            $this->info('No hay BDs tenant que eliminar.');
        }

        $this->info('Reseteando BD central (migrate:fresh)...');
        try {
            $this->call('migrate:fresh', [
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            $this->error('Error en migrate:fresh: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Listo. BD central vacía y migrada. Puedes ejecutar db:seed y crear ISPs de nuevo.');
        return self::SUCCESS;
    }

    /**
     * Recopila nombres de BDs tenant: desde tabla isps y desde SHOW DATABASES con el prefijo.
     */
    private function gatherTenantDatabaseNames(string $centralConn, string $centralDbName, string $prefix): array
    {
        $names = [];

        if (Schema::connection($centralConn)->hasTable('isps')) {
            $fromIsps = DB::connection($centralConn)
                ->table('isps')
                ->whereNotNull('database_name')
                ->where('database_name', '!=', '')
                ->pluck('database_name')
                ->unique()
                ->values()
                ->all();
            $names = array_merge($names, $fromIsps);
        }

        try {
            $databases = DB::connection($centralConn)->select('SHOW DATABASES');
            foreach ($databases as $row) {
                $db = $row->Database ?? $row->database ?? null;
                if ($db && $db !== $centralDbName && str_starts_with($db, $prefix)) {
                    $names[] = $db;
                }
            }
        } catch (\Throwable $e) {
            // SHOW DATABASES puede no estar permitido en algunos entornos
        }

        return array_values(array_unique($names));
    }

    private function dropDatabase(string $connection, string $databaseName): void
    {
        $escaped = '`' . str_replace(['`', '\\'], ['``', '\\\\'], $databaseName) . '`';
        DB::connection($connection)->statement("DROP DATABASE IF EXISTS {$escaped}");
    }
}
