<?php

namespace App\Modules\Servicios\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Servicios\Requests\StoreOnuRequest;
use App\Modules\Servicios\Requests\UpdateOnuRequest;
use App\Modules\Servicios\Requests\StoreOnuWithoutServiceRequest;
use App\Modules\Servicios\Requests\UpdateOnuApiRequest;
use App\Modules\Servicios\Models\Onu;
use App\Modules\Servicios\Models\Servicio;
use App\Core\Traits\NormalizesMacAddress;
use App\Core\Traits\RespondsWithJson;
use Illuminate\Support\Facades\Log;

class OnuController extends Controller
{
    use NormalizesMacAddress, RespondsWithJson;

    public function create(Servicio $servicio)
    {
        $marcas = \App\Modules\Sistema\Models\OnuMarca::where('estado', true)
            ->with('modelosActivos')
            ->orderBy('orden')
            ->get();
        $onusDisponibles = Onu::whereNull('servicio_id')->get();

        return view('servicios.onu.create', compact('servicio', 'marcas', 'onusDisponibles'));
    }

    public function store(StoreOnuRequest $request, Servicio $servicio)
    {
        try {
            $validated = $request->validated();

            // Normalizar MAC address
            if (!empty($validated['mac_address'])) {
                $validated['mac_address'] = $this->normalizarMacAddress($validated['mac_address']);
            }

            // Si viene marca_id o modelo_id, obtener nombres
            if (!empty($validated['marca_id'])) {
                $marca = \App\Modules\Sistema\Models\OnuMarca::find($validated['marca_id']);
                if ($marca) {
                    $validated['marca'] = $marca->nombre;
                }
            }

            if (!empty($validated['modelo_id'])) {
                $modelo = \App\Modules\Servicios\Models\OnuModelo::find($validated['modelo_id']);
                if ($modelo) {
                    $validated['modelo'] = $modelo->nombre;
                }
            }

            $validated['servicio_id'] = $servicio->id;
            $onu = Onu::create($validated);

            // ✅ Obtener cliente desde ubicación
            $servicio->load('ubicacion.cliente');
            $cliente = $servicio->ubicacion->cliente;

            return redirect()
                ->route('clientes.servicios.show', ['cliente' => $cliente->id, 'servicio' => $servicio])
                ->with('success', 'ONU agregada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear ONU: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al crear la ONU: ' . $e->getMessage());
        }
    }

    public function update(UpdateOnuRequest $request, Servicio $servicio, Onu $onu)
    {
        try {
            $validated = $request->validated();

            // Normalizar MAC address
            if (!empty($validated['mac_address'])) {
                $validated['mac_address'] = $this->normalizarMacAddress($validated['mac_address']);
            }

            // Si viene marca_id o modelo_id, obtener nombres
            if (!empty($validated['marca_id'])) {
                $marca = \App\Modules\Sistema\Models\OnuMarca::find($validated['marca_id']);
                if ($marca) {
                    $validated['marca'] = $marca->nombre;
                }
            }

            if (!empty($validated['modelo_id'])) {
                $modelo = \App\Modules\Servicios\Models\OnuModelo::find($validated['modelo_id']);
                if ($modelo) {
                    $validated['modelo'] = $modelo->nombre;
                }
            }

            $onu->update($validated);

            // ✅ Obtener cliente desde ubicación
            $servicio->load('ubicacion.cliente');
            $cliente = $servicio->ubicacion->cliente;

            return redirect()
                ->route('clientes.servicios.show', ['cliente' => $cliente->id, 'servicio' => $servicio])
                ->with('success', 'ONU actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar ONU: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la ONU: ' . $e->getMessage());
        }
    }

    public function destroy(Servicio $servicio, Onu $onu)
    {
        $onu->update(['servicio_id' => null]);

        // ✅ Obtener cliente desde ubicación
        $servicio->load('ubicacion.cliente');
        $cliente = $servicio->ubicacion->cliente;

        return redirect()
            ->route('clientes.servicios.show', ['cliente' => $cliente->id, 'servicio' => $servicio])
            ->with('success', 'ONU desasociada correctamente.');
    }

