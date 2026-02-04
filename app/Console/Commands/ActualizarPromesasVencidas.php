<?php

namespace App\Console\Commands;

use App\Core\Services\TenantConnectionService;
use App\Jobs\ActualizarPromesaVencida;
use App\Modules\Comprobantes\Models\PromesaPago;
use App\Modules\Sistema\Models\Isp;
use Illuminate\Console\Command;

class ActualizarPromesasVencidas extends Command
{
    protected $signature = 'promesas:actualizar-vencidas
                            {--isp= : ID del ISP (opcional; si no se indica, se procesan todos)}
                            {--sync : Ejecutar síncronamente}';
    protected $description = 'Actualizar estado de promesas de pago vencidas';

    public function handle()
    {
        $sync = $this->option('sync');
        $ispId = $this->option('isp') ? (int) $this->option('isp') : null;

        $isps = $ispId
            ? Isp::on(TenantConnectionService::CENTRAL_CONNECTION)->where('id', $ispId)->whereNotNull('database_name')->get()
            : Isp::on(TenantConnectionService::CENTRAL_CONNECTION)->whereNotNull('database_name')->get();

        if ($isps->isEmpty()) {
            $this->warn('No hay ISPs con BD tenant configurada.');
            return Command::SUCCESS;
        }

        $actualizadas = 0;

        foreach ($isps as $isp) {
            TenantConnectionService::setCurrentIspId($isp->id);
            $promesas = PromesaPago::pendientes()
                ->where('fecha_compromiso', '<', now())
                ->get();

            $bar = $this->output->createProgressBar($promesas->count());
            $bar->setMessage("ISP {$isp->nombre}");
            $bar->start();

            foreach ($promesas as $promesa) {
                try {
                    if ($sync) {
                        $promesa->actualizarEstado();
                    } else {
                        ActualizarPromesaVencida::dispatch($promesa);
                    }
                    $actualizadas++;
                } catch (\Exception $e) {
                    $this->error("\nError en promesa {$promesa->id}: {$e->getMessage()}");
                }
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
        }

        $this->newLine();
        $this->info("✅ Promesas procesadas: {$actualizadas}");

        if (!$sync) {
            $this->info("💡 Los jobs se están procesando en background.");
        }

        return Command::SUCCESS;
    }
}
