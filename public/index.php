<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Intentar cargar extensión SNMP si estamos en cli-server y no está cargada
if (php_sapi_name() === 'cli-server' && !extension_loaded('snmp')) {
    $extensionDir = ini_get('extension_dir');
    if (empty($extensionDir) || $extensionDir === './') {
        // Si extension_dir no está configurado, usar la ruta por defecto de XAMPP
        $extensionDir = 'C:\\xampp\\php\\ext';
    }
    $snmpDll = $extensionDir . DIRECTORY_SEPARATOR . 'php_snmp.dll';

    // Verificar que el archivo existe y intentar cargarlo
    if (file_exists($snmpDll)) {
        // Intentar cargar la extensión dinámicamente (solo si dl() está disponible y habilitado)
        if (function_exists('dl') && ini_get('enable_dl')) {
            @dl('snmp');
        }
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__ . '/../bootstrap/app.php')
    ->handleRequest(Request::capture());
