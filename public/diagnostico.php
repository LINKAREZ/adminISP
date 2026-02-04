<?php
/**
 * Página de diagnóstico para conexión RouterOS API (MikroTik)
 * PHP 7.4+ | Sin Composer | Compatible cPanel
 *
 * Permite editar URL, puerto, usuario y contraseña para probar la conexión API.
 * IMPORTANTE: Borrar o restringir acceso tras usarlo (seguridad).
 */

// Valores por defecto (se sobrescriben con el formulario)
$ROUTER_IP   = trim($_POST['url'] ?? $_GET['url'] ?? '192.168.88.1');
$ROUTER_PORT = (int)($_POST['puerto'] ?? $_GET['puerto'] ?? 8728);
$ROUTER_USER = trim($_POST['usuario'] ?? $_GET['usuario'] ?? 'admin');
$ROUTER_PASS = (string)($_POST['password'] ?? $_GET['password'] ?? '');
$TIMEOUT_SEC = 5;

error_reporting(E_ALL);
ini_set('display_errors', 1);

function estado_class($ok, $warning = false) {
    if ($ok) return 'ok';
    if ($warning) return 'warn';
    return 'error';
}

function estado_texto($ok, $warning = false) {
    if ($ok) return 'OK';
    if ($warning) return 'ADVERTENCIA';
    return 'BLOQUEADO / ERROR';
}

function funcion_disponible($nombre) {
    $disabled = explode(',', str_replace(' ', '', ini_get('disable_functions')));
    return !in_array($nombre, $disabled, true);
}

function probar_tcp($host, $port, $timeout) {
    $errno = 0;
    $errstr = '';
    if (!funcion_disponible('fsockopen')) {
        return ['ok' => false, 'errno' => -1, 'errstr' => 'fsockopen está en disable_functions'];
    }
    $fp = @fsockopen($host, (int)$port, $errno, $errstr, $timeout);
    if ($fp) {
        fclose($fp);
        return ['ok' => true, 'errno' => 0, 'errstr' => ''];
    }
    return ['ok' => false, 'errno' => $errno, 'errstr' => $errstr ?: 'Timeout o conexión rechazada'];
}

/**
 * Envía una palabra en el protocolo RouterOS API (4 bytes longitud big-endian + datos)
 */
function api_word($fp, $word) {
    $len = strlen($word);
    if ($len === 0) {
        fwrite($fp, "\x00\x00\x00\x00");
        return;
    }
    fwrite($fp, pack('N', $len) . $word);
}

/**
 * Prueba login real a RouterOS API (protocolo binario)
 * Retorna ['ok' => bool, 'message' => string, 'detail' => string]
 */
function probar_routeros_api_login($host, $port, $user, $pass, $timeout) {
    if (!funcion_disponible('fsockopen')) {
        return ['ok' => false, 'message' => 'fsockopen no disponible', 'detail' => ''];
    }
    if ($host === '' || $port <= 0) {
        return ['ok' => false, 'message' => 'Falta IP/host o puerto', 'detail' => ''];
    }
    $errno = 0;
    $errstr = '';
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if (!$fp) {
        return ['ok' => false, 'message' => 'No se pudo conectar', 'detail' => "{$errstr} (código {$errno})"];
    }
    stream_set_timeout($fp, $timeout);
    // Enviar /login con =name= y =password=
    api_word($fp, '/login');
    api_word($fp, '=name=' . $user);
    api_word($fp, '=password=' . $pass);
    api_word($fp, ''); // fin de frase
    // Leer respuesta (varias frases hasta !done o !trap)
    $response = '';
    $done = false;
    $trap = false;
    $trapMessage = '';
    while (!feof($fp)) {
        $lenBin = fread($fp, 4);
        if (strlen($lenBin) < 4) break;
        $len = unpack('N', $lenBin)[1];
        if ($len === 0) {
            $done = true;
            break;
        }
        $word = fread($fp, $len);
        if (strlen($word) < $len) break;
        $response .= $word . ' ';
        if ($word === '!done') {
            $trap = false;
            break;
        }
        if ($word === '!trap') {
            $trap = true;
        }
        if (strpos($word, '=message=') === 0) {
            $trapMessage = substr($word, 9);
        }
    }
    fclose($fp);
    if ($trap) {
        return ['ok' => false, 'message' => 'Login rechazado (API)', 'detail' => $trapMessage ?: 'Usuario o contraseña incorrectos'];
    }
    if (strpos($response, '!done') !== false || $done) {
        return ['ok' => true, 'message' => 'Conexión y login correctos', 'detail' => 'RouterOS API respondió OK'];
    }
    return ['ok' => false, 'message' => 'Respuesta inesperada', 'detail' => $response ?: 'Sin datos del router'];
}

