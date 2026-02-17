<?php

namespace App\Modules\Instalaciones\Services;

use App\Modules\Instalaciones\Models\ComisionVendedor;
use App\Modules\Instalaciones\Models\OrdenInstalacion;
use App\Modules\Servicios\Models\Servicio;
use Carbon\Carbon;

class ComisionService
{
    /**
     * Mes de permanencia desde fecha_completada: 1, 2, 3 o 3+ (1-indexed).
     */
    public function mesDePermanencia(OrdenInstalacion $orden): ?int
    {
        if (!$orden->fecha_completada || !$orden->servicio_id) {
            return null;
        }
        $fin = $orden->fecha_completada->copy()->startOfDay();
        $hoy = Carbon::now()->startOfDay();
        $meses = (int) $fin->diffInMonths($hoy, false);
        if ($meses < 0) {
            return 0;
        }
        return min(3, $meses + 1); // 1, 2 o 3 (3 representa 3+)
    }

    /**
     * Fecha en que se cumple el 3er mes (inicio del día siguiente al mes 3).
     */
    public function fechaCumplimientoTercerMes(OrdenInstalacion $orden): ?Carbon
    {
        if (!$orden->fecha_completada) {
            return null;
        }
        return $orden->fecha_completada->copy()->addMonths(3)->startOfDay();
    }

    /**
     * True si el servicio estuvo activo hasta al menos el cumplimiento del 3er mes (no fue cortado antes).
     */
    public function servicioActivoHastaTercerMes(OrdenInstalacion $orden): bool
    {
        if (!$orden->servicio_id) {
            return false;
        }
        $servicio = Servicio::find($orden->servicio_id);
        if (!$servicio) {
            return false;
        }
        $fechaCumplimiento = $this->fechaCumplimientoTercerMes($orden);
        if (!$fechaCumplimiento) {
            return false;
        }
        if ($servicio->estado === Servicio::ESTADO_ACTIVO) {
            return true;
        }
        if ($servicio->estado === Servicio::ESTADO_CORTADO && $servicio->fecha_corte) {
            return $servicio->fecha_corte->startOfDay()->gte($fechaCumplimiento);
        }
        return false;
    }

    /**
     * Elegible para comisión: mes >= 3, tiene vendedor_id, servicio activo hasta 3er mes, y no tiene ya comisión registrada.
     */
    public function elegibleParaComision(OrdenInstalacion $orden): bool
    {
        if (!$orden->vendedor_id) {
            return false;
        }
        $mes = $this->mesDePermanencia($orden);
        if ($mes === null || $mes < 3) {
            return false;
        }
        if (!$this->servicioActivoHastaTercerMes($orden)) {
            return false;
        }
        return !ComisionVendedor::where('orden_instalacion_id', $orden->id)->exists();
    }

    /**
     * Etiqueta de mes para vista: "1", "2", "3+".
     */
    public function etiquetaMesPermanencia(OrdenInstalacion $orden): string
    {
        $mes = $this->mesDePermanencia($orden);
        if ($mes === null || $mes === 0) {
            return '-';
        }
        return $mes >= 3 ? '3+' : (string) $mes;
    }
}
