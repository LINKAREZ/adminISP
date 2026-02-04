<?php

namespace App\Modules\Comprobantes\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comprobante extends Model
{
    use Auditable, BelongsToIsp;

    // Tipo único de comprobante para este sistema
    public const TIPO_RECIBO = 'recibo';

    // Estados del comprobante
    public const ESTADO_EMITIDO = 'emitido';
    public const ESTADO_ENVIADO = 'enviado';      // Enviado a SUNAT
    public const ESTADO_ACEPTADO = 'aceptado';    // Aceptado por SUNAT
    public const ESTADO_RECHAZADO = 'rechazado';  // Rechazado por SUNAT
    public const ESTADO_ANULADO = 'anulado';

    // Formas de pago
    public const FORMA_CONTADO = 'contado';
    public const FORMA_CREDITO = 'credito';

    protected $fillable = [
        'pago_id',
        'cliente_id',
        'tipo',
        'serie',
        'numero',
        'numero_completo',
        'fecha_emision',
        'monto',
        'moneda',
        'tipo_cambio',
        'subtotal',
        'igv',
        'descuento',
        'exonerado_igv',
        'cliente_nombre',
        'cliente_documento',
        'cliente_tipo_documento',
        'cliente_direccion',
        'hash',
        'codigo_respuesta',
        'mensaje_respuesta',
        'ticket_sunat',
        'enviado_sunat_at',
        'enviado_sunat',
        'forma_pago',
        'fecha_vencimiento_pago',
        'isp_id',
        'condiciones_pago',
        'guia_remision',
        'orden_compra',
        'comprobante_referencia_id',
        'tipo_nota',
        'motivo_nota',
        'periodo_servicio',
        'fecha_inicio_servicio',
        'fecha_fin_servicio',
        'anulado',
        'anulado_at',
        'anulado_por',
        'motivo_anulacion',
        'estado',
        'generado_por',
        'notas',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento_pago' => 'date',
        'fecha_inicio_servicio' => 'date',
        'fecha_fin_servicio' => 'date',
        'monto' => 'decimal:2',
        'tipo_cambio' => 'decimal:4',
        'subtotal' => 'decimal:2',
        'igv' => 'decimal:2',
        'descuento' => 'decimal:2',
        'exonerado_igv' => 'boolean',
        'enviado_sunat' => 'boolean',
        'anulado' => 'boolean',
        'enviado_sunat_at' => 'datetime',
        'anulado_at' => 'datetime',
    ];

    /**
     * Relación con pago
     */
    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class);
    }

    /**
     * Relación con cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Clientes\Models\Cliente::class);
    }

    /**
     * Usuario que generó el comprobante
     */
    public function generadoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\ControlAcceso\Models\User::class, 'generado_por');
    }

    /**
     * Usuario que anuló el comprobante
     */
    public function anuladoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\ControlAcceso\Models\User::class, 'anulado_por');
    }

    /**
     * Comprobante de referencia (para notas de crédito/débito)
     */
    public function comprobanteReferencia(): BelongsTo
    {
        return $this->belongsTo(self::class, 'comprobante_referencia_id');
    }

    /**
     * Notas asociadas a este comprobante
     */
    public function notas(): HasMany
    {
        return $this->hasMany(self::class, 'comprobante_referencia_id');
    }

    /**
     * Ítems del comprobante
     */
    public function items(): HasMany
    {
        return $this->hasMany(ComprobanteItem::class)->orderBy('orden');
    }

    /**
     * Generar número completo (serie-numero)
     */
    public function getNumeroCompletoAttribute(): string
    {
        if ($this->attributes['numero_completo'] ?? null) {
            return $this->attributes['numero_completo'];
        }
        return sprintf('%s-%08d', $this->serie, $this->numero);
    }

    /**
     * Obtener el siguiente número para una serie
     */
    public static function obtenerSiguienteNumero(string $tipo, string $serie): int
    {
        return SerieComprobante::obtenerSiguienteNumero($tipo, $serie);
    }

    /**
     * Verificar si es recibo
     */
    public function esRecibo(): bool
    {
        return $this->tipo === self::TIPO_RECIBO;
    }

    /**
     * Verificar si está anulado
     */
    public function estaAnulado(): bool
    {
        return $this->anulado === true;
    }

    /**
     * Verificar si fue enviado a SUNAT
     */
    public function fueEnviadoSunat(): bool
    {
        return $this->enviado_sunat === true;
    }

    /**
     * Verificar si puede ser anulado
     */
    public function puedeAnularse(): bool
    {
        // No puede anularse si ya está anulado
        if ($this->estaAnulado()) {
            return false;
        }

        // Si ya fue enviado a SUNAT, se debe generar nota de crédito en su lugar
        if ($this->fueEnviadoSunat() && $this->estado === self::ESTADO_ACEPTADO) {
            return false;
        }

        return true;
    }

    /**
     * Anular comprobante
     */
    public function anular(string $motivo, ?int $usuarioId = null): bool
    {
        if (!$this->puedeAnularse()) {
            return false;
        }

        $this->update([
            'anulado' => true,
            'anulado_at' => now(),
            'anulado_por' => $usuarioId ?? auth()->id(),
            'motivo_anulacion' => $motivo,
            'estado' => self::ESTADO_ANULADO,
        ]);

        return true;
    }

    /**
     * Obtener etiqueta del tipo
     */
    public function getTipoLabelAttribute(): string
    {
        return 'Recibo';
    }

    /**
     * Obtener color del estado
     */
    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_EMITIDO => 'info',
            self::ESTADO_ENVIADO => 'warning',
            self::ESTADO_ACEPTADO => 'success',
            self::ESTADO_RECHAZADO => 'danger',
            self::ESTADO_ANULADO => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Calcular totales desde los ítems
     */
    public function calcularTotales(): void
    {
        $items = $this->items;

        $subtotal = $items->sum('subtotal');
        $igv = $items->sum('igv');
        $descuento = $items->sum('descuento');
        $total = $items->sum('total');

        $this->update([
            'subtotal' => $subtotal,
            'igv' => $igv,
            'descuento' => $descuento,
            'monto' => $total,
        ]);
    }

    /**
     * Guardar snapshot del cliente al momento de emisión
     */
    public function guardarSnapshotCliente(): void
    {
        if (!$this->cliente) {
            return;
        }

        $this->update([
            'cliente_nombre' => $this->cliente->nombre,
            'cliente_documento' => $this->cliente->documento,
            'cliente_tipo_documento' => $this->cliente->tipo_documento,
            'cliente_direccion' => $this->cliente->direccion ?? null,
        ]);
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopeTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para filtrar por serie
     */
    public function scopeSerie($query, string $serie)
    {
        return $query->where('serie', $serie);
    }

    /**
     * Scope para comprobantes vigentes (no anulados)
     */
    public function scopeVigentes($query)
    {
        return $query->where('anulado', false);
    }

    /**
     * Scope para comprobantes anulados
     */
    public function scopeAnulados($query)
    {
        return $query->where('anulado', true);
    }

    /**
     * Scope para comprobantes del mes actual
     */
    public function scopeMesActual($query)
    {
        return $query->whereYear('fecha_emision', now()->year)
            ->whereMonth('fecha_emision', now()->month);
    }

    /**
     * Scope para comprobantes de un período
     */
    public function scopePeriodo($query, string $periodo)
    {
        return $query->where('periodo_servicio', $periodo);
    }
}
