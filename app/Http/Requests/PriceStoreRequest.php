<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PriceStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'currency_id' => [
                'required',
                'integer',
                'exists:currencies,id',
                Rule::unique('prices')->where(function ($query) {
                    // Use input() safely to avoid null errors
                    return $query->where('product_id', $this->input('product_id'));
                }),
            ],
            'amount' => ['required', 'numeric', 'between:-99999999.99,99999999.99'],
        ];
    }
}
