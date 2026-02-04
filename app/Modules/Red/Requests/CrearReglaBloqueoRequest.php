<?php

namespace App\Modules\Red\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearReglaBloqueoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_address_list' => 'required|string|max:255',
            'chain' => 'nullable|string|in:input,forward,output',
            'comment' => 'nullable|string|max:255',
            'disabled' => 'nullable|boolean',
        ];
    }
}
