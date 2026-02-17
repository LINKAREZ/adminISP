<?php

namespace App\Modules\Infraestructura\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Infraestructura\Models\CajaNap;
use App\Modules\Infraestructura\Models\Hilo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HiloController extends Controller
{
    public function index(Request $request, CajaNap $cajaNap)
    {
        $this->authorize('view', $cajaNap);
        $cajaNap->load(['poste', 'hilos.servicio.ubicacion.cliente']);

        if ($request->wantsJson()) {
            $hilos = $cajaNap->hilos->map(function ($h) {
                $serv = $h->servicio;
                return [
                    'id' => $h->id,
                    'numero_puerto' => $h->numero_puerto,
                    'estado' => $h->estado,
                    'cliente' => $serv && $serv->ubicacion && $serv->ubicacion->cliente ? $serv->ubicacion->cliente->nombre : null,
                    'usuario_pppoe' => $serv ? $serv->usuario_pppoe : null,
                ];
            });
            return response()->json([
                'caja_nap' => [
                    'id' => $cajaNap->id,
                    'codigo' => $cajaNap->codigo ?: 'NAP' . $cajaNap->id,
                    'capacidad_puertos' => $cajaNap->capacidad_puertos,
                ],
                'hilos' => $hilos,
            ]);
        }

        return view('infraestructura.cajas-nap.hilos', compact('cajaNap'));
    }

    public function store(Request $request, CajaNap $cajaNap): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $cajaNap);

        $validated = $request->validate([
            'cantidad' => 'required|integer|min:1|max:64',
        ]);

        $maxPort = $cajaNap->hilos()->max('numero_puerto') ?? 0;
        $created = 0;
        for ($i = 1; $i <= $validated['cantidad']; $i++) {
            $num = $maxPort + $i;
            if ($num > $cajaNap->capacidad_puertos) {
                break;
            }
            if ($cajaNap->hilos()->where('numero_puerto', $num)->exists()) {
                continue;
            }
            $cajaNap->hilos()->create([
                'numero_puerto' => $num,
                'estado' => Hilo::ESTADO_LIBRE,
            ]);
            $created++;
        }

        if ($request->wantsJson()) {
            $cajaNap->load('hilos.servicio.ubicacion.cliente');
            $hilos = $cajaNap->hilos->map(function ($h) {
                $serv = $h->servicio;
                return [
                    'id' => $h->id,
                    'numero_puerto' => $h->numero_puerto,
                    'estado' => $h->estado,
                    'cliente' => $serv && $serv->ubicacion && $serv->ubicacion->cliente ? $serv->ubicacion->cliente->nombre : null,
                    'usuario_pppoe' => $serv ? $serv->usuario_pppoe : null,
                ];
            });
            return response()->json([
                'ok' => true,
                'created' => $created,
                'message' => $created > 0 ? "Se crearon {$created} hilo(s)." : 'No se pudieron crear más hilos (capacidad o duplicados).',
                'hilos' => $hilos,
            ]);
        }

        return redirect()->route('infraestructura.cajas-nap.show', $cajaNap)
            ->with('success', $created > 0 ? "Se crearon {$created} hilo(s)." : 'No se pudieron crear más hilos (capacidad o duplicados).');
    }

    public function update(Request $request, CajaNap $cajaNap, Hilo $hilo): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $cajaNap);
        if ($hilo->caja_nap_id != $cajaNap->id) {
            abort(404);
        }

        $request->validate(['estado' => 'required|in:libre,ocupado,reservado']);
        $hilo->update(['estado' => $request->estado]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'estado' => $hilo->estado]);
        }

        return redirect()->back()->with('success', 'Hilo actualizado.');
    }

    public function destroy(Request $request, CajaNap $cajaNap, Hilo $hilo): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $cajaNap);
        if ($hilo->caja_nap_id != $cajaNap->id) {
            abort(404);
        }
        if ($hilo->servicio()->exists()) {
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'No se puede eliminar un hilo con servicio asignado.'], 422);
            }
            return redirect()->back()->with('error', 'No se puede eliminar un hilo con servicio asignado.');
        }
        $hilo->delete();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back()->with('success', 'Hilo eliminado.');
    }
}
