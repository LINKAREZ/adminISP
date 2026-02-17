<?php

namespace App\Modules\Infraestructura\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Infraestructura\Models\Olt;
use App\Modules\Infraestructura\Models\OltPuertoPon;
use App\Modules\Infraestructura\Services\DetallePonService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class DetallePonController extends Controller
{
    public function __construct(
        private DetallePonService $detallePonService
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('infraestructura.read');

        $buscarAbonado = $request->input('abonado');
        $oltIdFiltro = $request->input('olt_id');
        $resultadosBusqueda = [];
        $migracionPendiente = false;
        $olts = collect();

        try {
            if ($buscarAbonado !== null && trim($buscarAbonado) !== '') {
                $resultadosBusqueda = $this->detallePonService->buscarPorAbonado(trim($buscarAbonado));
            }

            $puertosPon = OltPuertoPon::with('olt')
                ->when($oltIdFiltro !== null && $oltIdFiltro !== '', fn ($q) => $q->where('olt_id', (int) $oltIdFiltro))
                ->orderBy('olt_id')
                ->orderBy('numero')
                ->get()
                ->groupBy('olt_id');

            $olts = Olt::orderBy('nombre')->get();
        } catch (QueryException $e) {
            if ($this->esTablaFtthFaltante($e)) {
                $migracionPendiente = true;
                $puertosPon = new Collection();
            } else {
                throw $e;
            }
        }

        return view('infraestructura.detalle-pon.index', [
            'puertosPonPorOlt' => $puertosPon,
            'olts' => $olts,
            'oltIdFiltro' => $oltIdFiltro,
            'resultadosBusqueda' => $resultadosBusqueda,
            'abonadoBuscado' => $buscarAbonado,
            'migracionPendiente' => $migracionPendiente,
        ]);
    }

    public function show(int $oltPuertoPon)
    {
        Gate::authorize('infraestructura.read');

        try {
            $model = OltPuertoPon::with('olt')->findOrFail($oltPuertoPon);
        } catch (QueryException $e) {
            if ($this->esTablaFtthFaltante($e)) {
                return redirect()->route('infraestructura.detalle-pon.index')
                    ->with('warning', 'Las tablas de trazabilidad FTTH no están creadas. Ejecute en el servidor: php artisan isp:migrate-tenant');
            }
            throw $e;
        }

        $detalle = $this->detallePonService->detallePorOltPon($model);

        return view('infraestructura.detalle-pon.show', [
            'oltPuertoPon' => $model,
            'detalle' => $detalle,
        ]);
    }

    /**
     * Ejecuta las migraciones tenant FTTH para el ISP del usuario desde el panel (sin SSH).
     */
    public function migrarFtth(Request $request)
    {
        Gate::authorize('infraestructura.update');
        $ispId = session('current_isp_id') ?? auth()->user()->isp_id;
        if (!$ispId) {
            return redirect()->route('infraestructura.mapa.index')
                ->with('error', 'No hay ISP asignado. Use el comando en servidor: php artisan isp:migrate-tenant');
        }
        $ispId = (int) $ispId;
        try {
            $exitCode = Artisan::call('isp:migrate-tenant', ['--isp' => $ispId]);
            $output = trim(Artisan::output());
            if ($exitCode !== 0) {
                return redirect()->route('infraestructura.mapa.index')
                    ->with('error', 'Error al ejecutar migraciones: ' . ($output ?: 'consulte los logs.'))
                    ->with('warning', null);
            }

            // Verificar que las tablas existan en la BD del tenant (y purgar conexión para la siguiente petición)
            $connName = \App\Core\Services\TenantConnectionService::connectionNameForId($ispId);
            \App\Core\Services\TenantConnectionService::registerConnectionForIspId($ispId);
            DB::purge($connName);
            \App\Core\Services\TenantConnectionService::registerConnectionForIspId($ispId);

            if (! Schema::connection($connName)->hasTable('olts')) {
                return redirect()->route('infraestructura.mapa.index')
                    ->with('error', 'La migración terminó pero la tabla "olts" no existe en la BD del ISP. Compruebe que el ISP tenga database_name correcto y ejecute en el servidor: php artisan isp:migrate-tenant --isp=' . $ispId)
                    ->with('warning', null);
            }

            session(['current_isp_id' => $ispId]);

            return redirect()->route('infraestructura.mapa.index')
                ->with('success', 'Tablas FTTH creadas correctamente. Ya puede usar Detalle PON, OLTs/ODFs y crear enlaces.')
                ->with('warning', null);
        } catch (\Throwable $e) {
            return redirect()->route('infraestructura.mapa.index')
                ->with('error', 'Error: ' . $e->getMessage())
                ->with('warning', null);
        }
    }

    private function esTablaFtthFaltante(QueryException $e): bool
    {
        $message = $e->getMessage();
        return str_contains($message, "Base table or view not found")
            && (
                str_contains($message, 'olt_puertos_pon')
                || str_contains($message, 'olts')
                || str_contains($message, 'odfs')
                || str_contains($message, 'enlace_olt_odf')
                || str_contains($message, 'recorrido_hilo_origen')
                || str_contains($message, 'splitters')
            );
    }
}
