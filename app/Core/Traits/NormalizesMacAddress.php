<?php

namespace App\Core\Traits;

trait NormalizesMacAddress
{
    /**
     * Normalizar formato de MAC address
     * Convierte diferentes formatos a formato estándar: XX:XX:XX:XX:XX:XX
     * Valida que la MAC tenga exactamente 12 caracteres hexadecimales
     *
     * @param string $mac MAC address en cualquier formato
     * @param bool $strict Si es true, lanza excepción si la MAC es inválida
     * @return string MAC address normalizada
     * @throws \InvalidArgumentException Si $strict es true y la MAC es inválida
     */
    protected function normalizarMacAddress(string $mac, bool $strict = false): string
    {
        // Limpiar espacios y convertir a mayúsculas
        $mac = strtoupper(trim($mac));

        // Remover TODOS los separadores usando regex (incluye :, -, espacios, y múltiples ::)
        $mac = preg_replace('/[:\-\s]+/', '', $mac);

        // Verificar que tenga exactamente 12 caracteres hexadecimales
        if (strlen($mac) !== 12 || !ctype_xdigit($mac)) {
            if ($strict) {
                throw new \InvalidArgumentException('MAC address inválida: debe tener 12 caracteres hexadecimales. Valor recibido: ' . $mac);
            }
            // Si no es estricto, intentar normalizar de forma más permisiva (compatibilidad)
            // Si tiene guiones, mantenerlos; si no, agregar guiones cada 2 caracteres
            if (strpos($mac, '-') === false && strpos($mac, ':') === false) {
                $mac = implode(':', str_split($mac, 2));
            } elseif (strpos($mac, '-') !== false) {
                $mac = str_replace('-', ':', $mac);
            }
            return $mac;
        }

        // Formatear con dos puntos cada 2 caracteres (formato estándar: XX:XX:XX:XX:XX:XX = 17 caracteres)
        return implode(':', str_split($mac, 2));
    }
}

