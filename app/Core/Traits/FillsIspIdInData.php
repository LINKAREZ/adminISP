<?php

namespace App\Core\Traits;

/**
 * Para controladores que asignan isp_id al montar $data para create/update.
 * Evita repetir "if (auth()->user()->isp_id) { $data['isp_id'] = ... }".
 */
trait FillsIspIdInData
{
    protected function mergeIspIdInto(array $data): array
    {
        if (array_key_exists('isp_id', $data) && $data['isp_id'] !== null && $data['isp_id'] !== '') {
            return $data;
        }
        $ispId = auth()->check() ? auth()->user()->isp_id : null;
        if ($ispId !== null) {
            $data['isp_id'] = $ispId;
        }
        return $data;
    }
}
