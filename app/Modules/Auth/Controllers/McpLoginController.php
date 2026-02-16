<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Models\AuditLog;
use App\Core\Services\TenantConnectionService;
use App\Modules\ControlAcceso\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Login por token para MCP / automatización.
 * Solo activo cuando MCP_LOGIN_TOKEN está definido en .env.
 */
class McpLoginController extends Controller
{
    public function __invoke(Request $request)
    {
        $token = config('auth.mcp_login_token');
        if (!$token || $token !== $request->query('token')) {
            return redirect()->route('login')->withErrors([
                'email' => __('Acceso denegado. Token inválido o no configurado.'),
            ]);
        }

        $email = config('auth.mcp_login_email');
        $password = config('auth.mcp_login_password');
        if (!$email || !$password) {
            return redirect()->route('login')->withErrors([
                'email' => __('MCP_LOGIN_EMAIL y MCP_LOGIN_PASSWORD deben estar configurados.'),
            ]);
        }

        if (!Auth::guard('web')->attempt(['email' => $email, 'password' => $password], true)) {
            return redirect()->route('login')->withErrors([
                'email' => __('Credenciales MCP incorrectas.'),
            ]);
        }

        $user = Auth::user();
        if ($user && $user->isp_id) {
            $ispId = (int) $user->isp_id;
            session(['current_isp_id' => $ispId]);
            TenantConnectionService::registerConnectionForIspId($ispId);
        } elseif ($user && !$user->isp_id) {
            // Super admin: usar primer ISP activo con BD
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
                    'metadata' => ['url' => $request->fullUrl(), 'method' => 'mcp-login'],
                ]);
            } catch (\Exception $e) {
                Log::warning('Error audit log en mcp-login', ['error' => $e->getMessage()]);
            }
        }

        $request->session()->save();

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard', [], 303);
        }

        return redirect('/dashboard', 303);
    }
}
