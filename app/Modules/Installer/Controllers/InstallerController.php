<?php

namespace App\Modules\Installer\Controllers;

use App\Modules\ControlAcceso\Models\User;
use App\Modules\ControlAcceso\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;

class InstallerController extends Controller
{
    /**
     * Verificar si la aplicación ya está instalada.
     */
    public static function isInstalled(): bool
    {
        $flagPath = storage_path('installed');
        if (file_exists($flagPath)) {
            return true;
        }
        try {
            if (Schema::hasTable('users') && User::exists()) {
                return true;
            }
        } catch (\Throwable $e) {
            // BD no configurada o sin tablas
        }
        return false;
    }

    /**
     * Paso 0: Verificar requisitos (.env existe, extensiones PHP).
     */
    public function index(Request $request)
    {
        $envPath = base_path('.env');
        $envExists = file_exists($envPath);
        $appKeyExists = $envExists && !empty(env('APP_KEY'));
        $requirements = $this->checkRequirements();

        return view('installer.index', compact('envExists', 'appKeyExists', 'requirements'));
    }

    /**
     * Crear .env desde .env.example si no existe, o generar APP_KEY si está vacía.
     */
    public function createEnv(Request $request)
    {
        $envPath = base_path('.env');
        $examplePath = base_path('.env.example');

        if (!file_exists($envPath)) {
            if (!file_exists($examplePath)) {
                return redirect()->route('installer.index')->with('error', 'No se encontró .env.example');
            }
            $content = file_get_contents($examplePath);
            $key = 'base64:' . base64_encode(random_bytes(32));
            $content = preg_replace('/^APP_KEY=.*/m', 'APP_KEY=' . $key, $content);
            if (!File::put($envPath, $content)) {
                return redirect()->route('installer.index')->with('error', 'No se pudo crear el archivo .env. Verifica permisos de escritura.');
            }
            Artisan::call('config:clear');
            return redirect()->route('installer.index')->with('success', 'Archivo .env creado correctamente.');
        }

        if (empty(env('APP_KEY'))) {
            $content = file_get_contents($envPath);
            $key = 'base64:' . base64_encode(random_bytes(32));
            $content = preg_replace('/^APP_KEY=.*/m', 'APP_KEY=' . $key, $content);
            File::put($envPath, $content);
            Artisan::call('config:clear');
            return redirect()->route('installer.index')->with('success', 'APP_KEY generada correctamente.');
        }

        return redirect()->route('installer.index')->with('info', 'El archivo .env ya está configurado.');
    }

    /**
     * Paso 1: Formulario de configuración de base de datos.
     * Si DB_HOST=db o no está definido, se asume VPS/Docker y se prellenan valores por defecto.
     */
    public function database(Request $request)
    {
        $appUrl = config('app.url');
        if (empty($appUrl)) {
            $appUrl = rtrim($request->getSchemeAndHttpHost() . $request->getBasePath(), '/');
        }
        $dbHost = env('DB_HOST', 'db');
        $isVpsDefaults = ($dbHost === 'db' || $dbHost === '');
        // Recomendación: usuario dedicado (adminisp) en lugar de root para la aplicación
        $current = [
            'APP_URL'     => $appUrl,
            'DB_HOST'     => $dbHost ?: 'db',
            'DB_PORT'     => env('DB_PORT', '3306'),
            'DB_DATABASE' => env('DB_DATABASE', 'adminisp'),
            'DB_USERNAME' => env('DB_USERNAME', $isVpsDefaults ? 'adminisp' : 'root'),
            'DB_PASSWORD' => env('DB_PASSWORD', $isVpsDefaults ? 'adminisp%' : ''),
        ];
        return view('installer.database', compact('current', 'isVpsDefaults'));
    }

