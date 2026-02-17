<?php

namespace App\Modules\Sistema\Controllers;

use App\Core\Models\SuperadminAuditLog;
use App\Core\Scopes\IspScope;
use App\Core\Services\TenantConnectionService;
use App\Http\Controllers\Controller;
use App\Modules\ControlAcceso\Models\User;
use Illuminate\View\View;

class SuperAdminAuditController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
                abort(403, 'Solo los super administradores pueden acceder.');
            }
            return $next($request);
        });
    }

    /**
     * Listado de registros de auditoría del panel central.
     */
    public function index(): View
    {
        $conn = TenantConnectionService::centralConnection();

        // Verificar que la tabla exista
        if (!\Illuminate\Support\Facades\Schema::connection($conn)->hasTable('superadmin_audit_logs')) {
            return view('superadmin.audit.index', [
                'logs' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'users' => User::withoutGlobalScope(IspScope::class)->orderBy('name')->get(['id', 'name']),
                'actions' => [],
            ]);
        }

        $query = SuperadminAuditLog::with('user')
            ->orderByDesc('created_at');

        if (request()->filled('user_id')) {
            $query->where('user_id', request('user_id'));
        }
        if (request()->filled('action')) {
            $query->where('action', request('action'));
        }
        if (request()->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', request('fecha_desde'));
        }
        if (request()->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', request('fecha_hasta'));
        }

        $logs = $query->paginate(25)->withQueryString();

        $users = User::withoutGlobalScope(IspScope::class)
            ->orderBy('name')
            ->get(['id', 'name']);

        $actions = SuperadminAuditLog::distinct()
            ->pluck('action')
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        return view('superadmin.audit.index', compact('logs', 'users', 'actions'));
    }
}
