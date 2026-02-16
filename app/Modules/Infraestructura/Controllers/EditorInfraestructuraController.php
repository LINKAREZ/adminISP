<?php

namespace App\Modules\Infraestructura\Controllers;

use App\Core\Rules\ExistsInTenant;
use App\Core\Services\TenantConnectionService;
use App\Core\Traits\FillsIspIdInData;
use App\Http\Controllers\Controller;
use App\Modules\Clientes\Models\Ubicacion;
use App\Modules\Infraestructura\Models\Cable;
use App\Modules\Infraestructura\Models\CajaNap;
use App\Modules\Infraestructura\Models\Mufa;
use App\Modules\Infraestructura\Models\Poste;
use App\Modules\Infraestructura\Models\Recorrido;
use App\Modules\Infraestructura\Services\InfraestructuraTableEnsurer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class EditorInfraestructuraController extends Controller
{
    use FillsIspIdInData;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            Gate::authorize('infraestructura.read');
            return $next($request);
        });
    }

    private function ensureInfraestructuraTables(): void
    {
        $connName = TenantConnectionService::currentTenantConnectionName();
        if ($connName) {
            InfraestructuraTableEnsurer::ensure($connName);
        }
    }

    public function index()
    {
        return view('infraestructura.editor.index');
    }

    /**
     * Datos para el editor: nodos (postes, cajas NAP, mufas) y recorridos.
     * Los nodos sin coordenadas reciben un punto por defecto para poder colocarlos.
     */
    public function data(): JsonResponse
    {
        $this->ensureInfraestructuraTables();

        $connName = TenantConnectionService::currentTenantConnectionName();
        $centerLat = -12.046374;
        $centerLng = -77.042793;

        $postes = Poste::withCount('cajasNap')->get();
        $postesData = $postes->map(function ($p, $i) use ($centerLat, $centerLng) {
            $lat = $p->latitud !== null ? (float) $p->latitud : $centerLat + ($i * 0.0002);
            $lng = $p->longitud !== null ? (float) $p->longitud : $centerLng + ($i * 0.0002);
            return [
                'tipo' => 'poste',
                'id' => $p->id,
                'codigo' => $p->codigo ?: 'P' . $p->id,
                'direccion' => $p->direccion,
                'zona' => $p->zona,
                'icon' => $p->icon,
                'lat' => $lat,
                'lng' => $lng,
                'url' => route('infraestructura.postes.show', $p),
                'edit_url' => route('infraestructura.postes.edit', $p),
                'update_url' => route('infraestructura.postes.update', $p),
                'delete_url' => route('infraestructura.postes.destroy', $p),
            ];
        });

        $cajasNap = CajaNap::with(['poste', 'hilos.servicio.ubicacion.cliente'])->get();
        $cajasNapData = $cajasNap->map(function ($c, $i) use ($centerLat, $centerLng) {
            $lat = $c->latitud !== null ? (float) $c->latitud : $centerLat + 0.0003 + ($i * 0.00015);
            $lng = $c->longitud !== null ? (float) $c->longitud : $centerLng + ($i * 0.00015);
            $hilos = $c->hilos->map(function ($h) {
                $serv = $h->servicio;
                return [
                    'id' => $h->id,
                    'numero_puerto' => $h->numero_puerto,
                    'estado' => $h->estado,
                    'cliente' => $serv && $serv->ubicacion && $serv->ubicacion->cliente ? $serv->ubicacion->cliente->nombre : null,
                    'usuario_pppoe' => $serv ? $serv->usuario_pppoe : null,
                ];
            })->values();
            return [
                'tipo' => 'caja_nap',
                'id' => $c->id,
                'codigo' => $c->codigo ?: 'NAP' . $c->id,
                'lat' => $lat,
                'lng' => $lng,
                'poste_id' => $c->poste_id,
                'capacidad_puertos' => (int) $c->capacidad_puertos,
                'url' => route('infraestructura.cajas-nap.show', $c),
                'hilos_count' => $c->hilos->count(),
                'hilos_libres' => $c->hilos->where('estado', 'libre')->count(),
                'hilos_ocupados' => $c->hilos->where('estado', 'ocupado')->count(),
                'hilos_reservados' => $c->hilos->where('estado', 'reservado')->count(),
                'hilos' => $hilos,
                'hilos_url' => route('infraestructura.cajas-nap.hilos.index', $c),
            ];
        });

        $mufasData = collect();
        if ($connName && Schema::connection($connName)->hasTable('mufas')) {
            try {
                $mufas = Mufa::with('poste')->get();
                $mufasData = $mufas->map(function ($m, $i) use ($centerLat, $centerLng) {
                    $lat = $m->latitud !== null ? (float) $m->latitud : $centerLat - 0.0003 - ($i * 0.00015);
                    $lng = $m->longitud !== null ? (float) $m->longitud : $centerLng + ($i * 0.00015);
                    return [
                        'tipo' => 'mufa',
                        'id' => $m->id,
                        'codigo' => $m->codigo ?: 'M' . $m->id,
                        'lat' => $lat,
                        'lng' => $lng,
                        'url' => route('infraestructura.mufas.show', $m),
                    ];
                });
            } catch (\Throwable $e) {
                $mufasData = collect();
            }
        }

        $nodosMap = [];
        foreach (array_merge($postesData->toArray(), $cajasNapData->toArray(), $mufasData->toArray()) as $n) {
            $nodosMap[$n['tipo'] . '_' . $n['id']] = [$n['lat'], $n['lng']];
        }

        $recorridosData = collect();
        if ($connName && Schema::connection($connName)->hasTable('recorridos')) {
            try {
                $recorridos = Recorrido::with('puntos')->get();
                $recorridosData = $recorridos->map(function ($rec) use ($nodosMap) {
                    $puntos = [];
                    $tipos = [];
                    $nodos = [];
                    foreach ($rec->puntos as $p) {
                        $key = $p->tipo . '_' . $p->nodo_id;
                        if (isset($nodosMap[$key])) {
                            $puntos[] = $nodosMap[$key];
                            $tipos[] = $p->tipo;
                            $nodos[] = ['tipo' => $p->tipo, 'id' => (int) $p->nodo_id];
                        }
                    }
                    return [
                        'id' => $rec->id,
                        'nombre' => $rec->nombre,
                        'tipo_cable' => $rec->tipo_cable,
                        'marca_cable' => $rec->marca_cable,
                        'anio_fabricacion' => $rec->anio_fabricacion,
                        'cantidad_buffer' => $rec->cantidad_buffer,
                        'hilos_por_buffer' => $rec->hilos_por_buffer,
                        'cantidad_total_hilos' => $rec->cantidad_total_hilos,
                        'puntos' => $puntos,
                        'tipos' => $tipos,
                        'nodos' => $nodos,
                    ];
                })->filter(fn ($r) => count($r['puntos']) >= 2)->values();
            } catch (\Throwable $e) {
                $recorridosData = collect();
            }
        }

        $ubicacionesData = collect();
        if ($connName && Schema::connection($connName)->hasTable('ubicaciones')) {
            try {
                $ubicaciones = Ubicacion::whereNotNull('latitud')
                    ->whereNotNull('longitud')
                    ->with('cliente')
                    ->get();
                $ubicacionesData = $ubicaciones->map(function ($u) {
                    $cliente = $u->cliente;
                    return [
                        'id' => $u->id,
                        'lat' => (float) $u->latitud,
                        'lng' => (float) $u->longitud,
                        'direccion' => $u->direccion,
                        'cliente_id' => $u->cliente_id,
                        'cliente_nombre' => $cliente ? $cliente->nombre : null,
                        'url' => $cliente ? route('clientes.show', $cliente) : null,
                    ];
                })->values();
            } catch (\Throwable $e) {
                $ubicacionesData = collect();
            }
        }

        $cablesData = collect();
        if ($connName && Schema::connection($connName)->hasTable('cables')) {
            try {
                $cables = Cable::all();
                foreach ($cables as $c) {
                    $keyOrigen = $c->tipo_origen . '_' . $c->id_origen;
                    $keyDestino = $c->tipo_destino . '_' . $c->id_destino;
                    if (isset($nodosMap[$keyOrigen]) && isset($nodosMap[$keyDestino])) {
                        $cablesData->push([
                            'id' => $c->id,
                            'latLngs' => [$nodosMap[$keyOrigen], $nodosMap[$keyDestino]],
                            'nombre' => $c->nombre,
                            'metros' => $c->metros,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                $cablesData = collect();
            }
        }

        return response()->json([
            'postes' => $postesData,
            'cajas_nap' => $cajasNapData,
            'mufas' => $mufasData,
            'recorridos' => $recorridosData,
            'ubicaciones' => $ubicacionesData,
            'cables' => $cablesData,
        ]);
    }

    public function updatePosicion(Request $request): JsonResponse
    {
        $request->validate([
            'tipo' => 'required|in:poste,caja_nap,mufa',
            'id' => 'required|integer|min:1',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $tipo = $request->tipo;
        $id = (int) $request->id;
        $lat = (float) $request->lat;
        $lng = (float) $request->lng;

        if ($tipo === 'poste') {
            $model = Poste::findOrFail($id);
        } elseif ($tipo === 'caja_nap') {
            $model = CajaNap::findOrFail($id);
        } else {
            $model = Mufa::findOrFail($id);
        }

        $this->authorize('update', $model);
        $model->update(['latitud' => $lat, 'longitud' => $lng]);

        return response()->json(['ok' => true]);
    }

    public function storePoste(Request $request): JsonResponse
    {
        Gate::authorize('infraestructura.create');
        $request->validate([
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'codigo' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'zona' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:50|in:minus,grip-lines-vertical,bolt,broadcast-tower,tower-cell,plug,satellite-dish,signal,circle-nodes',
        ]);
        $poste = Poste::create($this->mergeIspIdInto($request->only(['latitud', 'longitud', 'codigo', 'direccion', 'zona', 'icon'])));
        return response()->json([
            'ok' => true,
            'poste' => [
                'id' => $poste->id,
                'codigo' => $poste->codigo,
                'lat' => (float) $poste->latitud,
                'lng' => (float) $poste->longitud,
                'url' => route('infraestructura.postes.show', $poste),
            ],
        ]);
    }

    public function storeCajaNap(Request $request): JsonResponse
    {
        Gate::authorize('infraestructura.create');
        $request->validate([
            'poste_id' => ['required', 'integer', new ExistsInTenant('postes')],
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'codigo' => 'nullable|string|max:100',
        ]);
        $data = $request->only(['poste_id', 'latitud', 'longitud', 'codigo']);
        $data['capacidad_puertos'] = $request->input('capacidad_puertos', 8);
        $cajaNap = CajaNap::create($this->mergeIspIdInto($data));
        return response()->json([
            'ok' => true,
            'caja_nap' => [
                'id' => $cajaNap->id,
                'codigo' => $cajaNap->codigo,
                'lat' => $cajaNap->latitud ? (float) $cajaNap->latitud : null,
                'lng' => $cajaNap->longitud ? (float) $cajaNap->longitud : null,
                'poste_id' => $cajaNap->poste_id,
                'url' => route('infraestructura.cajas-nap.show', $cajaNap),
            ],
        ]);
    }

    public function storeMufa(Request $request): JsonResponse
    {
        Gate::authorize('infraestructura.create');
        $request->validate([
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'codigo' => 'nullable|string|max:100',
            'poste_id' => ['nullable', 'integer', new ExistsInTenant('postes')],
        ]);
        $mufa = Mufa::create($this->mergeIspIdInto($request->only(['latitud', 'longitud', 'codigo', 'poste_id'])));
        return response()->json([
            'ok' => true,
            'mufa' => [
                'id' => $mufa->id,
                'codigo' => $mufa->codigo,
                'lat' => (float) $mufa->latitud,
                'lng' => (float) $mufa->longitud,
                'url' => route('infraestructura.mufas.show', $mufa),
            ],
        ]);
    }

    /**
     * Crea un recorrido con N puntos (postes/cajas/mufas en orden).
     */
    public function storeCablesRecorrido(Request $request): JsonResponse
    {
        Gate::authorize('infraestructura.update');
        $this->ensureInfraestructuraTables();

        $request->validate([
            'nodos' => 'required|array',
            'nodos.*.tipo' => 'required|in:poste,caja_nap,mufa',
            'nodos.*.id' => 'required|integer|min:1',
        ]);

        $nodos = $request->input('nodos');
        if (count($nodos) < 2) {
            return response()->json(['message' => 'Se necesitan al menos 2 nodos para trazar un recorrido.'], 422);
        }

        $connName = TenantConnectionService::currentTenantConnectionName();
        if (! $connName) {
            return response()->json(['message' => 'No hay conexión tenant configurada.'], 500);
        }

        $recorrido = Recorrido::create($this->mergeIspIdInto([]));
        foreach ($nodos as $orden => $nodo) {
            $recorrido->puntos()->create([
                'orden' => $orden,
                'tipo' => (string) $nodo['tipo'],
                'nodo_id' => (int) $nodo['id'],
            ]);
        }

        return response()->json(['ok' => true, 'creados' => 1, 'recorrido_id' => $recorrido->id]);
    }

    public function updateRecorrido(Request $request, Recorrido $recorrido): JsonResponse
    {
        Gate::authorize('infraestructura.update');
        $request->validate([
            'nombre' => 'nullable|string|max:255',
            'tipo_cable' => 'nullable|string|max:100',
            'marca_cable' => 'nullable|string|max:100',
            'anio_fabricacion' => 'nullable|integer|min:1900|max:2100',
            'cantidad_buffer' => 'nullable|integer|min:0',
            'hilos_por_buffer' => 'nullable|integer|min:0',
            'cantidad_total_hilos' => 'nullable|integer|min:0',
        ]);
        $data = $request->only([
            'nombre', 'tipo_cable', 'marca_cable', 'anio_fabricacion',
            'cantidad_buffer', 'hilos_por_buffer', 'cantidad_total_hilos',
        ]);
        $buf = isset($data['cantidad_buffer']) ? (int) $data['cantidad_buffer'] : null;
        $hpb = isset($data['hilos_por_buffer']) ? (int) $data['hilos_por_buffer'] : null;
        if ($buf !== null && $hpb !== null && $buf >= 0 && $hpb >= 0) {
            $data['cantidad_total_hilos'] = $buf * $hpb;
        }
        $recorrido->update($data);
        return response()->json([
            'ok' => true,
            'nombre' => $recorrido->nombre,
            'tipo_cable' => $recorrido->tipo_cable,
            'marca_cable' => $recorrido->marca_cable,
            'anio_fabricacion' => $recorrido->anio_fabricacion,
            'cantidad_buffer' => $recorrido->cantidad_buffer,
            'hilos_por_buffer' => $recorrido->hilos_por_buffer,
            'cantidad_total_hilos' => $recorrido->cantidad_total_hilos,
        ]);
    }

    public function destroyRecorrido(Recorrido $recorrido): JsonResponse
    {
        Gate::authorize('infraestructura.update');
        $recorrido->delete();
        return response()->json(['ok' => true]);
    }

    /**
     * Actualiza el trazado (orden de nodos) de un recorrido.
     */
    public function updateRecorridoPuntos(Request $request, Recorrido $recorrido): JsonResponse
    {
        Gate::authorize('infraestructura.update');

        $request->validate([
            'nodos' => 'required|array',
            'nodos.*.tipo' => 'required|in:poste,caja_nap,mufa',
            'nodos.*.id' => 'required|integer|min:1',
        ]);

        $nodos = $request->input('nodos');
        if (count($nodos) < 2) {
            return response()->json(['message' => 'Se necesitan al menos 2 nodos en el recorrido.'], 422);
        }

        $recorrido->puntos()->delete();
        foreach ($nodos as $orden => $nodo) {
            $recorrido->puntos()->create([
                'orden' => $orden,
                'tipo' => (string) $nodo['tipo'],
                'nodo_id' => (int) $nodo['id'],
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
