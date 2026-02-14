<?php

namespace App\Modules\Onboarding\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sistema\Models\TenantRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SolicitudController extends Controller
{
    public function form(): View
    {
        return view('onboarding.solicitud');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_isp' => 'required|string|max:255',
            'email' => 'required|email',
            'telefono' => 'nullable|string|max:50',
            'mensaje' => 'nullable|string|max:2000',
        ], [
            'nombre_isp.required' => 'El nombre del ISP es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
        ]);

        TenantRequest::create([
            'nombre_isp' => $validated['nombre_isp'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? null,
            'mensaje' => $validated['mensaje'] ?? null,
            'status' => TenantRequest::STATUS_PENDING,
        ]);

        return redirect()->route('landing')->with('success', 'Solicitud enviada. Nos pondremos en contacto a la brevedad.');
    }
}
