<?php

namespace App\Modules\Servicios\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Onu extends Model
{
    use Auditable, BelongsToIsp, UsesTenantConnection;
    protected $fillable = [
        'servicio_id',
        'serial_number',
        'serial_number_completo',
        'serial_number_olt',
        'mac_address',
        'usuario',
        'password',
        'marca',
        'modelo',
        'notas',
        'isp_id',
    ];

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Servicios\Models\Servicio::class);
    }

    public function setMacAddressAttribute($value)
    {
        if ($value) {
            $value = strtoupper(trim($value));
            $value = preg_replace('/[:\-\s]+/', '', $value);

            if (strlen($value) === 12 && ctype_xdigit($value)) {
                $this->attributes['mac_address'] = implode(':', str_split($value, 2));
            } else {
                $this->attributes['mac_address'] = $value;
            }
        }
    }

    private static function modelosConTransformacion(): array
    {
        return ['624G', '622G', 'ATW-624G', 'ATW-622G'];
    }

    public function requiereTransformacion(): bool
    {
        $modelo = strtoupper(trim($this->modelo ?? ''));
        return in_array($modelo, array_map('strtoupper', self::modelosConTransformacion()));
    }

    public static function modeloRequiereTransformacion(?string $modelo): bool
    {
        if (empty($modelo)) {
            return false;
        }
        $modeloUpper = strtoupper(trim($modelo));
        return in_array($modeloUpper, array_map('strtoupper', self::modelosConTransformacion()));
    }

    public static function transformarSerialAOLT(string $serialCompleto, ?string $modelo = null): string
    {
        if ($modelo && !self::modeloRequiereTransformacion($modelo)) {
            return $serialCompleto;
        }

        if (preg_match('/^[A-Z]{2,}/', $serialCompleto)) {
            return $serialCompleto;
        }

        if (strlen($serialCompleto) < 8) {
            return $serialCompleto;
        }

        $prefijoHex = substr($serialCompleto, 0, 8);
        $sufijo = substr($serialCompleto, 8);

        if (!ctype_xdigit($prefijoHex)) {
            return $serialCompleto;
        }

        $prefijoAscii = '';
        for ($i = 0; $i < 8; $i += 2) {
            $hexPair = substr($prefijoHex, $i, 2);
            $decimalValue = hexdec($hexPair);
            $asciiChar = chr($decimalValue);

            if ($decimalValue >= 32 && $decimalValue <= 126) {
                $prefijoAscii .= $asciiChar;
            } else {
                $prefijoAscii .= $hexPair;
            }
        }

        return strtoupper($prefijoAscii) . $sufijo;
    }

    public static function transformarSerialACompleto(string $serialOlt, ?string $modelo = null): string
    {
        if ($modelo && !self::modeloRequiereTransformacion($modelo)) {
            return $serialOlt;
        }

        if (preg_match('/^[A-Z]{2,}/', $serialOlt)) {
            return $serialOlt;
        }

        if (strlen($serialOlt) < 4) {
            return $serialOlt;
        }

        $prefijoAscii = substr($serialOlt, 0, 4);
        $sufijo = substr($serialOlt, 4);

        $prefijoHex = '';
        for ($i = 0; $i < 4; $i++) {
            $asciiChar = $prefijoAscii[$i];
            $hexValue = dechex(ord($asciiChar));
            $prefijoHex .= str_pad($hexValue, 2, '0', STR_PAD_LEFT);
        }

        return strtolower($prefijoHex) . $sufijo;
    }

    public function setSerialNumberCompletoAttribute($value)
    {
        $this->attributes['serial_number_completo'] = $value;
        $modelo = $this->attributes['modelo'] ?? null;

        if ($value) {
            if (self::modeloRequiereTransformacion($modelo)) {
                $this->attributes['serial_number_olt'] = self::transformarSerialAOLT($value, $modelo);
            } else {
                $this->attributes['serial_number_olt'] = $value;
            }

            if (empty($this->attributes['serial_number'])) {
                $this->attributes['serial_number'] = $this->attributes['serial_number_olt'];
            }
        } else {
            $this->attributes['serial_number_olt'] = null;
        }
    }

    public function setSerialNumberOltAttribute($value)
    {
        $this->attributes['serial_number_olt'] = $value;
        $modelo = $this->attributes['modelo'] ?? null;

        if ($value && empty($this->attributes['serial_number_completo'])) {
            if (self::modeloRequiereTransformacion($modelo)) {
                $this->attributes['serial_number_completo'] = self::transformarSerialACompleto($value, $modelo);
            } else {
                $this->attributes['serial_number_completo'] = $value;
            }
        }

        if (empty($this->attributes['serial_number']) && $value) {
            $this->attributes['serial_number'] = $value;
        }
    }
}
