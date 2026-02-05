<?php

namespace App\Modules\ControlAcceso\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Debe coincidir con UserPolicy::create(): root y super admin pueden; resto por permiso.
     */
    public function authorize(): bool
    {
        if (! auth()->check()) {
            return false;
        }
        $user = auth()->user();
        if (method_exists($user, 'isRootUser') && $user->isRootUser()) {
            return true;
        }
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }
        return $user->hasPermission('control-acceso.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isSuperAdmin = auth()->check() && auth()->user()->isSuperAdmin();

        $roleRule = Rule::exists('roles', 'id');
        if ($isSuperAdmin) {
            $roleRule = $roleRule->where('name', 'administrador');
        } else {
            $roleRule = $roleRule->where('name', '!=', 'administrador');
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'role_id' => ['required', $roleRule],
            'isp_id' => [$isSuperAdmin ? 'required' : 'nullable', 'exists:isps,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser válido.',
            'email.unique' => 'Este email ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
