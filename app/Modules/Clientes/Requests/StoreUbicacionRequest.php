<?php

namespace App\Modules\Clientes\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUbicacionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('clientes.create');
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
            'foto_1_titulo' => ['nullable', 'string', 'max:80'],
            'foto_2_titulo' => ['nullable', 'string', 'max:80'],
            'foto_3_titulo' => ['nullable', 'string', 'max:80'],
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
            'direccion.required' => 'La dirección es obligatoria.',
        ];
    }

    /**
     * Validar que se suba al menos una foto de ubicación.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $tieneAlMenosUna = $this->hasFile('foto_1') || $this->hasFile('foto_2') || $this->hasFile('foto_3');
            if (!$tieneAlMenosUna) {
                $validator->errors()->add('foto_1', 'Debe subir al menos una foto de la ubicación.');
            }
        });
    }
}
