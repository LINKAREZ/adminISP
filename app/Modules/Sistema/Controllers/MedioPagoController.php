<?php

namespace App\Modules\Sistema\Controllers;

use App\Core\Traits\FillsIspIdInData;
use App\Http\Controllers\Controller;
use App\Modules\Sistema\Requests\StoreMedioPagoRequest;
use App\Modules\Sistema\Requests\UpdateMedioPagoRequest;
use App\Modules\Sistema\Models\MedioPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MedioPagoController extends Controller
{
    use FillsIspIdInData;
    public function __construct()
    {
        $this->authorizeResource(MedioPago::class, 'mediosPago');
    }

    public function index()
    {
        $mediosPago = \App\Modules\Sistema\Models\MedioPago::orderBy('tipo')->orderBy('nombre')->paginate(15);

        return view('medios-pago.index', compact('mediosPago'));
    }

    public function create()
    {
        return view('medios-pago.create');
    }

    public function store(StoreMedioPagoRequest $request)
    {
        $medioPago = \App\Modules\Sistema\Models\MedioPago::create($this->mergeIspIdInto($request->validated()));

        // Invalidar caché de medios de pago activos
        Cache::forget('medios_pago.activos');

        return redirect()
            ->route('sistema.medios-pago.index')
            ->with('success', 'Medio de pago creado correctamente.');
    }

    public function show(MedioPago $mediosPago)
    {
        $mediosPago->load('pagos.cliente');
        return view('medios-pago.show', compact('mediosPago'));
    }

    public function edit(MedioPago $mediosPago)
    {
        return view('medios-pago.edit', compact('mediosPago'));
    }

    public function update(UpdateMedioPagoRequest $request, MedioPago $mediosPago)
    {
        $mediosPago->update($request->validated());

        // Invalidar caché de medios de pago activos
        Cache::forget('medios_pago.activos');

        return redirect()
            ->route('sistema.medios-pago.index')
            ->with('success', 'Medio de pago actualizado correctamente.');
    }

    public function destroy(MedioPago $mediosPago)
    {
        if ($mediosPago->pagos()->count() > 0) {
            return back()
                ->with('error', 'No se puede eliminar el medio de pago porque tiene pagos asociados.');
        }

        $mediosPago->delete();

        // Invalidar caché de medios de pago activos
        Cache::forget('medios_pago.activos');

        return redirect()
            ->route('sistema.medios-pago.index')
            ->with('success', 'Medio de pago eliminado correctamente.');
    }
}
