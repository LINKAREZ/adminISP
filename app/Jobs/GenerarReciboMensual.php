<?php

namespace App\Jobs;

use App\Core\Services\TenantConnectionService;
use App\Modules\Servicios\Models\Servicio;
use App\Modules\Comprobantes\Models\Recibo;
use App\Modules\Comprobantes\Services\ReciboService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerarReciboMensual implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Servicio $servicio,
        public string $periodo
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ReciboService $reciboService): void
    {
        if ($this->servicio->isp_id) {
            TenantConnectionService::setCurrentIspId((int) $this->servicio->isp_id);
        }
        try {
            $reciboService->generarReciboMensual($this->servicio, $this->periodo);
        } catch (\Exception $e) {
            Log::error('Error al generar recibo mensual', [
                'servicio_id' => $this->servicio->id,
                'periodo' => $this->periodo,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
