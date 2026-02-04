<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Models\AuditLog;
use App\Modules\ControlAcceso\Models\User;
use App\Modules\Auth\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Registrar en audit log (con try-catch para no bloquear el login)
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

        // Redirigir: super admin → /superadmin, resto → /dashboard
        $user = Auth::user();
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard');
        }
        return redirect('/dashboard');
    }

    public function destroy(Request $request)
    {
        $userId = Auth::id();

        // Registrar en audit log (con try-catch para no bloquear el logout)
        if ($userId) {
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
