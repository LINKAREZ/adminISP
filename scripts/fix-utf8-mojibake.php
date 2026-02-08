<?php

/**
 * Corrige mojibake UTF-8 (ej. ConexiÃ³n → Conexión) en todo el proyecto.
 * Ejecutar desde la raíz: php scripts/fix-utf8-mojibake.php
 */

$replacements = [
    // Palabras completas (orden: más largas primero para no pisar partes)
    'PestaÃ±as' => 'Pestañas',
    'PestaÃ±a' => 'Pestaña',
    'ConexiÃ³n' => 'Conexión',
    'UbicaciÃ³n' => 'Ubicación',
    'ContraseÃ±a' => 'Contraseña',
    'contraseÃ±a' => 'contraseña',
    'DirecciÃ³n' => 'Dirección',
    'direcciÃ³n' => 'dirección',
    'InstalaciÃ³n' => 'Instalación',
    'identificaciÃ³n' => 'identificación',
    'realizarÃ¡' => 'realizará',
    'usarÃ¡' => 'usará',
    'NÃºmero' => 'Número',
    'automÃ¡ticamente' => 'automáticamente',
    'Usuario Ãºnico' => 'Usuario único',
    'PPPoE Ãšnico' => 'PPPoE Único',
    'AncÃ³n' => 'Ancón',
    'BreÃ±a' => 'Breña',
    'JesÃºs MarÃ­a' => 'Jesús María',
    'LurÃ­n' => 'Lurín',
    'RÃ­mac' => 'Rímac',
    'San MartÃ­n de Porres' => 'San Martín de Porres',
    'Santa MarÃ­a del Mar' => 'Santa María del Mar',
    'Villa MarÃ­a del Triunfo' => 'Villa María del Triunfo',
    'PachacÃ¡mac' => 'Pachacámac',
    'estÃ¡' => 'está',
    'ubicaciÃ³n' => 'ubicación',
];

$dirs = ['app', 'resources/views', 'config', 'routes', 'database/seeders', 'database/migrations'];
$fixed = 0;
$touched = 0;

foreach ($dirs as $dir) {
    $path = __DIR__ . '/../' . $dir;
    if (!is_dir($path)) {
        continue;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($it as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $fullPath = $file->getPathname();
        $name = $file->getFilename();
        $isBlade = str_ends_with($name, '.blade.php');
        $isPhp = str_ends_with($name, '.php');
        if (!$isPhp && !$isBlade) {
            continue;
        }
        $content = file_get_contents($fullPath);
        if ($content === false) {
            continue;
        }
        $original = $content;
        foreach ($replacements as $wrong => $correct) {
            $content = str_replace($wrong, $correct, $content);
        }
        if ($content !== $original) {
            file_put_contents($fullPath, $content);
            $fixed++;
            echo "Corregido: " . str_replace(__DIR__ . '/../', '', $fullPath) . "\n";
        }
        $touched++;
    }
}

echo "\nArchivos revisados: $touched. Archivos corregidos: $fixed.\n";
