<?php

namespace App\Console\Commands;

use App\Modules\Comprobantes\Models\Pago;
use App\Modules\Comprobantes\Models\Comprobante;
use Illuminate\Console\Command;

class GenerarComprobantesRetroactivos extends Command
{
    protected $signature = 'comprobantes:generar-retroactivos
                            {--limit= : Límite de pagos a procesar}
                            {--desde= : Fecha desde (formato: YYYY-MM-DD)}
                            {--hasta= : Fecha hasta (formato: YYYY-MM-DD)}';

    protected $description = 'Generar comprobantes para pagos existentes que no tienen comprobante';

    public function handle()
    {
        $this->info('🔍 Buscando pagos sin comprobante...');

        $query = Pago::whereDoesntHave('comprobante')
            ->with('cliente');

        // Filtros opcionales
        if ($this->option('desde')) {
            $query->where('fecha_pago', '>=', $this->option('desde'));
        }

        if ($this->option('hasta')) {
            $query->where('fecha_pago', '<=', $this->option('hasta'));
        }

        $pagos = $query->orderBy('fecha_pago', 'asc')->get();

        if ($this->option('limit')) {
            $pagos = $pagos->take((int) $this->option('limit'));
        }

        $total = $pagos->count();

        if ($total === 0) {
            $this->info('✅ No hay pagos sin comprobante.');
            return 0;
        }

        $this->info("📊 Encontrados {$total} pagos sin comprobante.");

        if (!$this->confirm("¿Deseas generar comprobantes para estos {$total} pagos?")) {
            $this->info('❌ Operación cancelada.');
            return 0;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $generados = 0;
        $errores = 0;

        foreach ($pagos as $pago) {
            try {
                // Cargar cliente si no está cargado
                if (!$pago->relationLoaded('cliente')) {
                    $pago->load('cliente');
                }

                // Solo generar recibos (documentos internos)
                $tipoComprobante = Comprobante::TIPO_RECIBO;

                // Obtener serie (por defecto R001)
                $serie = 'R001';

                // Obtener siguiente número
                $numero = Comprobante::obtenerSiguienteNumero($tipoComprobante, $serie);

                Comprobante::create([
                    'pago_id' => $pago->id,
                    'cliente_id' => $pago->cliente_id,
                    'tipo' => $tipoComprobante,
                    'serie' => $serie,
                    'numero' => $numero,
                    'numero_completo' => "{$serie}-{$numero}",
                    'fecha_emision' => $pago->fecha_pago ?? now(),
                    'monto' => $pago->monto,
                    'estado' => 'vigente',
                    'generado_por' => 1, // Usuario del sistema
                ]);

                $generados++;
            } catch (\Exception $e) {
                $errores++;
                $this->newLine();
                $this->error("❌ Error en pago ID {$pago->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Comprobantes generados: {$generados}");
        if ($errores > 0) {
            $this->warn("❌ Errores: {$errores}");
        }

        return 0;
    }
}
