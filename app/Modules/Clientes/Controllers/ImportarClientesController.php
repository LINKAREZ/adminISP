<?php

namespace App\Modules\Clientes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\Clientes\Models\Ubicacion;
use App\Modules\Servicios\Models\Servicio;
use App\Modules\Red\Models\Router;
use App\Modules\Servicios\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ImportarClientesController extends Controller
{
    public function index()
    {
        Gate::authorize('clientes.create');
        $routers = Router::where('estado', true)->orderBy('nombre')->get();
        $planes = Plan::where('estado', true)->orderBy('nombre')->get();
        return view('clientes.importar-clientes.index', compact('routers', 'planes'));
    }

    public function store(Request $request)
    {
        Gate::authorize('clientes.create');
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt|max:5120',
            'router_id' => 'required|exists:routers,id',
            'plan_id' => 'required|exists:planes,id',
        ]);

        $ispId = Auth::user()?->isp_id;
        if (! $ispId) {
            return redirect()->route('clientes.importar-clientes.index')
                ->with('error', 'No tiene ISP asignado.');
        }

        $file = $request->file('archivo');
        $handle = fopen($file->getPathname(), 'r');
        $encabezado = fgetcsv($handle);
        $creados = 0;
        $errores = [];

        while (($fila = fgetcsv($handle)) !== false) {
            if (count($fila) < 3) {
                continue;
            }
            $documento = isset($fila[0]) ? trim((string) $fila[0]) : null;
            $tipoDocumento = isset($fila[1]) ? strtolower(trim((string) $fila[1])) : 'dni';
            $nombre = isset($fila[2]) ? trim((string) $fila[2]) : null;
            $direccion = isset($fila[3]) ? trim((string) $fila[3]) : 'Pendiente';
            $telefonos = isset($fila[4]) ? trim((string) $fila[4]) : null;
            $usuarioPppoe = isset($fila[5]) ? trim((string) $fila[5]) : null;
            $passwordPppoe = isset($fila[6]) ? trim((string) $fila[6]) : null;

            if (! $documento || ! $nombre) {
                $errores[] = 'Fila sin documento o nombre: ' . implode(',', $fila);
                continue;
            }

            if (! in_array($tipoDocumento, ['dni', 'ruc', 'ce'], true)) {
                $tipoDocumento = 'dni';
            }

            $routerId = (int) $request->input('router_id');
            $planId = (int) $request->input('plan_id');

            try {
                DB::transaction(function () use ($documento, $tipoDocumento, $nombre, $direccion, $telefonos, $usuarioPppoe, $passwordPppoe, $routerId, $planId, $ispId, &$creados, &$errores, $fila) {
                    $cliente = Cliente::where('documento', $documento)->where('tipo_documento', $tipoDocumento)->first();
                    if (! $cliente) {
                        $cliente = Cliente::create([
                            'nombre' => $nombre,
                            'tipo_documento' => $tipoDocumento,
                            'documento' => $documento,
                            'telefonos' => $telefonos,
                            'notas' => 'Importado desde CSV',
                            'isp_id' => $ispId,
                        ]);
                    }

                    $ubicacion = Ubicacion::where('cliente_id', $cliente->id)->where('router_id', $routerId)->first();
                    if (! $ubicacion) {
                        $ubicacion = Ubicacion::create([
                            'cliente_id' => $cliente->id,
                            'router_id' => $routerId,
                            'direccion' => $direccion,
                            'referencia' => 'Importado desde CSV',
                            'isp_id' => $ispId,
                        ]);
                    }

                    $usuarioPppoe = $usuarioPppoe ?: 'cli' . $cliente->id;
                    $existeServicio = Servicio::where('ubicacion_id', $ubicacion->id)->where('usuario_pppoe', $usuarioPppoe)->exists();
                    if ($existeServicio) {
                        $errores[] = 'Ya existe servicio con usuario ' . $usuarioPppoe . ' para documento ' . $documento;
                        return;
                    }

                    Servicio::create([
                        'ubicacion_id' => $ubicacion->id,
                        'router_id' => $routerId,
                        'plan_id' => $planId,
                        'tipo_pppoe' => 'pppoe',
                        'usuario_pppoe' => $usuarioPppoe,
                        'password_pppoe' => $passwordPppoe,
                        'estado' => 'activo',
                        'es_provisional' => true,
                        'fecha_instalacion' => now(),
                        'isp_id' => $ispId,
                    ]);
                    $creados++;
                });
            } catch (\Throwable $e) {
                $errores[] = 'Fila ' . implode(',', $fila) . ': ' . $e->getMessage();
            }
        }
        fclose($handle);

        $msg = "Se importaron {$creados} cliente(s) / servicio(s).";
        if (! empty($errores)) {
            $msg .= ' Errores: ' . count($errores);
            return redirect()->route('clientes.importar-clientes.index')
                ->with('warning', $msg)
                ->with('errores_importacion', array_slice($errores, 0, 30));
        }
        return redirect()->route('clientes.importar-clientes.index')->with('success', $msg);
    }

    public function plantilla()
    {
        Gate::authorize('clientes.read');
        $csv = "documento,tipo_documento,nombre,direccion,telefonos,usuario_pppoe,password_pppoe\n";
        $csv .= "12345678,dni,Juan Pérez,Av. Ejemplo 123,999888777,usuario1,pass123\n";
        $csv .= "87654321,dni,María López,Calle 456,,usuario2,\n";
        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_importar_clientes.csv"',
        ]);
    }
}
