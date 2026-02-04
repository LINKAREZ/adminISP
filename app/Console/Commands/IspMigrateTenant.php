<?php

namespace App\Console\Commands;

use App\Core\Services\TenantConnectionService;
use App\Modules\Sistema\Models\Isp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

/**
 * Ejecuta las migraciones tenant en la(s) base(s) de datos de los ISPs.
 * Úsalo cuando agregues o modifiques migraciones en database/migrations/tenant/
 * (por ejemplo, nuevos campos o tablas en la BD de cada ISP).
 */
class IspMigrateTenant extends Command
{
    protected $signature = 'isp:migrate-tenant
                            {--isp= : ID del ISP (si no se indica, se aplica a todos los ISPs con BD)}';
    protected $description = 'Ejecuta las migraciones tenant en la(s) BD de los ISPs (añadir/quitar campos, etc.)';

    public function handle(): int
    {
        $ispId = $this->option('isp');
        $query = Isp::on(TenantConnectionService::CENTRAL_CONNECTION)
            ->whereNotNull('database_name');

        if ($ispId !== null) {
            $query->where('id', (int) $ispId);
            if ($query->count() === 0) {
                $this->error("ISP con id {$ispId} no encontrado o sin database_name.");
                return self::FAILURE;
            }
        }

        $isps = $query->orderBy('id')->get();
        if ($isps->isEmpty()) {
            $this->warn('No hay ISPs con base de datos tenant. Crea un ISP desde el panel o usa isp:create-database {id}.');
            return self::SUCCESS;
        }

        $this->info('Ejecutando migraciones tenant en ' . $isps->count() . ' ISP(s)...');
        $centralConnection = TenantConnectionService::CENTRAL_CONNECTION;

        foreach ($isps as $isp) {
            $this->line("  ISP #{$isp->id} ({$isp->nombre}) — {$isp->database_name}");
            TenantConnectionService::setCurrentIspId($isp->id);
            Config::set('database.default', TenantConnectionService::connectionNameForIsp($isp));
            try {
                Artisan::call('migrate', [
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ], $this->getOutput());
            } catch (\Throwable $e) {
                $this->error("  Error en ISP #{$isp->id}: " . $e->getMessage());
                Config::set('database.default', $centralConnection);
                return self::FAILURE;
            }
        }

        Config::set('database.default', $centralConnection);
        $this->info('Migraciones tenant aplicadas correctamente.');
        return self::SUCCESS;
    }
}
