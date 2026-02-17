<?php

namespace App\Modules\Comprobantes\Services;

use App\Modules\Comprobantes\Models\Recibo;
use App\Modules\Servicios\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ReciboService
{
    /**
     * Obtener número de ubicación del cliente
     * Si el recibo tiene servicio, usa la ubicación del servicio
     * Si no, cuenta las ubicaciones del cliente para determinar el número
     */
    private function obtenerNumeroUbicacion(array $data, \App\Modules\Clientes\Models\Cliente $cliente): int
    {
        if (!empty($data['servicio_id'])) {
            $servicio = \App\Modules\Servicios\Models\Servicio::with('ubicacion')->findOrFail($data['servicio_id']);
            if ($servicio && $servicio->ubicacion) {
                // Obtener todas las ubicaciones del cliente ordenadas por ID
                $ubicaciones = $cliente->ubicaciones()->orderBy('id')->get();
                // Encontrar el índice de la ubicación del servicio (1-based)
                $indice = $ubicaciones->search(function($ubicacion) use ($servicio) {
                    return $ubicacion->id === $servicio->ubicacion_id;
                });
                return $indice !== false ? $indice + 1 : 1;
            }
        }

        // Si no hay servicio, usar la primera ubicación o 1 por defecto
        $ubicaciones = $cliente->ubicaciones()->orderBy('id')->get();
        return $ubicaciones->count() > 0 ? 1 : 1;
    }

    /**
     * Generar código único para un recibo
     * Formato: DNI+YYYY+MM+U+Número
     * Ejemplo: 87654321202501U1
     *
     * El formato es: DNI(8) + AÑO(4) + MES(2) + U + Número(1+)
     *
     * @param array $data Datos del recibo
     * @param \App\Modules\Clientes\Models\Cliente $cliente Cliente asociado
     * @return string Código único del recibo
     */
    private function generarCodigoUnico(array $data, \App\Modules\Clientes\Models\Cliente $cliente): string
    {
        // Obtener DNI del cliente (8 dígitos)
        $dni = str_pad(substr($cliente->documento ?? '00000000', 0, 8), 8, '0', STR_PAD_LEFT);

        // Obtener período (YYYYMM)
        $periodo = $data['periodo'] ?? now()->format('Y-m');
        $periodoFormato = str_replace('-', '', $periodo); // YYYYMM
        $ano = substr($periodoFormato, 0, 4); // YYYY
        $mes = substr($periodoFormato, 4, 2); // MM

        // Obtener número de ubicación
        $numeroUbicacion = $this->obtenerNumeroUbicacion($data, $cliente);

        // Construir prefijo base: DNI + YYYY + MM + U
        $prefijo = "{$dni}{$ano}{$mes}U";

        // Buscar códigos existentes con este prefijo para determinar el siguiente número
        $codigosExistentes = Recibo::where('codigo', 'like', "{$prefijo}%")
            ->orderBy('codigo', 'desc')
            ->pluck('codigo');

        $numero = $numeroUbicacion;

        // Si ya existe un código con este número, buscar el siguiente disponible
        $codigoCompleto = $prefijo . $numero;
        while ($codigosExistentes->contains($codigoCompleto)) {
            $numero++;
            $codigoCompleto = $prefijo . $numero;
        }

        return $codigoCompleto;
    }

    /**
     * Crear un nuevo recibo manualmente
     *
     * Establece valores por defecto y crea el recibo.
     * La lógica está centralizada aquí en lugar del controlador.
     */
    public function crearRecibo(array $data, \App\Modules\Clientes\Models\Cliente $cliente): Recibo
    {
        // Establecer valores por defecto
        $data['cliente_id'] = $cliente->id;
        $data['saldo'] = $data['saldo'] ?? $data['monto'];
        
        // Determinar el estado basándose en la fecha de vencimiento si no se especifica
        if (!isset($data['estado'])) {
            if (isset($data['fecha_vencimiento'])) {
                $fechaVencimiento = Carbon::parse($data['fecha_vencimiento'])->startOfDay();
                // Si la fecha de vencimiento ya pasó (comparando solo fechas, sin hora), el recibo debe estar vencido
                $data['estado'] = $fechaVencimiento->isPast() ? Recibo::ESTADO_VENCIDO : Recibo::ESTADO_PENDIENTE;
            } else {
                $data['estado'] = Recibo::ESTADO_PENDIENTE;
            }
        }

        // Generar código único si no existe
        if (empty($data['codigo'])) {
            $data['codigo'] = $this->generarCodigoUnico($data, $cliente);
        }

        $recibo = Recibo::create($data);

        // Invalidar cachés relacionados
        $this->invalidarCachesRecibo($recibo);

        return $recibo;
    }

    /**
     * Actualizar un recibo existente
     *
     * Actualiza los datos y recalcula el estado automáticamente.
     * El estado y saldo se recalculan basándose en los pagos registrados.
     *
     * @param Recibo $recibo Recibo a actualizar
     * @param array $data Datos a actualizar
     * @return Recibo Recibo actualizado
     */
    public function actualizarRecibo(Recibo $recibo, array $data): Recibo
    {
        $recibo->update($data);
        // Recalcular estado y saldo automáticamente
        $recibo->actualizarEstado();

        // Invalidar cachés relacionados
        $this->invalidarCachesRecibo($recibo);

        return $recibo;
    }

    public function generarReciboMensual(Servicio $servicio, string $periodo): ?Recibo
    {
        // Asegurar que el servicio tenga la relación plan cargada
        if (!$servicio->relationLoaded('plan')) {
            $servicio->load('plan');
        }

        // Permitir activos o cortados en este período (prorrateo por suspensión)
        $mesPeriodo = Carbon::createFromFormat('Y-m', $periodo);
        $inicioPeriodo = $mesPeriodo->copy()->startOfMonth();
        $finPeriodo = $mesPeriodo->copy()->endOfMonth();
        if ($servicio->estado === 'cortado') {
            if (!$servicio->fecha_corte || $servicio->fecha_corte->format('Y-m') !== $periodo) {
                $this->logDebug("Servicio cortado fuera de este período, no se genera recibo", [
                    'servicio_id' => $servicio->id,
                ]);
                return null;
            }
        } elseif ($servicio->estado !== 'activo') {
            return null;
        }

        if (!$servicio->plan || !$servicio->plan->precio_mensual) {
            Log::warning("Servicio sin plan o sin precio mensual", [
                'servicio_id' => $servicio->id,
                'plan_id' => $servicio->plan_id
            ]);
            return null;
        }

        $reciboExistente = Recibo::where('servicio_id', $servicio->id)
            ->where('periodo', $periodo)
            ->first();

        if ($reciboExistente) {
            $this->logDebug("Recibo ya existe para este período", [
                'servicio_id' => $servicio->id,
                'periodo' => $periodo,
                'recibo_id' => $reciboExistente->id
            ]);
            return $reciboExistente;
        }

        // Fecha inicio de cobro: cuando el servicio recién está activo (ej. técnico instaló día 3, activo día 5)
        $fechaInicioCobro = $servicio->fecha_activacion_definitiva
            ? $servicio->fecha_activacion_definitiva->copy()->startOfDay()
            : ($servicio->fecha_instalacion ? $servicio->fecha_instalacion->copy()->startOfDay() : null);
        if (!$fechaInicioCobro) {
            $this->logDebug("Servicio sin fecha de instalación ni activación, no se genera recibo", [
                'servicio_id' => $servicio->id,
                'periodo' => $periodo,
            ]);
            return null;
        }
        if ($fechaInicioCobro->format('Y-m') > $periodo) {
            $this->logDebug("Período anterior al mes de inicio de cobro, no se genera recibo", [
                'servicio_id' => $servicio->id,
                'periodo' => $periodo,
            ]);
            return null;
        }

        // ✅ Obtener cliente_id desde ubicación
        $servicio->load('ubicacion');

        // Validar que el servicio tenga ubicación con cliente
        if (!$servicio->ubicacion || !$servicio->ubicacion->cliente_id) {
            Log::warning("Servicio sin ubicación válida o sin cliente", [
                'servicio_id' => $servicio->id,
                'ubicacion_id' => $servicio->ubicacion_id
            ]);
            return null;
        }

        $clienteId = $servicio->ubicacion->cliente_id;

        // Obtener cliente para generar código
        $cliente = \App\Modules\Clientes\Models\Cliente::find($clienteId);

        if (!$cliente) {
            Log::warning("Cliente no encontrado", [
                'cliente_id' => $clienteId,
                'servicio_id' => $servicio->id
            ]);
            return null;
        }

        // Prorrateo: días a cobrar en el mes (inicio de cobro o corte/suspensión)
        $inicioCobroMes = $fechaInicioCobro->gt($inicioPeriodo) ? $fechaInicioCobro : $inicioPeriodo->copy();
        $finCobroMes = $servicio->fecha_corte && $servicio->fecha_corte->format('Y-m') === $periodo
            ? Carbon::min($servicio->fecha_corte, $finPeriodo)->copy()
            : $finPeriodo->copy();
        if ($inicioCobroMes->gt($finCobroMes)) {
            $this->logDebug("Sin días a cobrar en el período", [
                'servicio_id' => $servicio->id,
                'periodo' => $periodo,
            ]);
            return null;
        }
        $diasEnMes = (int) $mesPeriodo->daysInMonth();
        $diasACobrar = $inicioCobroMes->diffInDays($finCobroMes) + 1;
        $montoProrrateado = round((float) $servicio->plan->precio_mensual * $diasACobrar / $diasEnMes, 2);

        // Fechas de emisión y vencimiento desde configuración (control total del ciclo de cobro)
        $diaEmision = (int) config('isp.comprobantes.dia_emision', 1);
        $diasVencimiento = (int) config('isp.comprobantes.dias_vencimiento', 15);
        $mesPeriodo = Carbon::createFromFormat('Y-m', $periodo);
        $diaEmisionSeguro = min(max(1, $diaEmision), $mesPeriodo->daysInMonth());
        $fechaEmision = $mesPeriodo->copy()->day($diaEmisionSeguro);
        $fechaVencimiento = $fechaEmision->copy()->addDays($diasVencimiento);
        if ($fechaVencimiento->format('Y-m') !== $periodo) {
            $fechaVencimiento = $mesPeriodo->copy()->endOfMonth();
        }

        // Determinar el estado basándose en la fecha de vencimiento (comparar solo fechas, sin hora)
        $fechaVencimientoComparar = $fechaVencimiento->copy()->startOfDay();
        $estado = $fechaVencimientoComparar->isPast() ? Recibo::ESTADO_VENCIDO : Recibo::ESTADO_PENDIENTE;

        $recibo = Recibo::create([
            'codigo' => $this->generarCodigoUnico([
                'servicio_id' => $servicio->id,
                'periodo' => $periodo,
            ], $cliente),
            'cliente_id' => $clienteId,
            'servicio_id' => $servicio->id,
            'periodo' => $periodo,
            'fecha_emision' => $fechaEmision,
            'fecha_vencimiento' => $fechaVencimiento,
            'monto' => $montoProrrateado,
            'saldo' => $montoProrrateado,
            'estado' => $estado,
        ]);

        $this->logDebug("Recibo mensual generado", [
            'recibo_id' => $recibo->id,
            'servicio_id' => $servicio->id,
            'periodo' => $periodo,
            'monto' => $recibo->monto,
            'fecha_vencimiento' => $recibo->fecha_vencimiento->format('Y-m-d')
        ]);

        return $recibo;
    }

    public function generarReciboParaServicio(Servicio $servicio, string $periodo): ?Recibo
    {
        return $this->generarReciboMensual($servicio, $periodo);
    }

    public function generarRecibosMensuales(?string $periodo = null): array
    {
        $periodo = $periodo ?? now()->format('Y-m');

        $servicios = Servicio::where('estado', 'activo')
            ->with(['plan', 'ubicacion.cliente'])
            ->get();

        $generadas = 0;
        $existentes = 0;
        $errores = 0;

        foreach ($servicios as $servicio) {
            try {
                $recibo = $this->generarReciboMensual($servicio, $periodo);

                if ($recibo) {
                    if ($recibo->wasRecentlyCreated) {
                        $generadas++;
                    } else {
                        $existentes++;
                    }
                } else {
                    $errores++;
                }
            } catch (\Exception $e) {
                Log::error("Error al generar recibo para servicio", [
                    'servicio_id' => $servicio->id,
                    'periodo' => $periodo,
                    'error' => $e->getMessage()
                ]);
                $errores++;
            }
        }

        return [
            'generadas' => $generadas,
            'existentes' => $existentes,
            'errores' => $errores,
            'total_servicios' => $servicios->count()
        ];
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $context);
        }
    }

    /**
     * Invalidar todos los cachés relacionados con un recibo
     */
    private function invalidarCachesRecibo(Recibo $recibo): void
    {
        // Invalidar caché de servicios activos del cliente
        Cache::forget("cliente.{$recibo->cliente_id}.servicios.activos.formateados");

        // Invalidar caché de recibos del servicio
        if ($recibo->servicio_id) {
            Cache::forget("servicio.{$recibo->servicio_id}.recibos.formateados");
        }

        // Invalidar estadísticas del cliente
        Cache::forget("cliente.{$recibo->cliente_id}.estadisticas");
    }
}
