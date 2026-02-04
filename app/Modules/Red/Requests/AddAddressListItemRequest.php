<?php

namespace App\Modules\Red\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddAddressListItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'list' => 'required|string',
            'address' => 'required|string',
            'comment' => 'nullable|string|max:255',
        ];
    }
}
