<?php

namespace App\Modules\Sistema\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sistema\Models\Isp;
use App\Modules\Sistema\Requests\StoreIspRequest;
use App\Modules\Sistema\Requests\UpdateIspRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Solo super admin puede ver todos los ISPs
        if (!$this->isSuperAdmin()) {
            abort(403, 'Solo los super administradores pueden gestionar ISPs.');
        }

        $request->validate([
            'buscar' => ['sometimes', 'string', 'max:100'],
            'estado' => ['sometimes', 'in:activo,inactivo'],
            'orden' => ['sometimes', 'in:nombre_asc,nombre_desc,recientes,antiguos'],
        ]);

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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!$this->isSuperAdmin()) {
            abort(403, 'Solo los super administradores pueden crear ISPs.');
        }

        return view('sistema.isps.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIspRequest $request)
    {
        if (!$this->isSuperAdmin()) {
            abort(403, 'Solo los super administradores pueden crear ISPs.');
        }

        $validated = $request->validated();

        // Normalizar checkbox (0/1) a booleano
        $validated['activo'] = $request->boolean('activo');

        $isp = Isp::create($validated);

        return redirect()->route('superadmin.isps.index')
            ->with('success', 'ISP creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Isp $isp)
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }

        // Cargar sin scope para ver cualquier ISP
        $isp = Isp::withoutGlobalScope(\App\Core\Scopes\IspScope::class)
            ->findOrFail($isp->id);

        // Cargar usuarios administradores de este ISP (rol 'administrador')
        $defaultAdmins = \App\Modules\ControlAcceso\Models\User::withoutGlobalScope(\App\Core\Scopes\IspScope::class)
            ->where('isp_id', $isp->id)
            ->whereHas('role', function ($q) {
                $q->where('name', 'administrador');
            })
            ->with('role')
            ->get();

        // Estadísticas del ISP
        $stats = [
            'usuarios' => $isp->users()->count(),
            'clientes' => $isp->clientes()->count(),
            'nodos' => $isp->nodos()->count(),
        ];

        return view('sistema.isps.show', compact('isp', 'defaultAdmins', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Isp $isp)
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
     * Update the specified resource in storage.
     */
    public function update(UpdateIspRequest $request, Isp $isp)
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
     * Remove the specified resource from storage.
     */
    public function destroy(Isp $isp)
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
     * Alternar estado activo/inactivo.
     */
    public function toggleStatus(Request $request, Isp $isp)
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
