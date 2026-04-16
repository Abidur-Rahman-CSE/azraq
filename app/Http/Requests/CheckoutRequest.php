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
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'shipping_method' => ['required', 'in:standard,express'],
            'payment_method' => ['required', 'in:cod,online'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'shipping_address.line_1' => ['required', 'string', 'max:255'],
            'shipping_address.line_2' => ['nullable', 'string', 'max:255'],
            'shipping_address.city' => ['required', 'string', 'max:255'],
            'shipping_address.area' => ['required', 'string', 'max:255'],
            'shipping_address.postal_code' => ['nullable', 'string', 'max:50'],
            'shipping_address.country' => ['required', 'string', 'max:255'],
            'billing_same_as_shipping' => ['nullable', 'boolean'],
            'billing_address.line_1' => ['required_unless:billing_same_as_shipping,1', 'nullable', 'string', 'max:255'],
            'billing_address.line_2' => ['nullable', 'string', 'max:255'],
            'billing_address.city' => ['required_unless:billing_same_as_shipping,1', 'nullable', 'string', 'max:255'],
            'billing_address.area' => ['required_unless:billing_same_as_shipping,1', 'nullable', 'string', 'max:255'],
            'billing_address.postal_code' => ['nullable', 'string', 'max:50'],
            'billing_address.country' => ['required_unless:billing_same_as_shipping,1', 'nullable', 'string', 'max:255'],
        ];
    }
}
