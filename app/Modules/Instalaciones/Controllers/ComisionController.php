<?php

namespace App\Modules\Instalaciones\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Instalaciones\Models\ComisionVendedor;
use App\Modules\Instalaciones\Models\OrdenInstalacion;
use App\Modules\Instalaciones\Services\ComisionService;
use Illuminate\Http\Request;

class ComisionController extends Controller
{
    public function __construct(
        private ComisionService $comisionService
    ) {}

    public function index()
    {
        if (!auth()->user()->hasPermission('instalaciones.read') && !auth()->user()->hasPermission('comprobantes.read')) {
            abort(403);
        }
        $elegibles = OrdenInstalacion::where('estado', OrdenInstalacion::ESTADO_COMPLETADA)
            ->whereNotNull('fecha_completada')
            ->whereNotNull('servicio_id')
            ->whereNotNull('vendedor_id')
            ->with(['cliente', 'plan', 'comisionVendedor'])
            ->orderByDesc('fecha_completada')
            ->get()
            ->filter(fn ($o) => $this->comisionService->elegibleParaComision($o));
        $pendientes = ComisionVendedor::where('estado', ComisionVendedor::ESTADO_PENDIENTE)
            ->with(['ordenInstalacion.cliente', 'ordenInstalacion.plan'])
            ->orderBy('fecha_cumplimiento_3mes')
            ->get();
        return view('instalaciones.comisiones.index', [
            'elegibles' => $elegibles,
            'pendientes' => $pendientes,
            'comisionService' => $this->comisionService,
        ]);
    }

    public function registrar(Request $request)
    {
        if (!auth()->user()->hasPermission('comprobantes.create') && !auth()->user()->hasPermission('instalaciones.update')) {
            abort(403);
        }
        $request->validate([
            'orden_instalacion_id' => 'required|exists:ordenes_instalacion,id',
            'monto' => 'required|numeric|min:0',
        ]);
        $orden = OrdenInstalacion::findOrFail($request->orden_instalacion_id);
        if (!$this->comisionService->elegibleParaComision($orden)) {
            return redirect()->route('instalaciones.comisiones.index')
                ->with('error', 'La orden no es elegible para comisión.');
        }
        if (ComisionVendedor::where('orden_instalacion_id', $orden->id)->exists()) {
            return redirect()->route('instalaciones.comisiones.index')
                ->with('error', 'Ya existe un registro de comisión para esta orden.');
        }
        $fechaCumplimiento = $this->comisionService->fechaCumplimientoTercerMes($orden);
        ComisionVendedor::create([
            'vendedor_id' => $orden->vendedor_id,
            'orden_instalacion_id' => $orden->id,
            'monto' => $request->monto,
            'fecha_cumplimiento_3mes' => $fechaCumplimiento,
            'estado' => ComisionVendedor::ESTADO_PENDIENTE,
            'isp_id' => $orden->isp_id,
        ]);
        return redirect()->route('instalaciones.comisiones.index')
            ->with('success', 'Comisión registrada como pendiente de pago.');
    }

    public function pagar($comision)
    {
        if (!auth()->user()->hasPermission('comprobantes.update') && !auth()->user()->hasPermission('instalaciones.update')) {
            abort(403);
        }
        $comision = ComisionVendedor::findOrFail($comision);
        if ($comision->estado !== ComisionVendedor::ESTADO_PENDIENTE) {
            return redirect()->route('instalaciones.comisiones.index')
                ->with('error', 'La comisión no está pendiente.');
        }
        $comision->update([
            'estado' => ComisionVendedor::ESTADO_PAGADO,
            'fecha_pago' => now()->toDateString(),
        ]);
        return redirect()->route('instalaciones.comisiones.index')
            ->with('success', 'Comisión marcada como pagada.');
    }
}
