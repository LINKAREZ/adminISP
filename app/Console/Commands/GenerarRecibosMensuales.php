<?php

namespace App\Console\Commands;

use App\Jobs\GenerarReciboMensual;
use App\Modules\Servicios\Models\Servicio;
use App\Modules\Comprobantes\Services\ReciboService;
use Illuminate\Console\Command;

class GenerarRecibosMensuales extends Command
{
    protected $signature = 'recibos:generar-mensuales
                            {--periodo= : Periodo en formato YYYY-MM (opcional)}
                            {--sync : Ejecutar síncronamente en lugar de usar queue}';
    protected $description = 'Generar recibos mensuales para todos los servicios activos';

    public function handle(ReciboService $reciboService)
    {
        $this->info('Generando recibos mensuales...');

        $periodo = $this->option('periodo') ?? now()->format('Y-m');
        $sync = $this->option('sync');

        $servicios = \App\Modules\Servicios\Models\Servicio::where('estado', 'activo')
            ->with(['plan', 'ubicacion.cliente'])
            ->get();

        $total = $servicios->count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $generadas = 0;
        $existentes = 0;
        $errores = 0;

        foreach ($servicios as $servicio) {
            try {
                if ($sync) {
                    // Ejecutar síncronamente
                    $job = new GenerarReciboMensual($servicio, $periodo);
                    $job->handle($reciboService);
                    $generadas++;
                } else {
                    // Despachar a queue
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
        $this->newLine(2);

        $this->info("✅ Jobs despachados: {$generadas}");
        $this->warn("❌ Errores: {$errores}");
        $this->info("📊 Total servicios procesados: {$total}");

        if (!$sync) {
            $this->info("💡 Los jobs se están procesando en background. Revisa la queue para ver el progreso.");
        }

        return Command::SUCCESS;
    }
}
