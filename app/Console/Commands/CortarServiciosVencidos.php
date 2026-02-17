<?php

namespace App\Console\Commands;

use App\Core\Services\TenantConnectionService;
use App\Core\Traits\NormalizesMacAddress;
use App\Modules\Red\Services\RouterOSScriptService;
use App\Modules\Servicios\Models\Servicio;
use App\Modules\Sistema\Models\Isp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CortarServiciosVencidos extends Command
{
    use NormalizesMacAddress;

    protected $signature = 'servicios:cortar-vencidos
                            {--isp= : ID del ISP (opcional; si no se indica, se procesan todos)}
                            {--dry-run : Solo listar servicios que se cortarían, sin ejecutar}';
    protected $description = 'Cortar servicios activos con recibos pasados de fecha de corte (vencimiento + días de gracia)';

    public function handle(RouterOSScriptService $scriptService): int
    {
        $ispId = $this->option('isp') ? (int) $this->option('isp') : null;
        $dryRun = $this->option('dry-run');

        $isps = $ispId
            ? Isp::on(TenantConnectionService::CENTRAL_CONNECTION)->where('id', $ispId)->whereNotNull('database_name')->get()
            : Isp::on(TenantConnectionService::CENTRAL_CONNECTION)->whereNotNull('database_name')->get();

        if ($isps->isEmpty()) {
            $this->warn('No hay ISPs con BD tenant configurada.');
            return Command::SUCCESS;
        }

        $totalCortados = 0;
        $totalErrores = 0;

        foreach ($isps as $isp) {
            TenantConnectionService::setCurrentIspId($isp->id);

            $servicios = Servicio::where('estado', 'activo')
                ->whereHas('recibos', fn ($q) => $q->pasadosFechaCorte())
                ->with(['router', 'recibos' => fn ($q) => $q->pasadosFechaCorte()])
                ->orderBy('id')
                ->get();

            if ($servicios->isEmpty()) {
                $this->line("ISP {$isp->nombre}: no hay servicios con recibos pasados de fecha de corte.");
                continue;
            }

            if ($dryRun) {
                $this->info("ISP {$isp->nombre}: {$servicios->count()} servicio(s) se cortarían (dry-run).");
                foreach ($servicios as $s) {
                    $this->line("  - Servicio #{$s->id} (MAC: " . ($s->mac_address ?? 'N/A') . ")");
                }
                continue;
            }

            $cortados = 0;
            $errores = 0;

            foreach ($servicios as $servicio) {
                try {
                    $servicio->update(['estado' => 'cortado', 'fecha_corte' => now()->toDateString()]);

                    if ($servicio->router && $servicio->mac_address) {
                        $macNormalizada = $this->normalizarMacAddress($servicio->mac_address);
                        $scriptResult = $scriptService->createCorteScript($servicio->router, $macNormalizada);
                        if ($scriptResult['success']) {
                            $scriptService->createCorteScheduler($servicio->router, $scriptResult['script_name']);
                        } else {
                            Log::warning('Comando cortar-servicios: script de corte falló', [
                                'servicio_id' => $servicio->id,
                                'error' => $scriptResult['message'] ?? 'Error desconocido',
                            ]);
                        }
                    }
                    $cortados++;
                } catch (\Throwable $e) {
                    $errores++;
                    Log::error('Error al cortar servicio desde comando', [
                        'servicio_id' => $servicio->id,
                        'error' => $e->getMessage(),
                    ]);
                    $this->error("  Servicio #{$servicio->id}: {$e->getMessage()}");
                }
            }

            $totalCortados += $cortados;
            $totalErrores += $errores;
            $this->info("ISP {$isp->nombre}: {$cortados} servicio(s) cortados." . ($errores > 0 ? " {$errores} error(es)." : ''));
        }

        $this->info("Total: {$totalCortados} servicio(s) cortados." . ($totalErrores > 0 ? " {$totalErrores} error(es)." : ''));
        return Command::SUCCESS;
    }
}
