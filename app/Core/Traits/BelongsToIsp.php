<?php

namespace App\Core\Traits;

use App\Modules\Sistema\Models\Isp;
use App\Core\Scopes\IspScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToIsp
{
    /**
     * Boot del trait
     */
    protected static function bootBelongsToIsp(): void
    {
        // No aplicar scope al modelo Isp (es el modelo principal)
        if (static::class === \App\Modules\Sistema\Models\Isp::class) {
            return;
        }

        // Aplicar global scope (excepto para User que se maneja en el scope)
        static::addGlobalScope(new IspScope());

        // Auto-asignar isp_id al crear (excepto para User durante registro)
        static::creating(function ($model) {
            // No auto-asignar isp_id a User durante el registro inicial
            // Se asignará manualmente o desde el usuario que lo crea
            if (static::class === \App\Modules\ControlAcceso\Models\User::class) {
                // Si no viene isp_id, usar el del usuario autenticado que lo crea
                if (empty($model->isp_id) && auth()->check() && auth()->user()->isp_id) {
                    $model->isp_id = auth()->user()->isp_id;
                }
                return;
            }

            if (empty($model->isp_id)) {
                $model->isp_id = static::getCurrentIspId();
            }
        });
    }

    /**
     * Relación con ISP
     */
    public function isp(): BelongsTo
    {
        return $this->belongsTo(Isp::class);
    }

    /**
     * Obtener el ISP ID actual
     */
    protected static function getCurrentIspId(): ?int
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user && $user->isp_id) {
                return $user->isp_id;
            }
        }

        return session('current_isp_id');
    }
}
