<?php

namespace App\Modules\Comprobantes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Comprobantes\Models\Pago;
use App\Modules\Sistema\Models\MedioPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;

class ReporteController extends Controller
{
    /**
     * Mostrar reporte de cuadre de caja (pagos por método de pago)
     */
    public function cuadreCaja(Request $request)
    {
        Gate::authorize('comprobantes.read');
        // Obtener fechas del request o usar fecha actual
        $fechaInicio = $request->input('fecha_inicio', Carbon::today()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::today()->format('Y-m-d'));

        // Convertir a Carbon para comparaciones
        $fechaInicioCarbon = Carbon::parse($fechaInicio)->startOfDay();
        $fechaFinCarbon = Carbon::parse($fechaFin)->endOfDay();

        // Obtener todos los medios de pago activos
        $mediosPago = MedioPago::where('activo', true)
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get();

        // Obtener pagos agrupados por medio de pago
        $pagosPorMedio = Pago::select(
            'medio_pago_id',
            'medio_pago',
            DB::raw('COUNT(*) as cantidad'),
            DB::raw('SUM(monto) as total')
        )
            ->whereBetween('fecha_pago', [$fechaInicioCarbon, $fechaFinCarbon])
            ->groupBy('medio_pago_id', 'medio_pago')
            ->get()
            ->keyBy(function ($item) {
                return $item->medio_pago_id ?? 'legacy_' . $item->medio_pago;
            });

        // Obtener total general
        $totalGeneral = Pago::whereBetween('fecha_pago', [$fechaInicioCarbon, $fechaFinCarbon])
            ->sum('monto');

        $cantidadTotal = Pago::whereBetween('fecha_pago', [$fechaInicioCarbon, $fechaFinCarbon])
            ->count();

        // Preparar datos para la vista
        $datosReporte = [];
        foreach ($mediosPago as $medio) {
            $key = $medio->id;
            $pagoData = $pagosPorMedio->get($key);

            $datosReporte[] = [
                'medio_pago' => $medio,
                'cantidad' => $pagoData ? $pagoData->cantidad : 0,
                'total' => $pagoData ? $pagoData->total : 0,
            ];
        }

        // Agregar medios de pago legacy (sin medio_pago_id)
        $mediosLegacy = ['efectivo', 'yape', 'plin', 'transferencia', 'otro'];
        foreach ($mediosLegacy as $medioLegacy) {
            $key = 'legacy_' . $medioLegacy;
            $pagoData = $pagosPorMedio->get($key);

            if ($pagoData && $pagoData->cantidad > 0) {
                $datosReporte[] = [
                    'medio_pago' => null,
                    'medio_pago_nombre' => ucfirst($medioLegacy),
                    'cantidad' => $pagoData->cantidad,
                    'total' => $pagoData->total,
                ];
            }
        }

        return view('comprobantes.reportes.cuadre-caja', compact(
            'datosReporte',
            'totalGeneral',
            'cantidadTotal',
            'fechaInicio',
            'fechaFin'
        ));
    }

    /**
     * Obtener detalle de pagos por método de pago
     */
    public function detalleMedioPago(Request $request)
    {
        Gate::authorize('comprobantes.read');
        $medioPagoId = $request->input('medio_pago_id');
        $medioPagoNombre = $request->input('medio_pago_nombre');
        $fechaInicio = $request->input('fecha_inicio', Carbon::today()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::today()->format('Y-m-d'));

        $fechaInicioCarbon = Carbon::parse($fechaInicio)->startOfDay();
        $fechaFinCarbon = Carbon::parse($fechaFin)->endOfDay();

        $query = Pago::with(['cliente', 'recibo.servicio', 'medioPago', 'registradoPor'])
            ->whereBetween('fecha_pago', [$fechaInicioCarbon, $fechaFinCarbon]);

        if ($medioPagoId) {
            $query->where('medio_pago_id', $medioPagoId);
        } elseif ($medioPagoNombre) {
            $query->where('medio_pago', $medioPagoNombre);
        } else {
            return response()->json(['success' => false, 'message' => 'Método de pago no especificado'], 400);
        }

        $pagos = $query->orderBy('fecha_pago', 'desc')->get();

        $medioPago = null;
        if ($medioPagoId) {
            $medioPago = MedioPago::find($medioPagoId);
        }

        return response()->json([
            'success' => true,
            'pagos' => $pagos->map(function ($pago) {
                if ($pago->fecha_hora) {
                    $fechaConHora = $pago->fecha_hora->setTimezone(config('app.timezone', 'America/Lima'))->format('d/m/Y H:i');
                } elseif ($pago->fecha_pago) {
                    $hora = $pago->created_at
                        ? $pago->created_at->setTimezone(config('app.timezone', 'America/Lima'))->format('H:i')
                        : '-';
                    $fechaConHora = $pago->fecha_pago->format('d/m/Y') . ' ' . $hora;
                } else {
                    $fechaConHora = 'N/A';
                }

                return [
                    'id' => $pago->id,
                    'fecha_pago' => $fechaConHora,
                    'monto' => $pago->monto,
                    'monto_formateado' => formato_soles($pago->monto),
                    'cliente' => $pago->cliente ? $pago->cliente->nombre : 'N/A',
                    'cliente_id' => $pago->cliente_id,
                    'numero_operacion' => $pago->numero_operacion ?? '-',
                    'codigo_verificacion' => $pago->codigo_seguridad ?? '-',
                    'servicio' => $pago->recibo && $pago->recibo->servicio ? $pago->recibo->servicio->mac_address : 'N/A',
                    'medio_pago_nombre' => $pago->medio_pago_nombre,
                    'registrado_por' => $pago->registradoPor ? $pago->registradoPor->name : 'N/A',
                ];
            }),
            'medio_pago' => $medioPago ? $medioPago->nombreCompleto : ($medioPagoNombre ? ucfirst($medioPagoNombre) : 'N/A'),
            'total' => $pagos->sum('monto'),
            'cantidad' => $pagos->count(),
        ]);
    }
}
