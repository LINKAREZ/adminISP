<?php

namespace App\Modules\Sistema\Controllers;

use App\Core\Services\TenantConnectionService;
use App\Core\Services\TenantDatabaseService;
use App\Http\Controllers\Controller;
use App\Modules\Sistema\Models\Isp;
use App\Modules\Sistema\Requests\IndexIspRequest;
use App\Modules\Sistema\Requests\StoreIspRequest;
use App\Modules\Sistema\Requests\UpdateIspRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        if (!$this->isSuperAdmin()) {
            abort(403, 'Solo los super administradores pueden gestionar ISPs.');
        }

        $query = Isp::withoutGlobalScope(\App\Core\Scopes\IspScope::class)
            ->withCount(['users', 'clientes', 'nodos']);

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('estado')) {
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
        $ispsActivos = (clone $query)->where('activo', true)->count();
        $ispsInactivos = $totalIsps - $ispsActivos;

        $perPage = (int) config('isp.paginacion.default', 15);
        $isps = $query->paginate($perPage)->withQueryString();

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

        // Estadísticas del ISP (desde BD tenant si tiene database_name)
        $stats = ['usuarios' => $defaultAdmins->count(), 'clientes' => 0, 'nodos' => 0];
        if ($isp->database_name) {
            TenantConnectionService::setCurrentIspId($isp->id);
            $stats['clientes'] = \App\Modules\Clientes\Models\Cliente::count();
            $stats['nodos'] = \App\Modules\Red\Models\Nodo::count();
        }

        return view('sistema.isps.show', compact('isp', 'defaultAdmins', 'stats'));
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

        // Verificar que no tenga usuarios asociados
        $tieneUsuarios = DB::table('users')
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
