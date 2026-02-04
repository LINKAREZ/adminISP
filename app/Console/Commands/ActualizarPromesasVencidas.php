<?php

namespace App\Console\Commands;

use App\Jobs\ActualizarPromesaVencida;
use App\Modules\Comprobantes\Models\PromesaPago;
use Illuminate\Console\Command;

class ActualizarPromesasVencidas extends Command
{
    protected $signature = 'promesas:actualizar-vencidas {--sync : Ejecutar síncronamente}';
    protected $description = 'Actualizar estado de promesas de pago vencidas';

    public function handle()
    {
        $promesas = \App\Modules\Comprobantes\Models\PromesaPago::pendientes()
            ->where('fecha_compromiso', '<', now())
            ->get();

        $sync = $this->option('sync');
        $total = $promesas->count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $actualizadas = 0;

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
        $this->newLine(2);

        $this->info("✅ Promesas procesadas: {$actualizadas}");

        if (!$sync) {
            $this->info("💡 Los jobs se están procesando en background.");
        }

        return Command::SUCCESS;
    }
}
