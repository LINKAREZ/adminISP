<?php

namespace App\Core\Utils;

/**
 * Utilidades para manipulación de strings
 */
class StringUtils
{
    /**
     * Normalizar nombre (primera letra mayúscula de cada palabra)
     */
    public static function normalizeName(?string $name): string
    {
        if (!$name) {
            return '';
        }

        return mb_convert_case(mb_strtolower(trim($name)), MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Normalizar dirección MAC
     */
    public static function normalizeMac(?string $mac): ?string
    {
        if (!$mac) {
            return null;
        }

        // Remover caracteres no válidos
        $mac = preg_replace('/[^0-9A-Fa-f]/', '', $mac);

        if (strlen($mac) !== 12) {
            return null;
        }

        // Formatear con dos puntos
        return strtoupper(implode(':', str_split($mac, 2)));
    }

    /**
     * Normalizar número de teléfono peruano
     */
    public static function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Remover todo excepto números y +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Si tiene 9 dígitos, agregar prefijo +51
        if (strlen($phone) === 9 && $phone[0] === '9') {
            return '+51' . $phone;
        }

        // Si ya tiene +51, retornar como está
        if (str_starts_with($phone, '+51') && strlen($phone) === 12) {
            return $phone;
        }

        return $phone;
    }

    /**
     * Generar slug único
     */
    public static function generateSlug(string $text, string $separator = '-'): string
    {
        $text = mb_strtolower($text, 'UTF-8');

        // Reemplazar caracteres especiales
        $replacements = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ñ' => 'n', 'ü' => 'u',
        ];

        $text = strtr($text, $replacements);

        // Remover caracteres no alfanuméricos
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);

        // Reemplazar espacios con el separador
        $text = preg_replace('/\s+/', $separator, trim($text));

        return $text;
    }

    /**
     * Extraer iniciales de un nombre
     */
    public static function getInitials(?string $name, int $maxChars = 2): string
    {
        if (!$name) {
            return '?';
        }

        $words = preg_split('/\s+/', trim($name));
        $initials = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8');
                if (strlen($initials) >= $maxChars) {
                    break;
                }
            }
        }

        return $initials ?: '?';
    }

    /**
     * Truncar texto con puntos suspensivos
     */
    public static function truncate(?string $text, int $length = 50, string $suffix = '...'): string
    {
        if (!$text) {
            return '';
        }

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length) . $suffix;
    }

    /**
     * Limpiar texto de caracteres especiales
     */
    public static function sanitize(?string $text): string
    {
        if (!$text) {
            return '';
        }

        return htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generar código aleatorio
     */
    public static function generateCode(int $length = 8, string $prefix = ''): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = $prefix;

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $code;
    }

    /**
     * Formatear número de documento (DNI/RUC)
     */
    public static function formatDocument(?string $document): string
    {
        if (!$document) {
            return '-';
        }

        $document = preg_replace('/[^0-9]/', '', $document);

        return match (strlen($document)) {
            8 => substr($document, 0, 2) . '.' . substr($document, 2, 3) . '.' . substr($document, 5, 3),
            11 => substr($document, 0, 2) . '.' . substr($document, 2, 3) . '.' . substr($document, 5, 3) . '.' . substr($document, 8, 3),
            default => $document,
        };
    }

    /**
     * Validar si es un email válido
     */
    public static function isValidEmail(?string $email): bool
    {
        if (!$email) {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Extraer dominio de un email
     */
    public static function getEmailDomain(?string $email): ?string
    {
        if (!self::isValidEmail($email)) {
            return null;
        }

        return substr($email, strpos($email, '@') + 1);
    }
}
