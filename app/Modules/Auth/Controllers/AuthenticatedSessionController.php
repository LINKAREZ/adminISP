<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Models\AuditLog;
use App\Core\Services\TenantConnectionService;
use App\Modules\ControlAcceso\Models\User;
use App\Modules\Auth\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        // Si el usuario ya está autenticado, redirigir al dashboard (o superadmin si aplica)
        if (Auth::check()) {
            $user = Auth::user();
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard');
            }
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        // Validar campos
        $credentials = $request->validated();

        // Usar Auth::attempt() - método estándar de Laravel Breeze
        // Esto maneja automáticamente la verificación, login y regeneración de sesión
        if (!Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('Las credenciales proporcionadas no son correctas.'),
            ]);
        }

        // Verificar que el usuario esté autenticado
        if (!Auth::check()) {
            throw ValidationException::withMessages([
                'email' => __('Error al iniciar sesión. Por favor, intenta nuevamente.'),
            ]);
        }

        // Registrar conexión tenant y session current_isp_id para que el primer request no dependa solo de SetIspContext
        $user = Auth::user();
        if ($user && isset($user->isp_id) && $user->isp_id) {
            $ispId = (int) $user->isp_id;
            session(['current_isp_id' => $ispId]);
            TenantConnectionService::registerConnectionForIspId($ispId);
        } elseif ($user && (!isset($user->isp_id) || $user->isp_id === null)) {
            // Super admin: usar primer ISP activo con BD para evitar 500 en módulos tenant
            $primerIsp = \App\Modules\Sistema\Models\Isp::on(TenantConnectionService::centralConnection())
                ->where('activo', true)
                ->whereNotNull('database_name')
                ->where('database_name', '!=', '')
                ->orderBy('id')
                ->first();
            if ($primerIsp) {
                session(['current_isp_id' => $primerIsp->id]);
                TenantConnectionService::registerConnectionForIspId((int) $primerIsp->id);
            }
        }

        // Registrar en audit log solo si la conexión tenant está configurada (tabla audit_logs está en tenant)
        $connName = TenantConnectionService::currentTenantConnectionName();
        $canLog = $connName && Config::has("database.connections.{$connName}");
        if ($canLog) {
            try {
                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'login',
                    'model_type' => User::class,
                    'model_id' => Auth::id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'metadata' => [
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                    ],
                ]);
            } catch (\Exception $e) {
                Log::warning('Error al crear audit log en login', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Guardar sesión antes del redirect (evita race: cliente sigue redirect antes de que se persista)
        $request->session()->save();

        // 303 See Other: el cliente debe hacer GET (evita 405 con 302)
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard', [], 303);
        }
        return redirect('/dashboard', 303);
    }

    public function destroy(Request $request)
    {
        $userId = Auth::id();

        // Registrar conexión tenant para que AuditLog pueda escribir
        $user = Auth::user();
        if ($user && isset($user->isp_id) && $user->isp_id) {
            TenantConnectionService::registerConnectionForIspId((int) $user->isp_id);
        }

        $connName = $userId ? TenantConnectionService::currentTenantConnectionName() : null;
        $canLog = $connName && Config::has("database.connections.{$connName}");

        if ($userId && $canLog) {
            try {
                AuditLog::create([
                    'user_id' => $userId,
                    'action' => 'logout',
                    'model_type' => User::class,
                    'model_id' => $userId,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'metadata' => [
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                    ],
                ]);
            } catch (\Exception $e) {
                Log::warning('Error al crear audit log en logout', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
