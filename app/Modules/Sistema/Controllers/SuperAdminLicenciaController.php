<?php

namespace App\Modules\Sistema\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sistema\Models\Licencia;
use App\Modules\Sistema\Models\Moneda;
use App\Modules\Sistema\Requests\StoreLicenciaRequest;
use App\Modules\Sistema\Requests\UpdateLicenciaRequest;
use App\Core\Services\TenantConnectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SuperAdminLicenciaController extends Controller
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

    public function index(Request $request): View
    {
        $licencias = collect();
        $conn = TenantConnectionService::centralConnection();
        if (\Illuminate\Support\Facades\Schema::connection($conn)->hasTable('licencias')) {
            $query = Licencia::withCount('isps')->orderBy('sort_order')->orderBy('name');
            if ($request->filled('buscar')) {
                $buscar = $request->buscar;
                $query->where(function ($q) use ($buscar) {
                    $q->where('name', 'like', "%{$buscar}%")
                        ->orWhere('slug', 'like', "%{$buscar}%");
                });
            }
            $licencias = $query->get();
        }
        return view('superadmin.licencias.index', compact('licencias'));
    }

    public function create(): View
    {
        $monedas = Moneda::activos()->get();
        return view('superadmin.licencias.create', compact('monedas'));
    }

    public function store(StoreLicenciaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        Licencia::create($data);
        return redirect()->route('superadmin.licencias.index')->with('success', 'Licencia creada correctamente.');
    }

    public function show(Licencia $licencia): View
    {
        $licencia->loadCount('isps');
        return view('superadmin.licencias.show', compact('licencia'));
    }

    public function edit(Licencia $licencia): View
    {
        $monedas = Moneda::activos()->get();
        return view('superadmin.licencias.edit', compact('licencia', 'monedas'));
    }

    public function update(UpdateLicenciaRequest $request, Licencia $licencia): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $licencia->update($data);
        return redirect()->route('superadmin.licencias.index')->with('success', 'Licencia actualizada correctamente.');
    }

    public function destroy(Licencia $licencia): RedirectResponse
    {
        $licencia->loadCount('isps');
        if (($licencia->isps_count ?? 0) > 0) {
            return redirect()
                ->route('superadmin.licencias.index')
                ->with('error', 'No se puede eliminar la licencia «' . $licencia->name . '» porque tiene ISPs asignados.');
        }
        $licencia->delete();
        return redirect()->route('superadmin.licencias.index')->with('success', 'Licencia eliminada correctamente.');
    }
}