// Ejecutar pruebas
$socket_ok    = funcion_disponible('socket_create');
$fsockopen_ok = funcion_disponible('fsockopen');
$curl_ok      = extension_loaded('curl');
$openssl_ok   = extension_loaded('openssl');
$json_ok      = extension_loaded('json');

$internet = probar_tcp('google.com', 80, $TIMEOUT_SEC);
$router_tcp = probar_tcp($ROUTER_IP, $ROUTER_PORT, $TIMEOUT_SEC);

$api_login = null;
if ($ROUTER_IP !== '' && $ROUTER_PORT > 0 && $ROUTER_USER !== '') {
    $api_login = probar_routeros_api_login($ROUTER_IP, $ROUTER_PORT, $ROUTER_USER, $ROUTER_PASS, $TIMEOUT_SEC);
}

$disable_functions = ini_get('disable_functions');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico RouterOS API - MikroTik</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 700px; margin: 0 auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        h1 { margin-top: 0; color: #333; font-size: 1.5rem; }
        h2 { color: #555; font-size: 1.1rem; margin: 24px 0 12px; padding-bottom: 6px; border-bottom: 1px solid #eee; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { width: 220px; color: #666; font-weight: 500; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-weight: 600; font-size: 0.85rem; }
        .ok { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .warn { background: #fff3cd; color: #856404; }
        .conclusion { margin-top: 24px; padding: 16px; border-radius: 6px; font-weight: 500; }
        .conclusion.ok { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .conclusion.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .conclusion.warn { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .mono { font-family: monospace; font-size: 0.9rem; background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
        .small { font-size: 0.85rem; color: #666; }
        .form-grid { display: grid; gap: 12px; margin-bottom: 16px; }
        .form-group { display: grid; grid-template-columns: 120px 1fr; align-items: center; gap: 8px; }
        .form-group label { font-weight: 500; color: #555; }
        .form-group input { padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 1rem; }
        .form-actions { margin-top: 16px; }
        .btn { padding: 10px 20px; background: #007bff; color: #fff; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .form-box { background: #f8f9fa; padding: 20px; border-radius: 6px; margin-bottom: 24px; border: 1px solid #eee; }
    </style>
</head>
<body>
<div class="container">
    <h1>Diagnóstico RouterOS API (MikroTik)</h1>

    <!-- Formulario: URL, puerto, usuario, contraseña -->
    <div class="form-box">
        <h2 style="margin-top: 0;">Configuración del router</h2>
        <form method="post" action="">
            <div class="form-grid">
                <div class="form-group">
                    <label for="url">URL / IP del router</label>
                    <input type="text" id="url" name="url" value="<?php echo htmlspecialchars($ROUTER_IP); ?>" placeholder="192.168.88.1 o router.midominio.com" required>
                </div>
                <div class="form-group">
                    <label for="puerto">Puerto API</label>
                    <input type="number" id="puerto" name="puerto" value="<?php echo (int)$ROUTER_PORT; ?>" min="1" max="65535" placeholder="8728">
                </div>
                <div class="form-group">
                    <label for="usuario">Usuario API</label>
                    <input type="text" id="usuario" name="usuario" value="<?php echo htmlspecialchars($ROUTER_USER); ?>" placeholder="admin" autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password">Contraseña API</label>
                    <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($ROUTER_PASS); ?>" placeholder="Contraseña del router" autocomplete="current-password">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">Ejecutar diagnóstico</button>
            </div>
        </form>
        <p class="small" style="margin-bottom: 0;">Puerto 8728 = API, 8729 = API-SSL. Deja contraseña vacía solo para probar conectividad TCP.</p>
    </div>

    <!-- Información general -->
    <h2>Información general</h2>
    <table>
        <tr>
            <th>Versión PHP</th>
            <td><span class="mono"><?php echo htmlspecialchars(PHP_VERSION); ?></span></td>
        </tr>
        <tr>
            <th>Sistema operativo</th>
            <td><span class="mono"><?php echo htmlspecialchars(PHP_OS); ?></span></td>
        </tr>
        <tr>
            <th>IP del servidor</th>
            <td><span class="mono"><?php echo htmlspecialchars($_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname() ?: 'localhost')); ?></span></td>
        </tr>
    </table>

    <!-- Estado de funciones y extensiones -->
    <h2>Estado de funciones y extensiones PHP</h2>
    <table>
        <tr>
            <th>socket_create</th>
            <td><span class="badge <?php echo estado_class($socket_ok); ?>"><?php echo estado_texto($socket_ok); ?></span></td>
        </tr>
        <tr>
            <th>fsockopen</th>
            <td><span class="badge <?php echo estado_class($fsockopen_ok); ?>"><?php echo estado_texto($fsockopen_ok); ?></span></td>
        </tr>
        <tr>
            <th>curl (extensión)</th>
            <td><span class="badge <?php echo estado_class($curl_ok); ?>"><?php echo estado_texto($curl_ok); ?></span></td>
        </tr>
        <tr>
            <th>openssl (extensión)</th>
            <td><span class="badge <?php echo estado_class($openssl_ok); ?>"><?php echo estado_texto($openssl_ok); ?></span></td>
        </tr>
        <tr>
            <th>json (extensión)</th>
            <td><span class="badge <?php echo estado_class($json_ok); ?>"><?php echo estado_texto($json_ok); ?></span></td>
        </tr>
        <tr>
            <th>disable_functions</th>
            <td class="small"><?php echo $disable_functions !== '' ? nl2br(htmlspecialchars($disable_functions)) : '<span class="badge ok">Ninguna</span>'; ?></td>
        </tr>
    </table>

    <!-- Prueba salida a Internet -->
    <h2>Prueba de salida a Internet</h2>
    <table>
        <tr>
            <th>Conexión a google.com:80</th>
            <td>
                <span class="badge <?php echo estado_class($internet['ok']); ?>"><?php echo $internet['ok'] ? 'OK' : 'ERROR'; ?></span>
                <?php if (!$internet['ok']) { ?>
                    <span class="small"> — <?php echo htmlspecialchars($internet['errstr']); ?> (código: <?php echo (int)$internet['errno']; ?>)</span>
                <?php } ?>
            </td>
        </tr>
    </table>

    <!-- Prueba conexión Router MikroTik -->
    <h2>Prueba de conexión a Router MikroTik</h2>
    <table>
        <tr>
            <th>Destino (tu configuración)</th>
            <td><span class="mono"><?php echo htmlspecialchars($ROUTER_IP ?: '(vacío)'); ?>:<?php echo (int)$ROUTER_PORT; ?></span></td>
        </tr>
        <tr>
            <th>Estado TCP (fsockopen)</th>
            <td>
                <span class="badge <?php echo estado_class($router_tcp['ok']); ?>"><?php echo $router_tcp['ok'] ? 'Conectado' : 'No conectado'; ?></span>
                <?php if (!$router_tcp['ok']) { ?>
                    <br><span class="small">Código: <?php echo (int)$router_tcp['errno']; ?> — <?php echo htmlspecialchars($router_tcp['errstr']); ?></span>
                <?php } ?>
            </td>
        </tr>
        <?php if ($api_login !== null) { ?>
        <tr>
            <th>Login API (usuario + contraseña)</th>
            <td>
                <span class="badge <?php echo estado_class($api_login['ok']); ?>"><?php echo $api_login['ok'] ? 'OK' : 'ERROR'; ?></span>
                <strong><?php echo htmlspecialchars($api_login['message']); ?></strong>
                <?php if ($api_login['detail']) { ?>
                    <br><span class="small"><?php echo htmlspecialchars($api_login['detail']); ?></span>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
    </table>

    <!-- Conclusión automática -->
    <h2>Conclusión</h2>
    <?php
    $conclusion_class = 'ok';
    $conclusion_msg = 'El entorno permite conexiones TCP. ';

    if ($api_login !== null && $api_login['ok']) {
        $conclusion_msg = 'Conexión y autenticación RouterOS API correctas. El servidor puede hablar con el MikroTik.';
    } elseif ($api_login !== null && !$api_login['ok'] && $router_tcp['ok']) {
        $conclusion_class = 'warn';
        $conclusion_msg = 'El puerto TCP conecta pero el login API falló: ' . $api_login['detail'] . '. Revisa usuario y contraseña, y que el usuario tenga permisos API en el MikroTik.';
    } elseif (!$socket_ok) {
        $conclusion_class = 'error';
        $conclusion_msg = 'Este hosting no es compatible con RouterOS API: socket_create está deshabilitado (disable_functions).';
    } elseif (!$internet['ok']) {
        $conclusion_class = 'error';
        $conclusion_msg = 'No hay salida a Internet desde el servidor. Posible bloqueo de firewall o red del hosting.';
    } elseif (!$router_tcp['ok']) {
        $conclusion_class = 'error';
        $conclusion_msg = 'El puerto TCP ' . (int)$ROUTER_PORT . ' no conecta. Posible bloqueo de puertos salientes en el hosting, firewall del router, o IP/puerto incorrectos. Comprueba que la API esté habilitada en el MikroTik (IP > Services).';
    } elseif (!$fsockopen_ok) {
        $conclusion_class = 'warn';
        $conclusion_msg = 'fsockopen está deshabilitado. La librería RouterOS API suele usar socket_create; si también lo bloquean, el hosting no será compatible.';
    }
    ?>
    <div class="conclusion <?php echo $conclusion_class; ?>">
        <?php echo htmlspecialchars($conclusion_msg); ?>
    </div>

    <p class="small" style="margin-top: 24px;">Recuerda eliminar o restringir el acceso a este archivo tras el diagnóstico.</p>
</div>
</body>
</html>
