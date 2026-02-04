<?php

namespace App\Modules\ControlAcceso\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('control-acceso.create');
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
                Password::min(12) // NIST 800-63B recomienda mínimo 12 caracteres
                    ->mixedCase() // Al menos una mayúscula y una minúscula
                    ->numbers() // Al menos un número
                    ->symbols() // Al menos un símbolo
                    ->uncompromised(), // Verifica contra base de datos de contraseñas comprometidas
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
            'password.min' => 'La contraseña debe tener al menos 12 caracteres.',
            'password.mixed' => 'La contraseña debe contener mayúsculas y minúsculas.',
            'password.numbers' => 'La contraseña debe contener al menos un número.',
            'password.symbols' => 'La contraseña debe contener al menos un símbolo.',
            'password.uncompromised' => 'Esta contraseña ha sido comprometida. Por favor, elija otra.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
