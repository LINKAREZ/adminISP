<?php

namespace App\Core\Traits;

/**
 * Para FormRequests que deben rellenar isp_id desde el usuario autenticado si no viene en el request.
 * En prepareForValidation() llamar: $this->mergeIspId();
 */
trait MergesIspId
{
    protected function mergeIspId(): void
    {
        if ($this->has('isp_id')) {
            return;
        }
        $user = auth()->user();
        if ($user && $user->isp_id) {
            $this->merge(['isp_id' => $user->isp_id]);
        }
    }
}
