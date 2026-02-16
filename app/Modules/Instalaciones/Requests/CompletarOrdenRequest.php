<?php

namespace App\Modules\Instalaciones\Requests;

use App\Modules\Servicios\Models\Onu;
use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class CompletarOrdenRequest extends FormRequest
{
    use AuthorizesWithPermission;
    public function authorize(): bool
    {
        return $this->authorizePermission('instalaciones.update');
    }

    public function rules(): array
    {
        $tipo = $this->input('tipo_pppoe', 'usuario_unico');
        $rules = [
            'tipo_pppoe' => 'required|in:usuario_compartido,usuario_unico',
            'mac_address' => [
                'nullable',
                'regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
            ],
            'onu_id' => 'nullable|exists:onus,id',
        ];
        if ($tipo === 'usuario_unico') {
            $rules['usuario_pppoe'] = 'required|string|max:255';
            $rules['password_pppoe'] = 'required|string|min:6|max:255';
        } else {
            $rules['usuario_pppoe'] = 'nullable|string|max:255';
            $rules['password_pppoe'] = 'nullable|string|max:255';
        }
        $rules['materiales'] = 'nullable|array';
        $rules['materiales.*.articulo_id'] = 'required_with:materiales|integer|exists:articulos,id';
        $rules['materiales.*.almacen_id'] = 'required_with:materiales|integer|exists:almacenes,id';
        $rules['materiales.*.cantidad'] = 'required_with:materiales|numeric|min:0';
        return $rules;
    }
}
