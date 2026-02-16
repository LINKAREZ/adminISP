<?php

namespace App\Modules\Comprobantes\Controllers;

use App\Core\Rules\ExistsInTenant;
use App\Http\Controllers\Controller;
use App\Modules\Comprobantes\Models\Gasto;
use App\Modules\Comprobantes\Models\CategoriaGasto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class GastoController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('comprobantes.read');

        $query = Gasto::with(['categoria', 'registradoPor'])->orderBy('fecha', 'desc');

        if ($request->filled('categoria_id')) {
            $query->where('categoria_gasto_id', $request->categoria_id);
        }
        if ($request->filled('fecha_desde')) {
            $query->where('fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha', '<=', $request->fecha_hasta);
        }

        $gastos = $query->paginate(20);
        $categorias = CategoriaGasto::where('estado', true)->orderBy('nombre')->get();

        return view('comprobantes.gastos.index', compact('gastos', 'categorias'));
    }

    public function create()
    {
        Gate::authorize('comprobantes.create');
        $categorias = CategoriaGasto::where('estado', true)->orderBy('nombre')->get();
        return view('comprobantes.gastos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        Gate::authorize('comprobantes.create');
        $validated = $request->validate([
            'fecha' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string|max:500',
            'categoria_gasto_id' => ['required', 'integer', new ExistsInTenant('categoria_gastos')],
        ]);
        $validated['registrado_por'] = Auth::id();
        Gasto::create($validated);
        return redirect()->route('comprobantes.gastos.index')->with('success', 'Gasto registrado correctamente.');
    }

    public function edit(Gasto $gasto)
    {
        Gate::authorize('comprobantes.update');
        $categorias = CategoriaGasto::where('estado', true)->orderBy('nombre')->get();
        return view('comprobantes.gastos.edit', compact('gasto', 'categorias'));
    }

    public function update(Request $request, Gasto $gasto)
    {
        Gate::authorize('comprobantes.update');
        $validated = $request->validate([
            'fecha' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string|max:500',
            'categoria_gasto_id' => ['required', 'integer', new ExistsInTenant('categoria_gastos')],
        ]);
        $gasto->update($validated);
        return redirect()->route('comprobantes.gastos.index')->with('success', 'Gasto actualizado correctamente.');
    }

    public function destroy(Gasto $gasto)
    {
        Gate::authorize('comprobantes.delete');
        $gasto->delete();
        return redirect()->route('comprobantes.gastos.index')->with('success', 'Gasto eliminado.');
    }
}
