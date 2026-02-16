<?php

namespace App\Modules\Installer\Services;

use Illuminate\Support\Facades\File;

/**
 * Escribe variables en el archivo .env del proyecto.
 * Los valores no deben contener saltos de línea (se reemplazan por espacio).
 * Comillas dobles y backslash se escapan.
 */
class InstallerEnvService
{
    public function write(array $variables): void
    {
        $envPath = base_path('.env');
        $content = file_exists($envPath) ? file_get_contents($envPath) : '';

        foreach ($variables as $key => $value) {
            $value = (string) $value;
            $value = str_replace(["\r\n", "\r", "\n"], ' ', $value);
            $value = str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}=\"{$value}\"";
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                $content .= "\n{$replacement}\n";
            }
        }

        File::put($envPath, trim($content) . "\n");
    }
}
