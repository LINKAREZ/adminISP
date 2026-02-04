<?php

namespace App\Modules\Notificaciones\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class PlantillaWhatsApp extends Model
{
    use Auditable;
    protected $table = 'plantillas_whatsapp';

    protected $fillable = [
        'tipo',
        'nombre',
        'mensaje',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Reemplazar variables en el mensaje
     */
    public function procesarMensaje(array $variables): string
    {
        $mensaje = $this->mensaje;

        foreach ($variables as $key => $value) {
            $mensaje = str_replace('{' . $key . '}', $value, $mensaje);
        }

        return $mensaje;
    }

    /**
     * Obtener plantilla por tipo
     */
    public static function porTipo(string $tipo): ?self
    {
        return self::where('tipo', $tipo)
            ->where('activo', true)
            ->first();
    }
}
