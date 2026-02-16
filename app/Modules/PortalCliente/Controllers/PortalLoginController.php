<?php

namespace App\Modules\PortalCliente\Controllers;

use App\Modules\Clientes\Models\ClienteCredencial;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PortalLoginController extends Controller
{
    public function create()
    {
        return view('portal.login');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'documento' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $credencial = ClienteCredencial::where('documento', $validated['documento'])->first();
        if (!$credencial || !Hash::check($validated['password'], $credencial->password)) {
            throw ValidationException::withMessages([
                'documento' => [__('Credenciales incorrectas.')],
            ]);
        }

        $request->session()->put('portal_cliente_id', $credencial->cliente_id);
        return redirect()->route('portal.dashboard');
    }
}
