<?php

namespace App\Console\Commands;

use App\Core\Services\TenantConnectionService;
use App\Core\Services\TenantDatabaseService;
use App\Modules\Sistema\Models\Isp;
use Illuminate\Console\Command;

class IspCreateDatabase extends Command
{
    protected $signature = 'isp:create-database {isp : ID del ISP} {--force : No pedir confirmación si el ISP ya tiene database_name}';
    protected $description = 'Crea la BD tenant para el ISP y ejecuta las migraciones tenant';

    public function handle(): int
    {
        $id = (int) $this->argument('isp');
        $isp = Isp::on(TenantConnectionService::CENTRAL_CONNECTION)->find($id);

        if (!$isp) {
            $this->error("ISP con id {$id} no encontrado.");
            return self::FAILURE;
        }

        if ($isp->database_name && !$this->option('force')) {
            if (!$this->confirm("El ISP ya tiene database_name '{$isp->database_name}'. ¿Crear BD y migrar de todos modos?")) {
                return self::SUCCESS;
            }
        }

        try {
            TenantDatabaseService::createDatabaseForIsp($isp);
            $this->info("BD tenant creada y migrada para ISP #{$isp->id} ({$isp->nombre}).");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            return self::FAILURE;
        }
    }
}
