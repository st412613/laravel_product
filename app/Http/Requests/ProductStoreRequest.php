<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // 'user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
