<?php

namespace App\Modules\Comprobantes\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComprobanteItem extends Model
{
    use Auditable, BelongsToIsp;

    protected $table = 'comprobante_items';

    // Tipos de afectación IGV según SUNAT
    public const TIPO_GRAVADO = '10';           // Gravado - Operación Onerosa
    public const TIPO_EXONERADO = '20';         // Exonerado - Operación Onerosa
    public const TIPO_INAFECTO = '30';          // Inafecto - Operación Onerosa
    public const TIPO_EXPORTACION = '40';       // Exportación
    public const TIPO_GRATUITO = '21';          // Exonerado - Transferencia Gratuita

    // Unidades de medida
    public const UNIDAD_SERVICIO = 'ZZ';        // Servicio
    public const UNIDAD_UNIDAD = 'NIU';         // Unidad
    public const UNIDAD_MENSUAL = 'MON';        // Servicio mensual

    protected $fillable = [
        'comprobante_id',
        'orden',
        'codigo_producto',
        'codigo_sunat',
        'unidad_medida',
        'descripcion',
        'descripcion_detalle',
        'cantidad',
        'precio_unitario',
        'valor_unitario',
        'descuento',
        'subtotal',
        'igv',
        'total',
        'tipo_afectacion_igv',
        'porcentaje_igv',
        'servicio_id',
        'recibo_id',
        'periodo',
        'isp_id',
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'precio_unitario' => 'decimal:4',
        'valor_unitario' => 'decimal:4',
        'descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'igv' => 'decimal:2',
        'total' => 'decimal:2',
        'porcentaje_igv' => 'decimal:2',
    ];

    /**
     * Relación con comprobante
     */
    public function comprobante(): BelongsTo
    {
        return $this->belongsTo(Comprobante::class);
    }

    /**
     * Relación con servicio
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Servicios\Models\Servicio::class);
    }

    /**
     * Relación con recibo
     */
    public function recibo(): BelongsTo
    {
        return $this->belongsTo(Recibo::class);
    }

    /**
     * Calcular montos automáticamente
     */
    public function calcularMontos(): void
    {
        $cantidad = $this->cantidad ?? 1;
        $precioUnitario = $this->precio_unitario ?? 0;
        $descuento = $this->descuento ?? 0;
        $porcentajeIgv = $this->porcentaje_igv ?? 18;

        // Si es exonerado o inafecto, no hay IGV
        $tieneIgv = $this->tipo_afectacion_igv === self::TIPO_GRAVADO;

        if ($tieneIgv) {
            // El precio unitario incluye IGV, calcular valor unitario
            $this->valor_unitario = round($precioUnitario / (1 + ($porcentajeIgv / 100)), 4);
            $this->subtotal = round(($this->valor_unitario * $cantidad) - $descuento, 2);
            $this->igv = round($this->subtotal * ($porcentajeIgv / 100), 2);
            $this->total = round($this->subtotal + $this->igv, 2);
        } else {
            // Sin IGV
            $this->valor_unitario = $precioUnitario;
            $this->subtotal = round(($precioUnitario * $cantidad) - $descuento, 2);
            $this->igv = 0;
            $this->total = $this->subtotal;
        }
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->calcularMontos();
        });
    }

    /**
     * Obtener descripción formateada con período
     */
    public function getDescripcionCompletaAttribute(): string
    {
        $descripcion = $this->descripcion;

        if ($this->periodo) {
            $descripcion .= " - Período: {$this->periodo}";
        }

        if ($this->descripcion_detalle) {
            $descripcion .= "\n{$this->descripcion_detalle}";
        }

        return $descripcion;
    }

    /**
     * Obtener etiqueta del tipo de afectación
     */
    public function getTipoAfectacionLabelAttribute(): string
    {
        return match ($this->tipo_afectacion_igv) {
            self::TIPO_GRAVADO => 'Gravado',
            self::TIPO_EXONERADO => 'Exonerado',
            self::TIPO_INAFECTO => 'Inafecto',
            self::TIPO_EXPORTACION => 'Exportación',
            self::TIPO_GRATUITO => 'Gratuito',
            default => 'Desconocido',
        };
    }
}
