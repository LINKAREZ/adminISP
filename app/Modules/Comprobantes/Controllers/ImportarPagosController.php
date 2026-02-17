<?php

namespace App\Modules\Comprobantes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Comprobantes\Models\Pago;
use App\Modules\Comprobantes\Models\Recibo;
use App\Modules\Comprobantes\Services\PagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ImportarPagosController extends Controller
{
    public function __construct(private PagoService $pagoService) {}

    public function index()
    {
        Gate::authorize('comprobantes.create');
        return view('comprobantes.importar-pagos.index');
    }

    public function store(Request $request)
    {
        Gate::authorize('comprobantes.create');
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('archivo');
        $handle = fopen($file->getPathname(), 'r');
        $encabezado = fgetcsv($handle);
        $creados = 0;
        $errores = [];

        while (($fila = fgetcsv($handle)) !== false) {
            if (count($fila) < 4) {
                continue;
            }
            $clienteId = isset($fila[0]) ? trim($fila[0]) : null;
            $reciboId = isset($fila[1]) ? trim($fila[1]) : null;
            $periodo = isset($fila[2]) ? trim($fila[2]) : null;
            $monto = isset($fila[3]) ? str_replace(',', '.', trim($fila[3])) : null;
            $fechaPago = isset($fila[4]) ? trim($fila[4]) : date('Y-m-d');
            $medioPago = isset($fila[5]) ? trim($fila[5]) : 'efectivo';
            $numeroOperacion = isset($fila[6]) ? trim($fila[6]) : null;

            if (!$clienteId || !$monto || !is_numeric($monto) || (float) $monto <= 0) {
                $errores[] = 'Fila inválida: ' . implode(',', $fila);
                continue;
            }

            $recibo = null;
            if ($reciboId && is_numeric($reciboId)) {
                $recibo = Recibo::where('id', $reciboId)->where('cliente_id', $clienteId)->first();
            }
            if (!$recibo && $periodo) {
                $recibo = Recibo::where('cliente_id', $clienteId)->where('periodo', $periodo)->where('saldo', '>', 0)->first();
            }
            if (!$recibo) {
                $recibo = Recibo::where('cliente_id', $clienteId)->where('saldo', '>', 0)->orderBy('fecha_vencimiento')->first();
            }

            try {
                $pago = Pago::create([
                    'cliente_id' => $clienteId,
                    'recibo_id' => $recibo?->id,
                    'servicio_id' => $recibo?->servicio_id,
                    'monto' => (float) $monto,
                    'fecha_pago' => $fechaPago,
                    'medio_pago' => $medioPago ?: 'efectivo',
                    'numero_operacion' => $numeroOperacion,
                    'registrado_por' => Auth::id(),
                ]);
                $pago->load('recibo');
                $this->pagoService->procesarPago($pago);
                $creados++;
            } catch (\Throwable $e) {
                $errores[] = 'Fila ' . implode(',', $fila) . ': ' . $e->getMessage();
            }
        }
        fclose($handle);

        $msg = "Se importaron {$creados} pago(s).";
        if (!empty($errores)) {
            $msg .= ' Errores: ' . count($errores);
            return redirect()->route('comprobantes.importar-pagos.index')
                ->with('warning', $msg)
                ->with('errores_importacion', array_slice($errores, 0, 20));
        }
        return redirect()->route('comprobantes.importar-pagos.index')->with('success', $msg);
    }

    public function plantilla()
    {
        Gate::authorize('comprobantes.read');
        $csv = "cliente_id,recibo_id,periodo,monto,fecha_pago,medio_pago,numero_operacion\n";
        $csv .= "1,,2025-02,50.00,2025-02-11,efectivo,\n";
        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_importar_pagos.csv"',
        ]);
    }
}
