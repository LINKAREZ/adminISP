<?php

namespace App\Modules\Red\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Red\Requests\StoreNodoRequest;
use App\Modules\Red\Requests\UpdateNodoRequest;
use App\Modules\Red\Models\Nodo;
use Illuminate\Http\Request;

class NodoController extends Controller
{
    public function index()
    {
        $nodos = \App\Modules\Red\Models\Nodo::latest()->paginate(15);
        return view('red.nodos.index', compact('nodos'));
    }

    public function create()
    {
        return view('red.nodos.create');
    }

    public function store(StoreNodoRequest $request)
    {
        \App\Modules\Red\Models\Nodo::create($request->validated());

        return redirect()->route('red.nodos.index')
            ->with('success', 'Nodo creado correctamente.');
    }

    public function show(Nodo $nodo)
    {
        $nodo->load('routers');
        return view('red.nodos.show', compact('nodo'));
    }

    public function edit(Nodo $nodo)
    {
        return view('red.nodos.edit', compact('nodo'));
    }

    public function update(UpdateNodoRequest $request, Nodo $nodo)
    {
        $nodo->update($request->validated());

        return redirect()->route('red.nodos.index')
            ->with('success', 'Nodo actualizado correctamente.');
    }

    public function destroy(Nodo $nodo)
    {
        $nodo->delete();

        return redirect()->route('red.nodos.index')
            ->with('success', 'Nodo eliminado correctamente.');
    }
}