    public function storeWithoutService(StoreOnuWithoutServiceRequest $request)
    {
        try {
            $validated = $request->validated();
            $macNormalizada = $this->normalizarMacAddress($validated['mac_address']);

            // Si no hay serial_number_completo, generar uno temporal basado en la MAC
            $serialCompleto = $validated['serial_number_completo'] ?? null;
            if (!$serialCompleto || strlen($serialCompleto) !== 16) {
                // Generar serial temporal: usar MAC sin separadores + padding con ceros hasta 16 caracteres
                $macSinSeparadores = str_replace([':', '-'], '', $macNormalizada);
                // Si la MAC tiene 12 caracteres, agregar 4 ceros al final para llegar a 16
                $serialCompleto = str_pad($macSinSeparadores, 16, '0', STR_PAD_RIGHT);
                if (config('app.debug')) {
                    Log::debug('Serial temporal generado desde MAC', [
                        'mac' => $macNormalizada,
                        'serial_generado' => $serialCompleto
                    ]);
                }
            }

            // Si viene marca_id o modelo_id, obtener nombres
            $marca = $validated['marca'] ?? null;
            $modelo = $validated['modelo'] ?? null;

            if (!empty($validated['marca_id']) && !$marca) {
                $marcaObj = \App\Modules\Sistema\Models\OnuMarca::find($validated['marca_id']);
                if ($marcaObj) {
                    $marca = $marcaObj->nombre;
                }
            }

            if (!empty($validated['modelo_id']) && !$modelo) {
                $modeloObj = \App\Modules\Servicios\Models\OnuModelo::find($validated['modelo_id']);
                if ($modeloObj) {
                    $modelo = $modeloObj->nombre;
                }
            }

            $onu = Onu::create([
                'serial_number_completo' => $serialCompleto,
                'mac_address' => $macNormalizada,
                'marca' => $marca,
                'modelo' => $modelo,
                'usuario' => $validated['usuario'] ?? null,
                'password' => $validated['password'] ?? null,
                'notas' => $validated['notas'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'ONU creada correctamente',
                'onu' => [
                    'id' => $onu->id,
                    'serial_number_completo' => $onu->serial_number_completo,
                    'mac_address' => $onu->mac_address,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear ONU sin servicio: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la ONU: ' . $e->getMessage()
            ], 500);
        }
    }

    public function buscarPorMac(Request $request)
    {
        $mac = $request->input('mac');

        if (!$mac) {
            return response()->json([
                'success' => false,
                'message' => 'MAC address es requerido'
            ], 400);
        }

        try {
            $macNormalizada = $this->normalizarMacAddress($mac);
            $onu = Onu::where('mac_address', $macNormalizada)->first();

            if (!$onu) {
                return response()->json([
                    'success' => false,
                    'message' => 'ONU no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'onu' => [
                    'id' => $onu->id,
                    'serial_number' => $onu->serial_number,
                    'serial_number_completo' => $onu->serial_number_completo,
                    'serial_number_olt' => $onu->serial_number_olt,
                    'mac_address' => $onu->mac_address,
                    'marca' => $onu->marca,
                    'modelo' => $onu->modelo,
                    'usuario' => $onu->usuario,
                    'password' => $onu->password,
                    'servicio_id' => $onu->servicio_id,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al buscar ONU por MAC: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar ONU: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Onu $onu)
    {
        // ✅ Cargar cliente a través de ubicación
        $onu->load('servicio.ubicacion.cliente');
        return response()->json([
            'success' => true,
            'onu' => [
                'id' => $onu->id,
                'serial_number' => $onu->serial_number,
                'serial_number_completo' => $onu->serial_number_completo,
                'serial_number_olt' => $onu->serial_number_olt,
                'mac_address' => $onu->mac_address,
                'marca' => $onu->marca,
                'modelo' => $onu->modelo,
                'usuario' => $onu->usuario,
                'password' => $onu->password,
                'servicio' => $onu->servicio ? [
                    'id' => $onu->servicio->id,
                    'mac_address' => $onu->servicio->mac_address,
                    'cliente' => $onu->servicio->ubicacion->cliente->nombre ?? null, // ✅ Cambio aquí
                ] : null,
            ]
        ]);
    }

    public function updateApi(UpdateOnuApiRequest $request, Onu $onu)
    {
        try {
            $data = $request->validated();

            if (!empty($data['mac_address'])) {
                $data['mac_address'] = $this->normalizarMacAddress($data['mac_address']);
            }

            $onu->update($data);

            return response()->json([
                'success' => true,
                'message' => 'ONU actualizada correctamente',
                'onu' => [
                    'id' => $onu->id,
                    'serial_number_completo' => $onu->serial_number_completo,
                    'mac_address' => $onu->mac_address,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar ONU: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la ONU: ' . $e->getMessage()
            ], 500);
        }
    }
}
