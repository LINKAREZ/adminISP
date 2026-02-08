<?php

namespace App\Modules\Clientes\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUbicacionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('clientes.update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'direccion' => ['required', 'string', 'max:255'],
            'router_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value && ! \App\Modules\Red\Models\Router::where('id', $value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => 'router']));
                    }
                },
            ],
            'referencia' => ['nullable', 'string', 'max:255'],
            'distrito' => ['nullable', 'string', 'max:255'],
            'provincia' => ['nullable', 'string', 'max:255'],
            'departamento' => ['nullable', 'string', 'max:255'],
            'latitud' => ['nullable', 'numeric'],
            'longitud' => ['nullable', 'numeric'],
            'notas' => ['nullable', 'string', 'max:1000'],
            'foto_1' => ['nullable', 'image', 'max:2048'],
            'foto_2' => ['nullable', 'image', 'max:2048'],
            'foto_3' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
