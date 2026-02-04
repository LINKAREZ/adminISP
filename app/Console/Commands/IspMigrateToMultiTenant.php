<?php

namespace App\Console\Commands;

use App\Core\Services\TenantConnectionService;
use App\Core\Services\TenantDatabaseService;
use App\Modules\Sistema\Models\Isp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Migra datos desde una BD única (legacy) a la arquitectura multi-tenant.
 *
 * Uso:
 * 1. Crear BD central (ej. adminisp_central) y en .env poner DB_DATABASE=adminisp_central
 * 2. php artisan migrate (solo migraciones centrales)
 * 3. php artisan isp:migrate-to-multi-tenant --source-database=adminisp
 *
 * El comando copia isps, users, roles, permissions desde --source-database a la BD central,
 * luego por cada ISP crea la BD tenant, ejecuta migraciones tenant y copia los datos
 * de las tablas operativas filtradas por isp_id.
 */
class IspMigrateToMultiTenant extends Command
{
    protected $signature = 'isp:migrate-to-multi-tenant
                            {--source-database= : Nombre de la BD actual (legacy) con todos los datos}';
    protected $description = 'Migrar datos de una BD única a multi-tenant (central + BD por ISP)';

    private string $sourceConnection = 'migration_source';
    private string $centralConnection = 'mysql';

    /** Tablas centrales (orden por FK). */
    private array $centralTables = ['isps', 'roles', 'permissions', 'permission_role', 'users'];

    /** Tablas tenant con columna isp_id (orden por FK). */
    private array $tenantTablesWithIspId = [
        'clientes', 'nodos', 'routers', 'ubicaciones', 'medios_pago', 'onu_marcas', 'onu_modelos',
        'planes', 'series_comprobantes', 'servicios', 'onus', 'recibos', 'pagos', 'comprobantes',
        'comprobante_items', 'promesas_pago', 'reglas', 'audit_logs',
    ];

    /** Tablas tenant sin isp_id (se copian todas las filas a cada tenant). */
    private array $tenantTablesWithoutIspId = ['api_configs', 'plantillas_whatsapp'];

    public function handle(): int
    {
        $sourceDb = $this->option('source-database');
        if (empty($sourceDb)) {
            $this->error('Indica la BD de origen: --source-database=nombre_bd');
            return self::FAILURE;
        }

        $centralDb = config('database.connections.mysql.database');
        if ($sourceDb === $centralDb) {
            $this->error('La BD de origen no puede ser la misma que la BD central actual.');
            $this->warn('Crea una BD nueva para central (ej. adminisp_central), ponla en .env y ejecuta migrate.');
            return self::FAILURE;
        }

        $this->registerSourceConnection($sourceDb);
        if (!$this->sourceHasRequiredTables()) {
            return self::FAILURE;
        }

        $this->info('Copiando tablas centrales desde ' . $sourceDb . ' a ' . $centralDb . '...');
        $this->copyCentralTables();
        $this->info('Actualizando database_name en isps...');
        $this->setIspDatabaseNames();

        $isps = Isp::on($this->centralConnection)->get();
        foreach ($isps as $isp) {
            $this->info('Procesando ISP #' . $isp->id . ' (' . $isp->nombre . ')');
            $this->createTenantAndCopyData($isp);
        }

        $this->info('Migración completada.');
        return self::SUCCESS;
    }

    private function registerSourceConnection(string $database): void
    {
        $base = Config::get('database.connections.' . $this->centralConnection, []);
        $config = array_merge($base, ['database' => $database]);
        Config::set('database.connections.' . $this->sourceConnection, $config);
        DB::purge($this->sourceConnection);
    }

    private function sourceHasRequiredTables(): bool
    {
        foreach (array_merge($this->centralTables, $this->tenantTablesWithIspId) as $table) {
            if (!DB::connection($this->sourceConnection)->getSchemaBuilder()->hasTable($table)) {
                $this->warn("La tabla {$table} no existe en la BD de origen. Se omitirá.");
            }
        }
        if (!DB::connection($this->sourceConnection)->getSchemaBuilder()->hasTable('isps')) {
            $this->error('La BD de origen debe tener al menos la tabla isps.');
            return false;
        }
        return true;
    }

    private function copyCentralTables(): void
    {
        foreach ($this->centralTables as $table) {
            if (!DB::connection($this->sourceConnection)->getSchemaBuilder()->hasTable($table)) {
                continue;
            }
            $rows = DB::connection($this->sourceConnection)->table($table)->get();
            if ($rows->isEmpty()) {
                continue;
            }
            $chunks = $rows->chunk(500);
            foreach ($chunks as $chunk) {
                DB::connection($this->centralConnection)->table($table)->insert(
                    $chunk->map(fn ($r) => (array) $r)->toArray()
                );
            }
            $this->line("  {$table}: " . $rows->count() . " filas.");
        }
    }

    private function setIspDatabaseNames(): void
    {
        $isps = DB::connection($this->centralConnection)->table('isps')->get();
        foreach ($isps as $isp) {
            $dbName = 'adminisp_isp_' . $isp->id;
            DB::connection($this->centralConnection)->table('isps')->where('id', $isp->id)->update(['database_name' => $dbName]);
        }
    }

    private function createTenantAndCopyData(Isp $isp): void
    {
        TenantDatabaseService::createDatabaseForIsp($isp, runSeeders: false);
        $isp->refresh();
        $connName = TenantConnectionService::connectionNameForIsp($isp);

        foreach ($this->tenantTablesWithIspId as $table) {
            if (!DB::connection($this->sourceConnection)->getSchemaBuilder()->hasTable($table)) {
                continue;
            }
            $rows = DB::connection($this->sourceConnection)->table($table)->where('isp_id', $isp->id)->get();
            if ($rows->isEmpty()) {
                continue;
            }
            foreach ($rows->chunk(500) as $chunk) {
                $data = $chunk->map(fn ($r) => (array) $r)->toArray();
                DB::connection($connName)->table($table)->insert($data);
            }
            $this->line("  {$table}: " . $rows->count() . " filas.");
        }

        foreach ($this->tenantTablesWithoutIspId as $table) {
            if (!DB::connection($this->sourceConnection)->getSchemaBuilder()->hasTable($table)) {
                continue;
            }
            $rows = DB::connection($this->sourceConnection)->table($table)->get();
            if ($rows->isEmpty()) {
                continue;
            }
            foreach ($rows->chunk(500) as $chunk) {
                $data = $chunk->map(fn ($r) => (array) $r)->toArray();
                DB::connection($connName)->table($table)->insert($data);
            }
            $this->line("  {$table}: " . $rows->count() . " filas.");
        }
    }
}
