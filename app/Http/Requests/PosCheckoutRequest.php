<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PosCheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required','exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],

            'payment_method' => ['required', 'in:cash,transfer,qris,ewallet'],

            'discount_mode' => ['nullable', 'in:percent,amount'],
            'discount_input' => ['nullable', 'numeric', 'min:0'],

            'cash_received' => ['nullable', 'numeric', 'min:0'],

            // customer
            'customer_id' => ['nullable'],
            'customer_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
