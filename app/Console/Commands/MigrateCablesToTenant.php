<?php

namespace App\Console\Commands;

use App\Core\Services\TenantConnectionService;
use App\Modules\Sistema\Models\Isp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migra los cables que quedaron en la base central (adminisp) al tenant.
 * Útil cuando los cables se guardaron por error en la BD central antes del fix.
 */
class MigrateCablesToTenant extends Command
{
    protected $signature = 'infraestructura:migrate-cables-to-tenant
                            {--isp= : ID del ISP (si no se indica, migra para todos los ISPs)}';

    protected $description = 'Migra cables desde la BD central al tenant (para recorridos creados antes del fix)';

    public function handle(): int
    {
        $ispId = $this->option('isp');
        $centralConn = TenantConnectionService::CENTRAL_CONNECTION;

        if (! Schema::connection($centralConn)->hasTable('cables')) {
            $this->warn('No existe la tabla cables en la BD central. Nada que migrar.');
            return self::SUCCESS;
        }

        $query = Isp::on($centralConn)->whereNotNull('database_name');
        if ($ispId !== null) {
            $query->where('id', (int) $ispId);
            if ($query->count() === 0) {
                $this->error("ISP con id {$ispId} no encontrado.");
                return self::FAILURE;
            }
        }

        $isps = $query->orderBy('id')->get();
        if ($isps->isEmpty()) {
            $this->warn('No hay ISPs con base de datos tenant.');
            return self::SUCCESS;
        }

        $totalMigrados = 0;

        foreach ($isps as $isp) {
            TenantConnectionService::registerConnection($isp);
            $tenantConn = TenantConnectionService::connectionNameForIsp($isp);

            if (! Schema::connection($tenantConn)->hasTable('cables')) {
                $this->line("  ISP #{$isp->id} ({$isp->nombre}): tabla cables no existe en tenant, omitiendo.");
                continue;
            }

            $cablesCentral = DB::connection($centralConn)
                ->table('cables')
                ->where('isp_id', $isp->id)
                ->get();

            if ($cablesCentral->isEmpty()) {
                $this->line("  ISP #{$isp->id} ({$isp->nombre}): sin cables en central.");
                continue;
            }

            $migrados = 0;
            foreach ($cablesCentral as $cable) {
                $existe = DB::connection($tenantConn)
                    ->table('cables')
                    ->where('tipo_origen', $cable->tipo_origen)
                    ->where('id_origen', $cable->id_origen)
                    ->where('tipo_destino', $cable->tipo_destino)
                    ->where('id_destino', $cable->id_destino)
                    ->exists();

                if (! $existe) {
                    DB::connection($tenantConn)->table('cables')->insert([
                        'tipo_origen' => $cable->tipo_origen,
                        'id_origen' => $cable->id_origen,
                        'tipo_destino' => $cable->tipo_destino,
                        'id_destino' => $cable->id_destino,
                        'nombre' => $cable->nombre,
                        'metros' => $cable->metros,
                        'isp_id' => $cable->isp_id,
                        'created_at' => $cable->created_at,
                        'updated_at' => $cable->updated_at,
                    ]);
                    $migrados++;
                }
            }

            if ($migrados > 0) {
                $this->info("  ISP #{$isp->id} ({$isp->nombre}): {$migrados} cable(s) migrado(s).");
                $totalMigrados += $migrados;
            }
        }

        if ($totalMigrados > 0) {
            $this->info("Total: {$totalMigrados} cable(s) migrado(s) a tenant(s). Recarga el mapa para verlos.");
        } else {
            $cablesSinIsp = DB::connection($centralConn)->table('cables')->whereNull('isp_id')->count();
            if ($cablesSinIsp > 0) {
                $this->warn("Hay {$cablesSinIsp} cable(s) en central con isp_id=null. No se pueden asignar automáticamente a un tenant.");
            } else {
                $this->info('No había cables por migrar (o ya estaban en el tenant).');
            }
        }

        return self::SUCCESS;
    }
}
