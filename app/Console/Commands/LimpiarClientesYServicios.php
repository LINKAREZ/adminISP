<?php

namespace App\Console\Commands;

use App\Core\Services\TenantConnectionService;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\Comprobantes\Models\Comprobante;
use App\Modules\Comprobantes\Models\ComprobanteItem;
use App\Modules\Comprobantes\Models\Pago;
use App\Modules\Comprobantes\Models\PromesaPago;
use App\Modules\Comprobantes\Models\Recibo;
use App\Modules\Servicios\Models\Onu;
use App\Modules\Servicios\Models\Servicio;
use App\Modules\Clientes\Models\Ubicacion;
use App\Modules\Sistema\Models\Isp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Borra todos los clientes y servicios del tenant.
 * Orden: primero dependientes de recibos/servicios (comprobante_items, comprobantes, pagos, promesas, recibos, onus),
 * luego servicios, ubicaciones y clientes.
 */
class LimpiarClientesYServicios extends Command
{
    protected $signature = 'tenant:limpiar-clientes-servicios
                            {--isp= : ID del ISP}
                            {--nombre= : Nombre del ISP (ej. WAN). Si se indica, solo se aplica a ese ISP}
                            {--force : No pedir confirmación}';

    protected $description = 'Borra todos los servicios y clientes del tenant (primero servicios y sus dependientes, luego clientes)';

    public function handle(): int
    {
        $ispId = $this->option('isp') ? (int) $this->option('isp') : null;
        $nombre = $this->option('nombre');
        $force = $this->option('force');

        $query = Isp::on(TenantConnectionService::CENTRAL_CONNECTION)->whereNotNull('database_name');
        if ($nombre !== null && $nombre !== '') {
            $query->where('nombre', 'like', '%' . trim($nombre) . '%');
        } elseif ($ispId !== null) {
            $query->where('id', $ispId);
        }
        $isps = $query->get();

        if ($isps->isEmpty()) {
            if ($nombre !== null && $nombre !== '') {
                $this->error("No se encontró ningún ISP con nombre «{$nombre}» (con BD tenant configurada).");
            } else {
                $this->error('No hay ISPs con BD tenant configurada.');
            }
            return Command::FAILURE;
        }

        $centralConnection = TenantConnectionService::CENTRAL_CONNECTION;

        foreach ($isps as $isp) {
            if (!$force && !$this->confirm("¿Borrar TODOS los clientes y servicios del ISP «{$isp->nombre}»? Esta acción no se puede deshacer.", false)) {
                $this->info('Operación cancelada.');
                continue;
            }

            TenantConnectionService::setCurrentIspId($isp->id);
            Config::set('database.default', TenantConnectionService::connectionNameForIsp($isp));

            try {
            DB::transaction(function () use ($isp) {
                $this->info("Limpiando tenant: {$isp->nombre}");

                // Orden: dependientes de comprobantes/recibos/servicios, luego servicios, ubicaciones, clientes
                $countItems = ComprobanteItem::query()->count();
                ComprobanteItem::query()->delete();
                $this->line("  - comprobante_items: {$countItems} eliminados.");

                $countComp = Comprobante::query()->count();
                Comprobante::query()->delete();
                $this->line("  - comprobantes: {$countComp} eliminados.");

                $countPagos = Pago::query()->count();
                Pago::query()->delete();
                $this->line("  - pagos: {$countPagos} eliminados.");

                $countPromesas = PromesaPago::query()->count();
                PromesaPago::query()->delete();
                $this->line("  - promesas_pago: {$countPromesas} eliminados.");

                $countRecibos = Recibo::query()->count();
                Recibo::query()->delete();
                $this->line("  - recibos: {$countRecibos} eliminados.");

                $countOnus = Onu::query()->count();
                Onu::query()->delete();
                $this->line("  - onus: {$countOnus} eliminados.");

                $countServicios = Servicio::query()->count();
                Servicio::query()->delete();
                $this->line("  - servicios: {$countServicios} eliminados.");

                $countUbicaciones = Ubicacion::query()->count();
                Ubicacion::query()->delete();
                $this->line("  - ubicaciones: {$countUbicaciones} eliminados.");

                $countClientes = Cliente::query()->count();
                Cliente::query()->delete();
                $this->line("  - clientes: {$countClientes} eliminados.");
            });
            } catch (\Throwable $e) {
                Config::set('database.default', $centralConnection);
                $this->error("Error en ISP «{$isp->nombre}»: " . $e->getMessage());
                return Command::FAILURE;
            }

            Cache::forget('dashboard_stats_' . $isp->id);
            Config::set('database.default', $centralConnection);
            $this->info("ISP «{$isp->nombre}»: clientes y servicios eliminados.");
        }

        return Command::SUCCESS;
    }
}
