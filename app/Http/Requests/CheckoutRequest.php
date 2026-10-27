<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'delivery_address' => ['required', 'string', 'min:10', 'max:500'],
            'district_or_city' => ['required', 'string', 'max:255'],
            'order_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
