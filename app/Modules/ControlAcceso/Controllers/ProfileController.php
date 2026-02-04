<?php

namespace App\Modules\ControlAcceso\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ControlAcceso\Requests\UpdateProfileRequest;
use App\Modules\ControlAcceso\Requests\UpdatePasswordRequest;
use App\Modules\ControlAcceso\Models\User;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Mostrar perfil del usuario
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load('role');

        return view('profile.index', compact('user'));
    }

    /**
     * Mostrar formulario de edición de perfil
     */
    public function edit()
    {
        /** @var User $user */
        $user = Auth::user();

        return view('profile.edit', compact('user'));
    }

    /**
     * Actualizar perfil del usuario
     */
    public function update(UpdateProfileRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $user->update($request->validated());

        return redirect()->route('profile.index')
            ->with('success', 'Perfil actualizado correctamente.');
    }

    /**
     * Mostrar formulario de cambio de contraseña
     */
    public function password()
    {
        return view('profile.password');
    }

    /**
     * Actualizar contraseña del usuario
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $validated = $request->validated();

        /** @var User $user */
        $user = Auth::user();
        $user->update([
            'password' => $validated['password'],
        ]);

        return redirect()->route('profile.index')
            ->with('success', 'Contraseña actualizada correctamente.');
    }
}
