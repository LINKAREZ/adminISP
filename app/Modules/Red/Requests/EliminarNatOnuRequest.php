<?php

namespace App\Modules\Red\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EliminarNatOnuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rule_id' => 'nullable|string',
            'comment' => 'nullable|string|max:255',
            'port' => 'nullable|integer|min:1024|max:65535',
        ];
    }
}
