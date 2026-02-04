<?php

namespace App\Modules\Red\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DesconectarPppoeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_id' => 'required|string',
        ];
    }
}
