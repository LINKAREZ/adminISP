<?php

namespace App\Modules\Comprobantes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Comprobantes\Models\CategoriaGasto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoriaGastoController extends Controller
{
    public function index()
    {
        Gate::authorize('comprobantes.read');
        $categorias = CategoriaGasto::withCount('gastos')->orderBy('nombre')->get();
        return view('comprobantes.gastos.categorias-index', compact('categorias'));
    }

    public function create()
    {
        Gate::authorize('comprobantes.create');
        return view('comprobantes.gastos.categorias-create');
    }

    public function store(Request $request)
    {
        Gate::authorize('comprobantes.create');
        $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'nullable|string|max:50',
        ]);
        CategoriaGasto::create($request->only('nombre', 'tipo'));
        return redirect()->route('comprobantes.categorias-gasto.index')->with('success', 'Categoría creada.');
    }

    public function edit(CategoriaGasto $categoriaGasto)
    {
        Gate::authorize('comprobantes.update');
        return view('comprobantes.gastos.categorias-edit', compact('categoriaGasto'));
    }

    public function update(Request $request, CategoriaGasto $categoriaGasto)
    {
        Gate::authorize('comprobantes.update');
        $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'nullable|string|max:50',
            'estado' => 'boolean',
        ]);
        $categoriaGasto->update(array_merge(
            $request->only('nombre', 'tipo'),
            ['estado' => $request->boolean('estado')]
        ));
        return redirect()->route('comprobantes.categorias-gasto.index')->with('success', 'Categoría actualizada.');
    }

    public function destroy(CategoriaGasto $categoriaGasto)
    {
        Gate::authorize('comprobantes.delete');
        if ($categoriaGasto->gastos()->exists()) {
            return redirect()->back()->with('error', 'No se puede eliminar: tiene gastos asociados.');
        }
        $categoriaGasto->delete();
        return redirect()->route('comprobantes.categorias-gasto.index')->with('success', 'Categoría eliminada.');
    }
}
