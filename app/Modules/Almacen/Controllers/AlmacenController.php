<?php

namespace App\Modules\Almacen\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Almacen\Models\Almacen;
use App\Modules\Almacen\Models\Articulo;
use App\Modules\Almacen\Models\MovimientoInventario;
use App\Modules\Almacen\Models\Stock;
use App\Modules\Almacen\Requests\EntregaTecnicoRequest;
use App\Modules\Almacen\Services\AlmacenService;
use App\Core\Services\TenantConnectionService;
use App\Modules\ControlAcceso\Models\User;
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    public function __construct(
        private AlmacenService $almacenService
    ) {}

    public function index(Request $request)
    {
        if (!TenantConnectionService::currentTenantConnectionName()) {
            return redirect()->route('dashboard')->with('warning', 'No hay ISP configurado. Seleccione un ISP para ver almacén.');
        }
        $this->authorize('viewAny', Articulo::class);
        $almacenes = Almacen::withCount('stock')->orderByRaw("CASE tipo WHEN 'central' THEN 1 ELSE 2 END")->orderBy('nombre')->paginate(20);
        return view('almacen.almacenes.index', compact('almacenes'));
    }

    public function stock(Request $request, Almacen $almacen)
    {
        $this->authorize('viewAny', Articulo::class);
        $query = Stock::where('almacen_id', $almacen->id)->where('cantidad', '>', 0)->with('articulo');
        if ($request->filled('buscar')) {
            $term = $request->buscar;
            $query->whereHas('articulo', function ($q) use ($term) {
                $q->where('nombre', 'like', "%{$term}%")->orWhere('codigo', 'like', "%{$term}%");
            });
        }
        $stocks = $query->orderBy('articulo_id')->paginate(25);
        return view('almacen.stock.index', compact('almacen', 'stocks'));
    }

    public function movimientos(Request $request)
    {
        $this->authorize('viewAny', Articulo::class);
        $query = MovimientoInventario::with(['articulo', 'almacenOrigen', 'almacenDestino'])->latest();
        if ($request->filled('almacen_id')) {
            $aid = $request->almacen_id;
            $query->where(function ($q) use ($aid) {
                $q->where('almacen_origen_id', $aid)->orWhere('almacen_destino_id', $aid);
            });
        }
        $movimientos = $query->paginate(25);
        $almacenes = Almacen::orderBy('nombre')->get();
        return view('almacen.movimientos.index', compact('movimientos', 'almacenes'));
    }

    public function entregarForm()
    {
        if (!auth()->user()->hasPermission('almacen.create') && !auth()->user()->hasPermission('almacen.update')) {
            abort(403);
        }
        $central = $this->almacenService->obtenerAlmacenCentral();
        $tecnicos = User::on(TenantConnectionService::CENTRAL_CONNECTION)->orderBy('name')->get(['id', 'name', 'email']);
        $articulos = \App\Modules\Almacen\Models\Articulo::orderBy('nombre')->get();
        $stockCentral = Stock::where('almacen_id', $central->id)->where('cantidad', '>', 0)->with('articulo')->get();
        return view('almacen.entregas.create', compact('central', 'tecnicos', 'articulos', 'stockCentral'));
    }

    public function entregarStore(EntregaTecnicoRequest $request)
    {
        $tecnicoId = (int) $request->tecnico_id;
        $tecnico = User::on(TenantConnectionService::CENTRAL_CONNECTION)->find($tecnicoId);
        if (!$tecnico) {
            return redirect()->back()->with('error', 'Técnico no encontrado.')->withInput();
        }
        $central = $this->almacenService->obtenerAlmacenCentral();
        $almacenDestino = $this->almacenService->obtenerAlmacenVehiculo($tecnicoId, $tecnico->name);
        $items = array_filter($request->validated()['items'], fn ($i) => (float) ($i['cantidad'] ?? 0) > 0);
        if (empty($items)) {
            return redirect()->back()->with('error', 'Indique al menos un ítem con cantidad mayor a 0.')->withInput();
        }
        foreach ($items as $item) {
            $disp = $this->almacenService->cantidadDisponible($central->id, (int) $item['articulo_id']);
            if ($disp < (float) $item['cantidad']) {
                return redirect()->back()->with('error', 'Stock insuficiente en almacén central para uno o más ítems.')->withInput();
            }
        }
        try {
            $this->almacenService->trasladar($central->id, $almacenDestino->id, $items, $request->observacion);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
        return redirect()->route('almacen.entregas.create')->with('success', 'Entrega registrada correctamente.');
    }
}
