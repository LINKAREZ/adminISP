<?php

namespace App\Core\Services;

use App\Modules\Sistema\Models\Isp;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Crea la base de datos física del tenant y ejecuta las migraciones tenant.
 */
class TenantDatabaseService
{
    /**
     * Genera un nombre de BD único para el ISP (solo caracteres permitidos por MySQL).
     */
    public static function generateDatabaseName(Isp $isp): string
    {
        $base = 'adminisp_isp_' . $isp->id;
        return Str::lower(preg_replace('/[^a-z0-9_]/', '_', $base));
    }

    /**
     * Crea la BD física, asigna database_name al ISP, registra la conexión y ejecuta migraciones tenant.
     *
     * @param bool $runSeeders Si false, no ejecuta seeders (útil al migrar datos desde legacy).
     * @throws \RuntimeException
     */
    public static function createDatabaseForIsp(Isp $isp, bool $runSeeders = true): void
    {
        $isp->refresh(); // por si acaso
        $databaseName = $isp->database_name ?: self::generateDatabaseName($isp);

        // Crear la BD física usando la conexión central (sin seleccionar BD para CREATE DATABASE)
        $connection = DB::connection(TenantConnectionService::CENTRAL_CONNECTION);
        $connection->statement('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', $databaseName) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // Actualizar el ISP con el nombre de la BD si no lo tenía
        if (!$isp->database_name) {
            $isp->update(['database_name' => $databaseName]);
            $isp = $isp->fresh();
        }

        // Registrar la conexión tenant y ejecutar migraciones
        TenantConnectionService::registerConnection($isp);
        $tenantConnection = TenantConnectionService::connectionNameForIsp($isp);
        Config::set('database.default', $tenantConnection);
        Artisan::call('migrate', [
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);
        if ($runSeeders) {
            self::runTenantSeeders();
        }
        Config::set('database.default', TenantConnectionService::CENTRAL_CONNECTION);
    }

    /**
     * Ejecutar seeders que poblan tablas tenant (se llama con la conexión por defecto ya en el tenant).
     */
    protected static function runTenantSeeders(): void
    {
        $classes = [
            'ApiConfigSeeder',
            'OnuMarcaModeloSeeder',
            'SerieComprobanteSeeder',
            'PlantillaWhatsAppSeeder',
            'ReglaSeeder',
        ];
        foreach ($classes as $class) {
            try {
                Artisan::call('db:seed', ['--class' => $class, '--force' => true]);
            } catch (\Throwable $e) {
                // Log pero no fallar la creación del tenant
                report($e);
            }
        }
    }
}
