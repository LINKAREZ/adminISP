<?php

namespace App\Core\Rules;

/**
 * Clase centralizada de reglas de validación comunes
 *
 * Uso:
 *   use App\Core\Rules\ValidationRules;
 *
 *   public function rules(): array
 *   {
 *       return [
 *           'documento' => ValidationRules::documento(),
 *           'telefono' => ValidationRules::telefono(),
 *           'email' => ValidationRules::email(),
 *           'mac_address' => ValidationRules::macAddress(),
 *           // etc.
 *       ];
 *   }
 */
class ValidationRules
{
    // =========================================
    // DOCUMENTOS DE IDENTIDAD
    // =========================================

    /**
     * Reglas para DNI peruano (8 dígitos)
     */
    public static function dni(bool $required = true): array
    {
        $rules = ['string', 'size:8', 'regex:/^[0-9]{8}$/'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * Reglas para RUC peruano (11 dígitos, empieza con 10 o 20)
     */
    public static function ruc(bool $required = true): array
    {
        $rules = ['string', 'size:11', 'regex:/^(10|20)[0-9]{9}$/'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * Reglas para documento genérico (DNI o RUC)
     */
    public static function documento(bool $required = true): array
    {
        $rules = ['string', 'regex:/^[0-9]+$/'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    // =========================================
    // CONTACTO
    // =========================================

    /**
     * Reglas para teléfono (9 dígitos, empieza con 9)
     */
    public static function telefono(bool $required = false): array
    {
        $rules = ['string', 'size:9', 'regex:/^9[0-9]{8}$/'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * Reglas para email
     */
    public static function email(bool $required = false): array
    {
        $rules = ['string', 'email:rfc,dns', 'max:255'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    // =========================================
    // REDES Y EQUIPOS
    // =========================================

    /**
     * Reglas para dirección MAC
     */
    public static function macAddress(bool $required = true): array
    {
        $rules = ['string', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * Reglas para dirección IP
     */
    public static function ipAddress(bool $required = true): array
    {
        $rules = ['ip'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * Reglas para puerto de red (1024-65535)
     */
    public static function port(bool $required = false): array
    {
        $rules = ['integer', 'min:1', 'max:65535'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * Reglas para serial number de ONU (16 caracteres hexadecimales)
     */
    public static function serialNumberOnu(bool $required = true): array
    {
        $rules = ['string', 'size:16', 'regex:/^[0-9A-Fa-f]{16}$/'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    // =========================================
    // PAGOS
    // =========================================

    /**
     * Reglas para número de operación Yape (8 dígitos)
     */
    public static function numeroOperacionYape(bool $required = true): array
    {
        $rules = ['string', 'size:8', 'regex:/^[0-9]{8}$/'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * Reglas para código de seguridad Yape (3 dígitos)
     */
    public static function codigoSeguridadYape(bool $required = true): array
    {
        $rules = ['string', 'size:3', 'regex:/^[0-9]{3}$/'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * Reglas para número de operación Plin (solo números)
     */
    public static function numeroOperacionPlin(bool $required = true): array
    {
        $rules = ['string', 'max:50', 'regex:/^[0-9]+$/'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * Reglas para número de operación de transferencia
     */
    public static function numeroOperacionTransferencia(bool $required = true): array
    {
        $rules = ['string', 'max:50'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * Reglas para monto (decimal positivo)
     */
    public static function monto(bool $required = true): array
    {
        $rules = ['numeric', 'min:0.01', 'max:999999.99'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    // =========================================
    // FECHAS
    // =========================================

    /**
     * Reglas para fecha
     */
    public static function fecha(bool $required = true): array
    {
        $rules = ['date'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * Reglas para año (2020-2099)
     */
    public static function ano(bool $required = true): array
    {
        $rules = ['integer', 'min:2020', 'max:2099'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * Reglas para mes (01-12)
     */
    public static function mes(bool $required = true): array
    {
        $rules = ['string', 'size:2', 'in:01,02,03,04,05,06,07,08,09,10,11,12'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    // =========================================
    // SERVICIOS PPPoE
    // =========================================

    /**
     * Reglas para usuario PPPoE
     */
    public static function usuarioPppoe(bool $required = true): array
    {
        $rules = ['string', 'min:3', 'max:255', 'regex:/^[a-zA-Z0-9._@-]+$/'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * Reglas para password PPPoE
     */
    public static function passwordPppoe(bool $required = true): array
    {
        $rules = ['string', 'min:6', 'max:255'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    // =========================================
    // GENERALES
    // =========================================

    /**
     * Reglas para nombre (texto simple)
     */
    public static function nombre(bool $required = true, int $max = 255): array
    {
        $rules = ['string', 'max:' . $max];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * Reglas para descripción/notas (texto largo)
     */
    public static function descripcion(bool $required = false, int $max = 1000): array
    {
        $rules = ['string', 'max:' . $max];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * Reglas para ID de relación foránea
     */
    public static function foreignId(string $table, bool $required = true): array
    {
        $rules = ['integer', 'exists:' . $table . ',id'];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * Reglas para archivo de imagen
     */
    public static function imagen(bool $required = false, int $maxKb = 2048): array
    {
        $rules = ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:' . $maxKb];
        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }
}
