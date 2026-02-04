<?php

namespace App\Modules\Auditoria\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuditoriaController extends Controller
{
    /**
     * Mostrar lista de registros de auditoría
     */
    public function index(Request $request)
    {
        Gate::authorize('auditoria.read');
        $request->validate([
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'action' => ['sometimes', 'string', 'max:100'],
            'model_type' => ['sometimes', 'string', 'max:150'],
            'fecha_desde' => ['sometimes', 'date'],
            'fecha_hasta' => ['sometimes', 'date'],
            'buscar' => ['sometimes', 'string', 'max:100'],
        ]);

        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('user', function ($userQuery) use ($buscar) {
                    $userQuery->where('name', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%");
                })
                    ->orWhere('action', 'like', "%{$buscar}%")
                    ->orWhere('model_type', 'like', "%{$buscar}%")
                    ->orWhere('ip_address', 'like', "%{$buscar}%");
            });
        }

        $logs = $query->paginate(50);

        // Obtener opciones para filtros
        $users = \App\Modules\ControlAcceso\Models\User::orderBy('name')->get(['id', 'name']);
        $actions = AuditLog::distinct()->pluck('action')->sort();
        $modelTypes = AuditLog::distinct()->pluck('model_type')->sort();

        return view('auditoria.index', compact('logs', 'users', 'actions', 'modelTypes'));
    }

    /**
     * Mostrar detalles de un registro de auditoría
     */
    public function show(AuditLog $auditLog)
    {
        Gate::authorize('auditoria.read');
        $auditLog->load('user');

        return view('auditoria.show', compact('auditLog'));
    }
}
