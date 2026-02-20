<?php

namespace App\Modules\Sistema\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sistema\Models\Moneda;
use App\Modules\Sistema\Models\Plan;
use App\Modules\Sistema\Requests\StorePlanRequest;
use App\Modules\Sistema\Requests\UpdatePlanRequest;
use App\Core\Services\TenantConnectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SuperAdminPlanController extends Controller
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
        $plans = collect();
        $conn = TenantConnectionService::centralConnection();
        if (\Illuminate\Support\Facades\Schema::connection($conn)->hasTable('plans')) {
            $query = Plan::withCount('isps')->orderBy('sort_order')->orderBy('name');
            if ($request->filled('buscar')) {
                $buscar = $request->buscar;
                $query->where(function ($q) use ($buscar) {
                    $q->where('name', 'like', "%{$buscar}%")
                        ->orWhere('slug', 'like', "%{$buscar}%");
                });
            }
            $plans = $query->get();
        }
        return view('superadmin.plans.index', compact('plans'));
    }

    public function create(): View
    {
        $monedas = Moneda::activos()->get();
        return view('superadmin.plans.create', compact('monedas'));
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        Plan::create($data);
        return redirect()->route('superadmin.plans.index')->with('success', 'Plan creado correctamente.');
    }

    public function show(Plan $plan): View
    {
        $plan->loadCount('isps');
        return view('superadmin.plans.show', compact('plan'));
    }

    public function edit(Plan $plan): View
    {
        $monedas = Moneda::activos()->get();
        return view('superadmin.plans.edit', compact('plan', 'monedas'));
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $plan->update($data);
        return redirect()->route('superadmin.plans.index')->with('success', 'Plan actualizado correctamente.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $plan->loadCount('isps');
        if (($plan->isps_count ?? 0) > 0) {
            return redirect()
                ->route('superadmin.plans.index')
                ->with('error', 'No se puede eliminar el plan «' . $plan->name . '» porque tiene ISPs asignados.');
        }
        $plan->delete();
        return redirect()->route('superadmin.plans.index')->with('success', 'Plan eliminado correctamente.');
    }
}
