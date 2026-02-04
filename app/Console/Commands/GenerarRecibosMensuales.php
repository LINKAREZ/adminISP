<?php

namespace App\Console\Commands;

use App\Core\Services\TenantConnectionService;
use App\Jobs\GenerarReciboMensual;
use App\Modules\Sistema\Models\Isp;
use App\Modules\Servicios\Models\Servicio;
use App\Modules\Comprobantes\Services\ReciboService;
use Illuminate\Console\Command;

class GenerarRecibosMensuales extends Command
{
    protected $signature = 'recibos:generar-mensuales
                            {--periodo= : Periodo en formato YYYY-MM (opcional)}
                            {--isp= : ID del ISP (opcional; si no se indica, se procesan todos)}
                            {--sync : Ejecutar síncronamente en lugar de usar queue}';
    protected $description = 'Generar recibos mensuales para todos los servicios activos';

    public function handle(ReciboService $reciboService)
    {
        $this->info('Generando recibos mensuales...');

        $periodo = $this->option('periodo') ?? now()->format('Y-m');
        $sync = $this->option('sync');
        $ispId = $this->option('isp') ? (int) $this->option('isp') : null;

        $isps = $ispId
            ? Isp::on(TenantConnectionService::CENTRAL_CONNECTION)->where('id', $ispId)->whereNotNull('database_name')->get()
            : Isp::on(TenantConnectionService::CENTRAL_CONNECTION)->whereNotNull('database_name')->get();

        if ($isps->isEmpty()) {
            $this->warn('No hay ISPs con BD tenant configurada.');
            return Command::SUCCESS;
        }

        $generadas = 0;
        $errores = 0;
        $total = 0;

        foreach ($isps as $isp) {
            TenantConnectionService::setCurrentIspId($isp->id);
            $servicios = Servicio::where('estado', 'activo')
                ->with(['plan', 'ubicacion.cliente'])
                ->get();
            $total += $servicios->count();

            $bar = $this->output->createProgressBar($servicios->count());
            $bar->setMessage("ISP {$isp->nombre}");
            $bar->start();

            foreach ($servicios as $servicio) {
                try {
                    if ($sync) {
                        $job = new GenerarReciboMensual($servicio, $periodo);
                        $job->handle($reciboService);
                        $generadas++;
                    } else {
                        GenerarReciboMensual::dispatch($servicio, $periodo);
                        $generadas++;
                    }
                } catch (\Exception $e) {
                    $errores++;
                    $this->error("\nError en servicio {$servicio->id}: {$e->getMessage()}");
                }
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
        }

        $this->newLine();
        $this->info("✅ Jobs despachados: {$generadas}");
        $this->warn("❌ Errores: {$errores}");
        $this->info("📊 Total servicios procesados: {$total}");

        if (!$sync) {
            $this->info("💡 Los jobs se están procesando en background. Revisa la queue para ver el progreso.");
        }

        return Command::SUCCESS;
    }
}
