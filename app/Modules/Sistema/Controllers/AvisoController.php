<?php

namespace App\Modules\Sistema\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sistema\Models\Aviso;
use Illuminate\Http\Request;

class AvisoController extends Controller
{
    public function index()
    {
        $avisos = Aviso::orderBy('vigencia_inicio', 'desc')->orderBy('id', 'desc')->paginate(20);
        return view('sistema.avisos.index', compact('avisos'));
    }

    public function create()
    {
        return view('sistema.avisos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'nullable|string|max:255',
            'mensaje' => 'required|string|max:2000',
            'tipo' => 'nullable|string|max:50',
            'vigencia_inicio' => 'nullable|date',
            'vigencia_fin' => 'nullable|date|after_or_equal:vigencia_inicio',
            'activo' => 'boolean',
        ]);
        Aviso::create(array_merge($request->only('titulo', 'mensaje', 'tipo', 'vigencia_inicio', 'vigencia_fin'), ['activo' => $request->boolean('activo', true)]));
        return redirect()->route('sistema.avisos.index')->with('success', 'Aviso creado.');
    }

    public function edit(Aviso $aviso)
    {
        return view('sistema.avisos.edit', compact('aviso'));
    }

    public function update(Request $request, Aviso $aviso)
    {
        $request->validate([
            'titulo' => 'nullable|string|max:255',
            'mensaje' => 'required|string|max:2000',
            'tipo' => 'nullable|string|max:50',
            'vigencia_inicio' => 'nullable|date',
            'vigencia_fin' => 'nullable|date',
            'activo' => 'boolean',
        ]);
        $aviso->update(array_merge($request->only('titulo', 'mensaje', 'tipo', 'vigencia_inicio', 'vigencia_fin'), ['activo' => $request->boolean('activo', true)]));
        return redirect()->route('sistema.avisos.index')->with('success', 'Aviso actualizado.');
    }

    public function destroy(Aviso $aviso)
    {
        $aviso->delete();
        return redirect()->route('sistema.avisos.index')->with('success', 'Aviso eliminado.');
    }

    /**
     * Página pública para mostrar un aviso (redirección desde router; URL con ?isp=).
     * El tenant se establece por middleware desde query isp.
     */
    public function showPublic(int $id)
    {
        $aviso = Aviso::find($id);
        if (! $aviso) {
            abort(404);
        }
        if (! $aviso->activo) {
            abort(404);
        }
        $hoy = now()->toDateString();
        if ($aviso->vigencia_inicio && $aviso->vigencia_inicio->toDateString() > $hoy) {
            abort(404);
        }
        if ($aviso->vigencia_fin && $aviso->vigencia_fin->toDateString() < $hoy) {
            abort(404);
        }
        return view('sistema.avisos.show-public', compact('aviso'));
    }
}
