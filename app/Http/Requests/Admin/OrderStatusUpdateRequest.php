<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OrderStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_status' => ['required', 'in:pending,unpaid,paid,failed,refunded'],
            'fulfillment_status' => ['required', 'in:pending,processing,fulfilled,cancelled'],
            'shipping_status' => ['required', 'in:not_shipped,packed,in_transit,delivered,returned'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
