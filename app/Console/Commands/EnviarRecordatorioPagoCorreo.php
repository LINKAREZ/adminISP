<?php

namespace App\Console\Commands;

use App\Core\Services\TenantConnectionService;
use App\Mail\RecordatorioPagoMail;
use App\Modules\Comprobantes\Models\Recibo;
use App\Modules\Sistema\Models\Isp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnviarRecordatorioPagoCorreo extends Command
{
    protected $signature = 'recordatorio:enviar-correo
                            {--isp= : ID del ISP (opcional)}
                            {--dias= : Días antes del vencimiento (opcional, usa config por defecto)}';
    protected $description = 'Envía recordatorio por correo a clientes con recibo por vencer';

    public function handle(): int
    {
        if (! config('isp.recordatorio_pago.enabled', true)) {
            $this->warn('Recordatorio por correo está deshabilitado en config.');
            return Command::SUCCESS;
        }

        $diasAntes = (int) ($this->option('dias') ?? config('isp.recordatorio_pago.dias_antes', 3));
        $fechaObjetivo = now()->addDays($diasAntes)->toDateString();

        $ispId = $this->option('isp') ? (int) $this->option('isp') : null;
        $isps = $ispId
            ? Isp::on(TenantConnectionService::CENTRAL_CONNECTION)->where('id', $ispId)->whereNotNull('database_name')->get()
            : Isp::on(TenantConnectionService::CENTRAL_CONNECTION)->whereNotNull('database_name')->get();

        if ($isps->isEmpty()) {
            $this->warn('No hay ISPs con BD tenant.');
            return Command::SUCCESS;
        }

        $enviados = 0;
        $sinEmail = 0;
        $errores = 0;

        foreach ($isps as $isp) {
            TenantConnectionService::setCurrentIspId($isp->id);

            $recibos = Recibo::with(['cliente.credencialPortal'])
                ->where('estado', Recibo::ESTADO_PENDIENTE)
                ->whereDate('fecha_vencimiento', $fechaObjetivo)
                ->get();

            foreach ($recibos as $recibo) {
                $email = $recibo->cliente?->credencialPortal?->email;
                if (empty($email)) {
                    $sinEmail++;
                    continue;
                }

                try {
                    Mail::to($email)->send(new RecordatorioPagoMail($recibo));
                    $enviados++;
                } catch (\Throwable $e) {
                    $errores++;
                    $this->error("Error enviando a {$email} (recibo {$recibo->codigo}): " . $e->getMessage());
                }
            }
        }

        $this->info("Recordatorios enviados: {$enviados}. Sin email: {$sinEmail}. Errores: {$errores}.");
        return Command::SUCCESS;
    }
}
