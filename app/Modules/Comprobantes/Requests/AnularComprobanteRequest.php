<?php

namespace App\Modules\Comprobantes\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnularComprobanteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motivo' => 'required|string|min:10|max:500',
        ];
    }
}
