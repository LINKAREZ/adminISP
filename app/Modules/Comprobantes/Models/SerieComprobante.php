<?php

namespace App\Modules\Comprobantes\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SerieComprobante extends Model
{
    use Auditable, BelongsToIsp;

    protected $table = 'series_comprobantes';

    protected $fillable = [
        'tipo',
        'serie',
        'ultimo_numero',
        'activo',
        'descripcion',
        'genera_automatico',
        'envia_sunat',
        'isp_id',
    ];

    protected $casts = [
        'ultimo_numero' => 'integer',
        'activo' => 'boolean',
        'genera_automatico' => 'boolean',
        'envia_sunat' => 'boolean',
    ];

    /**
     * Obtener y reservar el siguiente número para una serie
     * Usa bloqueo para evitar duplicados en concurrencia
     */
    public static function obtenerSiguienteNumero(string $tipo, string $serie): int
    {
        return DB::transaction(function () use ($tipo, $serie) {
            // Bloquear la fila para actualización
            $serieModel = self::where('tipo', $tipo)
                ->where('serie', $serie)
                ->lockForUpdate()
                ->first();

            if (!$serieModel) {
                // Crear serie si no existe
                $serieModel = self::create([
                    'tipo' => $tipo,
                    'serie' => $serie,
                    'ultimo_numero' => 0,
                    'activo' => true,
                ]);
            }

            $siguienteNumero = $serieModel->ultimo_numero + 1;
            $serieModel->update(['ultimo_numero' => $siguienteNumero]);

            return $siguienteNumero;
        });
    }

    /**
     * Obtener serie activa por tipo
     */
    public static function obtenerSerieActiva(string $tipo): ?self
    {
        return self::where('tipo', $tipo)
            ->where('activo', true)
            ->orderBy('id')
            ->first();
    }

    /**
     * Obtener número completo formateado
     */
    public function formatearNumero(int $numero): string
    {
        return sprintf('%s-%08d', $this->serie, $numero);
    }

    /**
     * Scope para series activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope por tipo
     */
    public function scopeTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }
}
