<?php

namespace App\Modules\Sistema\Controllers;

use App\Core\Services\TenantConnectionService;
use App\Core\Services\TenantDatabaseService;
use App\Http\Controllers\Controller;
use App\Modules\Sistema\Models\Isp;
use App\Modules\Sistema\Models\Licencia;
use App\Modules\Sistema\Requests\IndexIspRequest;
use App\Modules\Sistema\Requests\StoreIspRequest;
use App\Modules\Sistema\Requests\UpdateIspRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class IspController extends Controller
{
    /**
     * Verificar si el usuario es super admin
     */
    protected function isSuperAdmin(): bool
    {
        $user = auth()->user();
        return $user && $user->isSuperAdmin();
    }

    /**
     * Listado de ISPs (solo superadmin). Soporta filtros y respuesta AJAX.
     *
     * @param IndexIspRequest $request
     * @return View|JsonResponse
     */
    public function index(IndexIspRequest $request): View|JsonResponse
    {
        try {
            if (!$this->isSuperAdmin()) {
                abort(403, 'Solo los super administradores pueden gestionar ISPs.');
            }
            app()->forgetInstance('current_isp_id');
            return $this->indexLoad($request);
        } catch (\Throwable $e) {
            Log::error('Error en listado superadmin ISPs', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            $msg = 'Error al cargar el listado de ISPs. ' . (config('app.debug') ? $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine() : 'Revisa los logs del servidor.');
            if ($request->ajax()) {
                return response()->json([
                    'listHtml' => '<div class="alert alert-danger">' . e($msg) . '</div>',
                    'paginationHtml' => '',
                    'totalIsps' => 0,
                    'ispsActivos' => 0,
                    'ispsInactivos' => 0,
                    'currentCount' => 0,
                    'totalCount' => 0,
                ]);
            }
            // Respuesta HTML mínima para no depender de vistas (evita 500 en cadena)
            $url = route('superadmin.isps.index');
            return response(
                '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Error</title></head><body style="font-family:sans-serif;padding:2rem;">'
                . '<h1>Error al cargar el listado</h1><p>' . htmlspecialchars($msg) . '</p>'
                . '<p><a href="' . e($url) . '">Volver a intentar</a> | <a href="' . e(route('superadmin.dashboard')) . '">Dashboard</a></p>'
                . '</body></html>',
                200,
                ['Content-Type' => 'text/html; charset=utf-8']
            );
        }
    }

    /**
     * Carga del listado de ISPs (extraído para try-catch en index).
     */
    private function indexLoad(IndexIspRequest $request): View|JsonResponse
    {
        $query = Isp::withoutGlobalScope(\App\Core\Scopes\IspScope::class)
            ->withCount('users');

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('estado') && Schema::connection(TenantConnectionService::centralConnection())->hasColumn('isps', 'activo')) {
            $query->where('activo', $request->estado === 'activo');
        }

        switch ($request->get('orden', 'nombre_asc')) {
            case 'nombre_desc':
                $query->orderBy('nombre', 'desc');
                break;
            case 'recientes':
                $query->orderByDesc('id');
                break;
            case 'antiguos':
                $query->orderBy('id');
                break;
            case 'nombre_asc':
            default:
                $query->orderBy('nombre');
                break;
        }

        $totalIsps = (clone $query)->count();
        $hasActivo = Schema::connection(TenantConnectionService::centralConnection())->hasColumn('isps', 'activo');
        $ispsActivos = $hasActivo ? (clone $query)->where('activo', true)->count() : $totalIsps;
        $ispsInactivos = $totalIsps - $ispsActivos;

        $perPage = (int) config('isp.paginacion.default', 15);
        $isps = $query->paginate($perPage)->withQueryString();

        $previousIspId = session('current_isp_id');
        foreach ($isps->getCollection() as $isp) {
            if (empty($isp->database_name)) {
                $isp->setAttribute('clientes_count', 0);
                $isp->setAttribute('nodos_count', 0);
                continue;
            }
            try {
                TenantConnectionService::setCurrentIspId($isp->id);
                $isp->setAttribute('clientes_count', \App\Modules\Clientes\Models\Cliente::count());
                $isp->setAttribute('nodos_count', \App\Modules\Red\Models\Nodo::count());
            } catch (\Throwable $e) {
                Log::warning('No se pudo obtener conteos del tenant del ISP ' . $isp->id . ': ' . $e->getMessage());
                $isp->setAttribute('clientes_count', 0);
                $isp->setAttribute('nodos_count', 0);
            }
        }
        if ($previousIspId !== null) {
            TenantConnectionService::setCurrentIspId((int) $previousIspId);
        }

        if ($request->ajax()) {
            return response()->json([
                'listHtml' => view('sistema.isps.partials.list', compact('isps'))->render(),
                'paginationHtml' => view('sistema.isps.partials.pagination', compact('isps'))->render(),
                'totalIsps' => $totalIsps,
                'ispsActivos' => $ispsActivos,
                'ispsInactivos' => $ispsInactivos,
                'currentCount' => $isps->count(),
                'totalCount' => $isps->total(),
            ]);
        }

        return view('sistema.isps.index', compact('isps', 'totalIsps', 'ispsActivos', 'ispsInactivos'));
    }

    /**
     * Formulario de creación de ISP.
     *
     * @return View
     */
    public function create(): View
    {
        if (!$this->isSuperAdmin()) {
            abort(403, 'Solo los super administradores pueden crear ISPs.');
        }

        return view('sistema.isps.create');
    }

    /**
     * Guardar nuevo ISP y crear su base de datos tenant.
     * Al crear un ISP se crea automáticamente la BD física, se ejecutan
     * migraciones tenant y seeders (configuración inicial del ISP).
     *
     * @param StoreIspRequest $request
     * @return RedirectResponse
     */
    public function store(StoreIspRequest $request): RedirectResponse
    {
        if (!$this->isSuperAdmin()) {
            abort(403, 'Solo los super administradores pueden crear ISPs.');
        }

        $validated = $request->validated();

        // Normalizar checkbox (0/1) a booleano
        $validated['activo'] = $request->boolean('activo');

        $isp = Isp::create($validated);

        // Garantizar que el ISP tenga database_name antes de crear la BD física (invariante: no existe ISP sin BD)
        $isp->update(['database_name' => TenantDatabaseService::generateDatabaseName($isp)]);
        $isp->refresh();

        try {
            TenantDatabaseService::createDatabaseForIsp($isp);
        } catch (\Throwable $e) {
            $isp->delete();
            return redirect()->route('superadmin.isps.create')
                ->withInput($request->except('_token'))
                ->with('error', 'No se pudo crear la base de datos del ISP: ' . $e->getMessage());
        }

        return redirect()->route('superadmin.isps.index')
            ->with('success', 'ISP creado exitosamente. Base de datos tenant creada y migrada.');
    }

    /**
     * Ver detalle de un ISP.
     *
     * @param Isp $isp
     * @return View
     */
    public function show(Isp $isp): View
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }

        // Cargar sin scope para ver cualquier ISP
        $isp = Isp::withoutGlobalScope(\App\Core\Scopes\IspScope::class)
            ->findOrFail($isp->id);

        // Cargar usuarios administradadores de este ISP (rol 'administrador') desde BD central
        $defaultAdmins = \App\Modules\ControlAcceso\Models\User::withoutGlobalScope(\App\Core\Scopes\IspScope::class)
            ->where('isp_id', $isp->id)
            ->whereHas('role', function ($q) {
                $q->where('name', 'administrador');
            })
            ->with('role')
            ->get();

        // Estadísticas del ISP (desde BD tenant de este ISP, no la sesión)
        $stats = ['usuarios' => $defaultAdmins->count(), 'clientes' => 0, 'nodos' => 0];
        $licenseRouters = collect();
        $licenciaNamesById = [];
        $previousIspId = session('current_isp_id');
        if ($isp->database_name) {
            TenantConnectionService::setCurrentIspId($isp->id);
            $stats['clientes'] = \App\Modules\Clientes\Models\Cliente::count();
            $stats['nodos'] = \App\Modules\Red\Models\Nodo::count();
            $licenseRouters = \App\Modules\Red\Models\Router::select('id', 'nombre', 'licencia_id', 'license_starts_at', 'license_expires_at')->get();
            $licenciaIds = $licenseRouters->pluck('licencia_id')->filter()->unique()->values();
            if ($licenciaIds->isNotEmpty()) {
                $licenciaNamesById = Licencia::on('mysql')->whereIn('id', $licenciaIds)->pluck('name', 'id')->all();
            }
            if ($previousIspId !== null) {
                TenantConnectionService::setCurrentIspId((int) $previousIspId);
            }
        }

        $licenciasAsignadas = $isp->assignedLicencias()->orderBy('sort_order')->orderBy('name')->get();
        $idsAsignados = $licenciasAsignadas->pluck('id')->all();
        $licenciasDisponiblesParaAsignar = Licencia::on('mysql')->where('is_active', true)
            ->when(!empty($idsAsignados), fn ($q) => $q->whereNotIn('id', $idsAsignados))
            ->orderBy('sort_order')->orderBy('name')->get();
        return view('sistema.isps.show', compact('isp', 'defaultAdmins', 'stats', 'licenseRouters', 'licenciaNamesById', 'licenciasAsignadas', 'licenciasDisponiblesParaAsignar'));
    }

    /**
     * Asignar una licencia al ISP (previo pago).
     */
    public function assignLicense(Request $request, Isp $isp): RedirectResponse
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }
        $isp = Isp::withoutGlobalScope(\App\Core\Scopes\IspScope::class)->findOrFail($isp->id);
        $request->validate(['licencia_id' => 'required|exists:licencias,id']);
        $licenciaId = (int) $request->licencia_id;
        if ($isp->assignedLicencias()->where('licencias.id', $licenciaId)->exists()) {
            return redirect()->route('superadmin.isps.show', $isp)->with('info', 'Esa licencia ya está asignada a este ISP.');
        }
        $isp->assignedLicencias()->attach($licenciaId);
        return redirect()->route('superadmin.isps.show', $isp)->with('success', 'Licencia asignada correctamente.');
    }

    /**
     * Quitar una licencia asignada al ISP.
     */
    public function unassignLicense(Isp $isp, Licencia $licencia): RedirectResponse
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }
        $isp = Isp::withoutGlobalScope(\App\Core\Scopes\IspScope::class)->findOrFail($isp->id);
        $isp->assignedLicencias()->detach($licencia->id);
        return redirect()->route('superadmin.isps.show', $isp)->with('success', 'Licencia quitada del ISP.');
    }

    /**
     * Formulario de edición de ISP.
     *
     * @param Isp $isp
     * @return View
     */
    public function edit(Isp $isp): View
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }

        // Cargar sin scope para editar cualquier ISP
        $isp = Isp::withoutGlobalScope(\App\Core\Scopes\IspScope::class)
            ->findOrFail($isp->id);

        return view('sistema.isps.edit', compact('isp'));
    }

    /**
     * Actualizar ISP.
     *
     * @param UpdateIspRequest $request
     * @param Isp $isp
     * @return RedirectResponse
     */
    public function update(UpdateIspRequest $request, Isp $isp): RedirectResponse
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }

        // Cargar sin scope para actualizar cualquier ISP
        $isp = Isp::withoutGlobalScope(\App\Core\Scopes\IspScope::class)
            ->findOrFail($isp->id);

        $validated = $request->validated();

        // Normalizar checkbox (0/1) a booleano
        $validated['activo'] = $request->boolean('activo');

        // No permitir nunca quitar o vaciar database_name (todo ISP debe tener BD)
        unset($validated['database_name']);

        $isp->update($validated);

        return redirect()->route('superadmin.isps.index')
            ->with('success', 'ISP actualizado exitosamente.');
    }

    /**
     * Eliminar ISP (si no tiene usuarios asociados).
     *
     * @param Isp $isp
     * @return RedirectResponse
     */
    public function destroy(Isp $isp): RedirectResponse
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }

        // Cargar sin scope para eliminar cualquier ISP
        $isp = Isp::withoutGlobalScope(\App\Core\Scopes\IspScope::class)
            ->findOrFail($isp->id);

        // Verificar que no tenga usuarios asociados (users está en BD central)
        $tieneUsuarios = DB::connection(TenantConnectionService::centralConnection())
            ->table('users')
            ->where('isp_id', $isp->id)
            ->exists();

        if ($tieneUsuarios) {
            return redirect()->route('superadmin.isps.index')
                ->with('error', 'No se puede eliminar el ISP porque tiene usuarios asociados.');
        }

        $isp->delete();

        return redirect()->route('superadmin.isps.index')
            ->with('success', 'ISP eliminado exitosamente.');
    }

    /**
     * Alternar estado activo/inactivo del ISP.
     *
     * @param Request $request
     * @param Isp $isp
     * @return RedirectResponse|JsonResponse
     */
    public function toggleStatus(Request $request, Isp $isp): RedirectResponse|JsonResponse
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }

        $isp = Isp::withoutGlobalScope(\App\Core\Scopes\IspScope::class)
            ->findOrFail($isp->id);

        $isp->activo = !$isp->activo;
        $isp->save();

        $message = $isp->activo ? 'ISP activado exitosamente.' : 'ISP desactivado exitosamente.';

        if ($request->ajax()) {
            return response()->json([
                'activo' => $isp->activo,
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}