    /**
     * Solo probar conexión a la BD (AJAX). No guarda nada.
     */
    public function testDatabase(Request $request)
    {
        $validated = $request->validate([
            'DB_HOST' => ['required', 'string', 'max:255'],
            'DB_PORT' => ['required', 'string', 'max:10'],
            'DB_DATABASE' => ['required', 'string', 'max:255'],
            'DB_USERNAME' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (str_contains($value, '@')) {
                        $fail('Use el usuario de MySQL (ej: root o adminisp), no un correo electrónico.');
                    }
                },
            ],
            'DB_PASSWORD' => ['nullable', 'string'],
        ]);

        try {
            $dsn = "mysql:host={$validated['DB_HOST']};port={$validated['DB_PORT']};dbname={$validated['DB_DATABASE']};charset=utf8mb4";
            new \PDO(
                $dsn,
                $validated['DB_USERNAME'],
                $validated['DB_PASSWORD'] ?? '',
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            return response()->json(['success' => true, 'message' => 'Conexión correcta a la base de datos.']);
        } catch (\PDOException $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo conectar: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Guardar configuración de BD (prueba la conexión y escribe .env).
     */
    public function saveDatabase(Request $request)
    {
        $validated = $request->validate([
            'APP_URL' => ['required', 'string', 'max:255', 'regex:#^https?://.+#'],
            'DB_HOST' => ['required', 'string', 'max:255'],
            'DB_PORT' => ['required', 'string', 'max:10'],
            'DB_DATABASE' => ['required', 'string', 'max:255'],
            'DB_USERNAME' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (str_contains($value, '@')) {
                        $fail('El usuario de MySQL no puede ser un correo. Use el usuario de la base de datos (ej: root o adminisp), no el email del administrador del panel.');
                    }
                },
            ],
            'DB_PASSWORD' => ['nullable', 'string'],
        ]);

        try {
            $dsn = "mysql:host={$validated['DB_HOST']};port={$validated['DB_PORT']};dbname={$validated['DB_DATABASE']};charset=utf8mb4";
            $pdo = new \PDO(
                $dsn,
                $validated['DB_USERNAME'],
                $validated['DB_PASSWORD'] ?? '',
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
        } catch (\PDOException $e) {
            return back()->withInput()->withErrors([
                'DB_CONNECTION' => 'No se pudo conectar a la base de datos: ' . $e->getMessage()
            ]);
        }

        try {
            $this->writeEnvVariables($validated);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Permission denied') || str_contains($msg, 'Failed to open stream')) {
                $help = 'En el servidor (VPS con Docker) ejecuta: '
                    . 'docker compose exec app chown www-data:www-data .env && '
                    . 'docker compose exec app chmod 664 .env';
                return back()->withInput()->withErrors([
                    'env' => 'No se puede escribir el archivo .env. ' . $help
                ]);
            }
            throw $e;
        }
        Artisan::call('config:clear');

        return redirect()->route('installer.migrate')->with('success', 'Configuración guardada en .env. La aplicación usará estos valores para conectarse a la base de datos.');
    }

    /**
     * Paso 2: Ejecutar migraciones de la BD central.
     */
    public function migrate(Request $request)
    {
        return view('installer.migrate');
    }

    /**
     * Ejecutar migraciones de la BD central (AJAX/POST).
     */
    public function runMigrations(Request $request)
    {
        try {
            Artisan::call('migrate:fresh', ['--force' => true]);
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Migraciones ejecutadas correctamente.',
                'output' => $output,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar migraciones: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Ejecutar seeders de la BD central.
     */
    public function runSeeders(Request $request)
    {
        try {
            Artisan::call('db:seed', [
                '--class' => 'RolePermissionSeeder',
                '--force' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Datos iniciales de la BD central creados correctamente.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar seeders: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Paso 3: Crear usuario administrador.
     */
    public function admin(Request $request)
    {
        return view('installer.admin');
    }

    /**
     * Guardar usuario administrador.
     */
    public function saveAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        try {
            $roleAdmin = Role::where('name', 'administrador')->first();
            if (!$roleAdmin) {
                return back()->withErrors(['role' => 'No se encontró el rol administrador. Ejecuta los seeders.'])->withInput();
            }

            User::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['name'],
                    'password' => $validated['password'],
                    'role_id' => $roleAdmin->id,
                    'isp_id' => null,
                ]
            );

            $this->writeEnvVariables([
                'DEFAULT_ADMIN_EMAIL' => $validated['email'],
                'DEFAULT_ADMIN_NAME' => $validated['name'],
            ]);

            return redirect()->route('installer.finish');
        } catch (\Throwable $e) {
            return back()->withErrors(['save' => 'Error al crear usuario: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Paso 4: Finalizar instalación.
     */
    public function finish(Request $request)
    {
        File::put(storage_path('installed'), date('Y-m-d H:i:s'));
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        return view('installer.finish');
    }

    private function checkRequirements(): array
    {
        return [
            'php_version' => [
                'ok' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'message' => 'PHP 8.2 o superior (actual: ' . PHP_VERSION . ')',
            ],
            'pdo' => [
                'ok' => extension_loaded('pdo'),
                'message' => 'Extensión PDO',
            ],
            'pdo_mysql' => [
                'ok' => extension_loaded('pdo_mysql'),
                'message' => 'Extensión PDO MySQL',
            ],
            'mbstring' => [
                'ok' => extension_loaded('mbstring'),
                'message' => 'Extensión Mbstring',
            ],
            'openssl' => [
                'ok' => extension_loaded('openssl'),
                'message' => 'Extensión OpenSSL',
            ],
            'json' => [
                'ok' => extension_loaded('json'),
                'message' => 'Extensión JSON',
            ],
            'storage_writable' => [
                'ok' => is_writable(storage_path()),
                'message' => 'Carpeta storage con permisos de escritura',
            ],
            'bootstrap_writable' => [
                'ok' => is_writable(base_path('bootstrap/cache')),
                'message' => 'Carpeta bootstrap/cache con permisos de escritura',
            ],
        ];
    }

    /**
     * Crear la base de datos (AJAX).
     */
    public function createDatabase(Request $request)
    {
        $validated = $request->validate([
            'DB_HOST' => ['required', 'string', 'max:255'],
            'DB_PORT' => ['required', 'string', 'max:10'],
            'DB_DATABASE' => ['required', 'string', 'max:255'],
            'DB_USERNAME' => ['required', 'string', 'max:255'],
            'DB_PASSWORD' => ['nullable', 'string'],
            'DB_ADMIN_USERNAME' => ['nullable', 'string', 'max:255'],
            'DB_ADMIN_PASSWORD' => ['nullable', 'string'],
        ]);

        $host = $validated['DB_HOST'];
        $port = $validated['DB_PORT'];
        $database = $validated['DB_DATABASE'];
        $user = !empty($validated['DB_ADMIN_USERNAME'])
            ? $validated['DB_ADMIN_USERNAME']
            : $validated['DB_USERNAME'];
        $password = !empty($validated['DB_ADMIN_USERNAME'])
            ? ($validated['DB_ADMIN_PASSWORD'] ?? '')
            : ($validated['DB_PASSWORD'] ?? '');

        try {
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new \PDO($dsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            $name = '`' . str_replace('`', '``', $database) . '`';
            $pdo->exec("CREATE DATABASE IF NOT EXISTS {$name} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            return response()->json([
                'success' => true,
                'message' => "Base de datos \"{$database}\" creada o ya existía correctamente.",
            ]);
        } catch (\PDOException $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
            if ($code == 1044 || str_contains($msg, 'Access denied') || str_contains($msg, 'CREATE')) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no tiene permiso para crear bases de datos. Usa un usuario con permiso (p. ej. root) en Usuario y Contraseña.',
                ], 400);
            }
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $msg,
            ], 400);
        }
    }

    /**
     * Crear usuario MySQL (AJAX).
     */
    public function createDatabaseUser(Request $request)
    {
        $validated = $request->validate([
            'DB_HOST' => ['required', 'string', 'max:255'],
            'DB_PORT' => ['required', 'string', 'max:10'],
            'DB_DATABASE' => ['required', 'string', 'max:255'],
            'DB_USERNAME' => ['required', 'string', 'max:255'],
            'DB_PASSWORD' => ['nullable', 'string'],
            'DB_ADMIN_USERNAME' => ['required', 'string', 'max:255'],
            'DB_ADMIN_PASSWORD' => ['nullable', 'string'],
        ]);

        $host = $validated['DB_HOST'];
        $port = $validated['DB_PORT'];
        $database = $validated['DB_DATABASE'];
        $appUser = $validated['DB_USERNAME'];
        $appPassword = $validated['DB_PASSWORD'] ?? '';
        $adminUser = $validated['DB_ADMIN_USERNAME'];
        $adminPassword = $validated['DB_ADMIN_PASSWORD'] ?? '';

        $escape = function ($s) {
            return str_replace(['\\', "'"], ['\\\\', "''"], (string) $s);
        };

        try {
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new \PDO($dsn, $adminUser, $adminPassword, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);

            $dbName = '`' . str_replace('`', '``', $database) . '`';
            $userQuoted = "'" . $escape($appUser) . "'@'%'";

            try {
                $pdo->exec("CREATE USER {$userQuoted} IDENTIFIED BY '" . $escape($appPassword) . "'");
            } catch (\PDOException $e) {
                $errno = (int) ($pdo->errorInfo()[1] ?? 0);
                if ($errno === 1396 || str_contains($e->getMessage(), 'already exists')) {
                    $pdo->exec("ALTER USER {$userQuoted} IDENTIFIED BY '" . $escape($appPassword) . "'");
                } else {
                    throw $e;
                }
            }

            $pdo->exec("GRANT ALL PRIVILEGES ON {$dbName}.* TO {$userQuoted}");
            $pdo->exec('FLUSH PRIVILEGES');

            return response()->json([
                'success' => true,
                'message' => "Usuario \"{$appUser}\" creado o actualizado con permisos sobre \"{$database}\".",
            ]);
        } catch (\PDOException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 400);
        }
    }

    private function writeEnvVariables(array $variables): void
    {
        $envPath = base_path('.env');
        $content = file_exists($envPath) ? file_get_contents($envPath) : '';

        foreach ($variables as $key => $value) {
            $value = (string) $value;
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
